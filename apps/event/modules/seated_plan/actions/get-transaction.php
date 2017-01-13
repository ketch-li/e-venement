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
*    Copyright (c) 2006-2016 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Number'));
    $this->preLinks($request);
    if ( !$request->getParameter('gauge_id', false) && !$request->getParameter('gauges_list',false) )
      throw new liSeatedException('The action "get-transaction" needs a gauge_id or 1+ gauges_list[] parameter');
    if ( !$request->getParameter('transaction_id', false) )
      throw new liSeatedException('The action "get-transaction" needs a transaction_id');
    
    $ids = array();
    if ( is_array($request->getParameter('gauges_list',false)) )
    foreach ( $request->getParameter('gauges_list') as $id )
      $ids[] = $id;
    if ( $request->getParameter('gauge_id', false) )
      $ids[] = $request->getParameter('gauge_id', false);
    
    $q = Doctrine::getTable('Seat')->createQuery('s')
      ->leftJoin('s.Tickets tck')
      ->andWhereIn('tck.gauge_id', $ids)
      ->leftJoin('tck.DirectContact tc WITH tc.confirmed = ?', true)
      
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.id = ?', $request->getParameter('transaction_id', false))
      ->leftJoin('t.Contact c WITH c.confirmed = ?', true)
      ->leftJoin('c.Professionals p WITH p.id = t.professional_id')
      ->leftJoin('p.Organism o')
    ;
    if ( count($ids) == 1 )
      $q->andWhere('s.seated_plan_id = ?', $request->getParameter('id'));
    
    $transactions = array();
    $this->data = array();
    foreach ( $q->execute() as $seat )
    {
      $this->data[] = array(
        'type'        => 'transaction',
        'seat_id'     => $seat->id,
        'seat_name'   => $seat->name,
        'seat_class'  => $seat->class,
        'transaction' => $request->getParameter('transaction_id'),
        'transaction-txt' => '#'.$request->getParameter('transaction_id'),
        'gauge_id'  => $seat->Tickets[0]->Gauge->id,
        'transaction_id' => $seat->Tickets[0]->transaction_id,
        'position' => array($seat->x-$seat->diameter/2, $seat->y-$seat->diameter/2+4), // +2 is for half of the font height
        'width'     => $seat->diameter,
      );
    }
    
    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
      return 'Success';
    return 'Json';
