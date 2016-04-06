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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->preLinks($request);
    $ids = $request->getParameter('gauges_list', false);
    
    if ( is_array($ids) )
    {
      $q = Doctrine::getTable('SeatedPlan')->createQuery('sp')
        ->leftJoin('sp.Seats s')
        ->leftJoin('sp.Workspaces ws')
        ->leftJoin('ws.Gauges g')
        ->andWhereIn('g.id', $ids)
        ->leftJoin('g.Manifestation m')
        ->leftJoin('m.Event e')
        ->leftJoin('e.MetaEvent me')
        ->leftJoin('m.Location l')
        ->andWhere('l.id = sp.location_id')

        ->select('sp.*, s.*')
      ;
      $this->seated_plans = $q->execute();
      $this->forward404Unless($this->seated_plans->count() > 0);
    }
    elseif ( $id = $request->getParameter('id') )
    {
      $this->executeEdit($request);
      $this->seated_plans = new Doctrine_Collection('SeatedPlan');
      $this->seated_plans[] = $this->seated_plan;
    }
    
    $this->data = array();
    foreach ( $this->seated_plans as $sp )
    foreach ( $sp->Seats as $seat )
    {
      if ( !$seat->rank )
        continue;
      
      $this->data[] = array(
        'type'      => 'rank',
        'rank'      => $seat->rank,
        'seat_id'   => $seat->id,
        'seat_name' => $seat->name,
        'position' => array($seat->x-$seat->diameter/2, $seat->y-$seat->diameter/2+4), // +2 is for half of the font height
        'width'     => $seat->diameter,
      );
    }
    
    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
      return $this->renderText(print_r($this->data));
    return 'Success';
