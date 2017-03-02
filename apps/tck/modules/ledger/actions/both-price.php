<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    // by price / tickets
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->leftJoin('p.Tickets t')
      ->leftJoin('t.Transaction tr')
      ->leftJoin('t.Gauge g')
      ->leftJoin('t.User u')
      ->andWhere('t.printed_at IS NOT NULL OR t.cancelling IS NOT NULL OR t.integrated_at IS NOT NULL')
      ->andWhere('t.duplicating IS NULL') // get only originals
      ->orderBy('pt.name');
    if ( isset($criterias['users']) && is_array($criterias['users']) && isset($criterias['users'][0]) && $criterias['users'][0] )
      $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);
    if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
      $q->andWhereIn('g.workspace_id',$criterias['workspaces']);
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->andWhereIn('t.manifestation_id',$criterias['manifestations']);
    else
      $q->andWhere('t.cancelling IS NULL AND (t.printed_at IS NOT NULL AND t.printed_at >= ? AND t.printed_at < ? OR t.integrated_at IS NOT NULL AND t.integrated_at >= ? AND t.integrated_at < ?) OR t.cancelling IS NOT NULL AND t.created_at >= ? AND t.created_at < ?',array(
          $dates[0], $dates[1],
          $dates[0], $dates[1],
          $dates[0], $dates[1],
        ));

    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    // optimizing stuff
    $q->select('p.id, pt.id, pt.lang, pt.name, pt.description')
      ->addSelect('sum(t.value * CASE WHEN t.cancelling IS NOT NULL THEN 1 ELSE 0 END) AS tickets_cancelling_value')
      ->addSelect('sum(t.value * CASE WHEN t.cancelling IS     NULL THEN 1 ELSE 0 END) AS tickets_normal_value')
      ->addSelect('sum(t.cancelling IS NOT NULL) AS nb_cancelling')
      ->addSelect('count(t.id) AS nb_tickets')
      ->groupBy('p.id, pt.id, pt.lang, pt.name, pt.description')
      ->orderBy('pt.name');
    $this->byPrice = $q->execute();
    
    // by price / products
    if (!( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0
        || isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0
    ))
    {
      $q = Doctrine::getTable('Price')->createQuery('p')
        ->leftJoin('p.BoughtProducts t')
        ->leftJoin('t.Transaction tr')
        ->leftJoin('t.User u')
        ->andWhere('t.integrated_at IS NOT NULL')
        ->orderBy('pt.name');
      if ( isset($criterias['users']) && is_array($criterias['users']) && isset($criterias['users'][0]) && $criterias['users'][0] )
        $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);
      if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0
        || isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
        $q->andWhere('FALSE');
      else
        $q->andWhere('t.integrated_at >= ? AND t.integrated_at < ?',array(
          $dates[0], $dates[1],
        ));
      
      // restrict access to our own user
      $q = $this->restrictQueryToCurrentUser($q);
      
      // optimizing stuff
      $q->select('p.id, pt.id, pt.lang, pt.name, pt.description')
        ->addSelect('0 AS tickets_cancelling_value')
        ->addSelect('sum(t.value) AS tickets_normal_value')
        ->addSelect('0 AS nb_cancelling')
        ->addSelect('count(t.id) AS nb_tickets')
        ->groupBy('p.id, pt.id, pt.lang, pt.name, pt.description')
        ->orderBy('pt.name');
      foreach ( $q->fetchArray() as $p )
      {
        $add = true;
        foreach ( $this->byPrice as $key => $old )
        if ( $old->id == $p['id'] )
        {
          $add = false;
          foreach ( array('tickets_cancelling_value', 'tickets_normal_value', 'nb_cancelling', 'nb_tickets') as $val )
            $old->$val = $old->$val + $p[$val];
          break;
        }
        if ( $add )
        {
          $price = new Price;
          foreach ( $p as $key => $value )
            $price->mapValue($key, $value);
          $this->byPrice[] = $price;
        }
      }
      
      // bought products w/o any price_id
      $q = Doctrine::getTable('BoughtProduct')->createQuery('bp')
        ->andWhere('bp.integrated_at IS NOT NULL')
        ->andWhere('bp.price_id IS NULL')
      ;
      $p = new Price;
      $p->name = 'N/A';
      $p->description = $p->name;
      foreach ( array('tickets_normal_value' => 0, 'nb_tickets' => 0, 'tickets_cancelling_value' => 0, 'nb_cancelling' => 0) as $key => $val )
        $p->mapValue($key, $val);
      foreach ( $q->execute() as $bp )
      foreach ( array('tickets_normal_value' => $bp->value, 'nb_tickets' => 1) as $key => $val )
        $p->$key = $p->$key + $val;
      if ( $p->tickets_normal_value > 0 )
        $this->byPrice[] = $p;
   }
