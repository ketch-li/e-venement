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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class liMemberCardPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    $this->dispatcher->connect('mc.member_card.created', array($this, 'memberCardCreated'));
    $this->dispatcher->connect('tck.products_integrate', array($this, 'addMemberCardOnLinkedProductDelivery'));
  }
  
  public function addMemberCardOnLinkedProductDelivery(sfEvent $event)
  {
    foreach ( $event['products'] as $bp )
    {
      if ( !$bp->product_declination_id )
        continue;
      if ( $bp->Declination->MemberCardTypes->count() == 0 )
        continue;
      if ( $bp->member_card_id )
        continue;
      
      foreach ( $bp->Declination->MemberCardTypes as $mct )
      foreach ( $bp->Transaction->MemberCards as $mc )
      if ( $mct->id == $mc->member_card_type_id
        && Doctrine::getTable('BoughtProduct')->createQuery('bp')->andWhere('bp.member_card_id = ?', $mc->id)->count() == 0 )
      {
        $bp->member_card_id = $mc->id;
        $bp->save();
      }
      
      if ( $bp->member_card_id )
        continue;
      
      $bp->MemberCard = new MemberCard;
      $bp->MemberCard->Transaction = $event['transaction'];
      $bp->MemberCard->MemberCardType = $mct;
      if ( $bp->MemberCard->value > 0 )
      {
        $bp->MemberCard->value = $bp->MemberCard->value;
        $payment = new Payment;
        $payment->value = -$bp->MemberCard->value;
        $payment->MemberCard = $bp->MemberCard;
        $payment->payment_method_id = Doctrine::getTable('PaymentMethod')->createQuery('pm')->andWhere('pm.member_card_linked = ?', true)->fetchOne()->id;
        $bp->MemberCard->Transaction->Payments[] = $payment;
      }
      $bp->save();
      sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent($event->getSubject(), 'mc.member_card.created', array(
        'member_card' => $bp->MemberCard,
      )));
    }
  }
  
  public function memberCardCreated(sfEvent $event)
  {
    foreach ( $event['member_card']->MemberCardType->MemberCardPriceModels as $mcpm )
    if ( $mcpm->autoadd && $mcpm->event_id )
    foreach ( $event['member_card']->MemberCardPrices as $mcp )
    if ( $mcp->event_id == $mcpm->event_id && $mcp->price_id == $mcpm->price_id )
    {
      $q = Doctrine::getTable('Gauge')->createQuery('g')
        ->leftJoin('g.Manifestation m')
        ->andWhere('m.event_id = ?', $mcp->event_id)
        ->orderBy('m.happens_at ASC, gauge.value DESC');
      $gauge = false;
      foreach ( $q->execute() as $gauge )
      if ( $gauge->free > 0 )
        break;
      if ( !$gauge )
        continue;
      
      if ( !$event['member_card']->transaction_id && !$event['member_card']->Transaction->isNew() )
        $event['member_card']->transaction_id = $event['member_card']->Transaction->id;
      
      $ticket = new Ticket;
      $ticket->price_id = $mcp->price_id;
      $ticket->gauge_id = $gauge->id;
      $ticket->value = $ticket->Price->value;
      $ticket->transaction_id = $event['member_card']->transaction_id;
      $ticket->MemberCard = $event['member_card'];
      $event['member_card']->Tickets[] = $ticket;
      $ticket->save();
    }
  }
}
