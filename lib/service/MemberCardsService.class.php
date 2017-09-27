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
*    Copyright (c) 2006-2017 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2017 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/

class MemberCardsService extends EvenementService
{
<<<<<<< HEAD
=======
  public function deleteRemovedMCPrices(MemberCardPriceModel $mcpm)
  {
    if ( is_null($mcpm->event_id) )
      $event_clause = " IS NULL";
    else
      $event_clause = " = $mcpm->event_id";
    
    $q = "
      DELETE 
      FROM member_card_price mcp
      WHERE mcp.id IN (
      	SELECT mcp.id
      	FROM member_card_price mcp
      	LEFT JOIN member_card_price_model mcpm ON mcpm.event_id = mcp.event_id AND mcpm.price_id = mcp.price_id
      	WHERE mcp.member_card_id IN (
      		SELECT id
      		FROM member_card
      		WHERE member_card_type_id = $mcpm->member_card_type_id
          AND expire_at > now()
          AND active = true
      	)
      	AND mcpm.id IS NULL
        AND mcp.event_id ".$event_clause."
        AND mcp.price_id = $mcpm->price_id
      )
    ";
    
    $con = Doctrine_Manager::getInstance()->connection();
    $st = $con->execute($q);
  }
  
  public function deleteUpdatedMCPrices(MemberCardPriceModel $mcpm)
  {
    if ( is_null($mcpm->event_id) )
      $event_clause = " IS NULL";
    else
      $event_clause = " = $mcpm->event_id";
    
    $q = "
      DELETE
      FROM member_card_price
      WHERE id IN (
      	SELECT mcpid
      	FROM (
      		SELECT prices.mcid, prices.mcpid, prices.quantity quantity, rank() OVER (
      			PARTITION BY mcid
      			ORDER BY mcpid DESC
      		)
      		FROM (
      			SELECT mc.id mcid, null mcpid, mcpm.quantity, mcpm.member_card_type_id, mcpm.event_id, mcpm.price_id
      			FROM member_card mc
      			INNER JOIN member_card_price_model mcpm ON mcpm.member_card_type_id = mc.member_card_type_id
      			INNER JOIN ticket t ON mc.id = t.member_card_id AND (t.printed_at IS NOT NULL OR t.integrated_at IS NOT NULL) AND t.duplicating IS NULL AND t.id NOT IN (SELECT duplicating FROM ticket WHERE duplicating IS NOT NULL) AND t.cancelling IS NULL AND t.id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)
      			INNER JOIN manifestation m ON m.id = t.manifestation_id
            UNION
      			SELECT mc.id mcid, mcp.id mcpid, mcpm.quantity, mcpm.member_card_type_id, mcpm.event_id, mcpm.price_id
      			FROM member_card mc
      			INNER JOIN member_card_price mcp ON mcp.member_card_id = mc.id
      			INNER JOIN member_card_price_model mcpm ON mcpm.member_card_type_id = mc.member_card_type_id AND mcpm.event_id = mcp.event_id AND mcpm.price_id = mcp.price_id
      			ORDER BY mcid, mcpid
      		) prices
      		WHERE member_card_type_id = $mcpm->member_card_type_id
      		AND event_id ".$event_clause."
      		AND price_id = $mcpm->price_id
      	) topprices
      	WHERE rank > quantity
      )
    ";
    
    $con = Doctrine_Manager::getInstance()->connection();
    $st = $con->execute($q);
  }
  
  public function AddNewUnlimitedMCPrices(MemberCardPriceModel $mcpm)
  {
    if ( is_null($mcpm->event_id) )
      $event_clause = " IS NULL";
    else
      $event_clause = " = $mcpm->event_id";
    
    $q = "
      INSERT INTO member_card_price
      (sf_guard_user_id, automatic, member_card_id, price_id, event_id, created_at, updated_at, version)

      SELECT mcpm.sf_guard_user_id, true, mc.id, mcpm.price_id, mcpm.event_id, now(), now(), 1
      FROM member_card mc
      INNER JOIN member_card_price_model mcpm ON mcpm.member_card_type_id = mc.member_card_type_id
      WHERE mc.member_card_type_id = $mcpm->member_card_type_id
      AND mc.expire_at > now()
      AND mc.active = true
      AND mcpm.event_id ".$event_clause."
      AND mcpm.price_id = $mcpm->price_id
      AND mcpm.quantity = -1
      AND mc.id NOT IN (
      	SELECT mc.id
      	FROM member_card mc
      	INNER JOIN member_card_price mcp ON mcp.member_card_id = mc.id
      	WHERE mc.member_card_type_id = $mcpm->member_card_type_id
      	AND mcp.event_id ".$event_clause."
        AND mcp.price_id = $mcpm->price_id
      )
    ";
    
    $con = Doctrine_Manager::getInstance()->connection();
    $st = $con->execute($q);
    
    $this->updateMCPMVersion($mcpm);
  }
  
  public function AddNewlimitedMCPrices(MemberCardPriceModel $mcpm)
  {
    if ( is_null($mcpm->event_id) )
      $event_clause = " IS NULL";
    else
      $event_clause = " = $mcpm->event_id";
    
    $q = "
      INSERT INTO member_card_price
      (sf_guard_user_id, automatic, member_card_id, price_id, event_id, created_at, updated_at, version)

      SELECT mcpm.sf_guard_user_id, true, mc.id, mcpm.price_id, mcpm.event_id, now(), now(), 1 
      FROM member_card mc
      INNER JOIN member_card_price_model mcpm ON mcpm.member_card_type_id = mc.member_card_type_id
      LEFT JOIN (
      	SELECT mc.id, mc.member_card_type_id, m.event_id, count(t.*) tickets
      	FROM member_card mc
      	LEFT JOIN ticket t ON t.member_card_id = mc.id AND (t.printed_at IS NOT NULL OR t.integrated_at IS NOT NULL) AND t.duplicating IS NULL AND t.id NOT IN (SELECT duplicating FROM ticket WHERE duplicating IS NOT NULL) AND t.cancelling IS NULL AND t.id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)
      	LEFT JOIN manifestation m ON m.id = t.manifestation_id
      	GROUP BY mc.id, mc.member_card_type_id, m.event_id
      ) mct ON mct.member_card_type_id = mcpm.member_card_type_id AND mct.id = mc.id AND (mct.event_id = mcpm.event_id OR mct.event_id IS NULL)
      LEFT JOIN (
      	SELECT mc.id, mc.member_card_type_id, mcp.event_id, count(mcp.*) prices
      	FROM member_card mc
      	LEFT JOIN member_card_price mcp ON mc.id = mcp.member_card_id
      	GROUP BY mc.id, mc.member_card_type_id, mcp.event_id 
      ) amcp ON amcp.id = mc.id AND amcp.member_card_type_id = mcpm.member_card_type_id AND (amcp.event_id = mcpm.event_id OR amcp.event_id IS NULL)
      CROSS JOIN generate_series(1, mcpm.quantity - coalesce(mct.tickets, 0) - coalesce(amcp.prices, 0)) as v
      WHERE mcpm.member_card_type_id = $mcpm->member_card_type_id
      AND mcpm.event_id ".$event_clause."
      AND mcpm.price_id = $mcpm->price_id
      AND mc.expire_at > now()
      AND mc.active = true    
    ";
    
    $con = Doctrine_Manager::getInstance()->connection();
    $st = $con->execute($q);
    
    $this->updateMCPMVersion($mcpm);
  }
  
  protected function updateMCPMVersion(MemberCardPriceModel $mcpm)
  {
    $q = "
      INSERT INTO member_card_price_version
      SELECT *
      FROM member_card_price mcp
      ON CONFLICT DO NOTHING
    ";
    
    $con = Doctrine_Manager::getInstance()->connection();
    $st = $con->execute($q);
  }
  
  public function completeMemberCardsWithMCPrice(array $values, Event $event = NULL)
  {
    $mcs = $this->getActiveMemberCards($values['member_card_type_id']);
    
    foreach ( $mcs as $mc ) {
        $mcp = new MemberCardPrice;
        $mcp->price_id = $values['price_id'];
        
        // anonymous MemberCardPrice
        if ( !isset($event) ) {
            $this->addMemberCardPriceToMemberCard($mcp, $mc, $values['quantity']);
            continue;
        }
        
        // if there is already a MemberCardPrice for this event_id, do not process
        if ( in_array($event->id, $mc->MemberCardPrices->toKeyValueArray('id', 'event_id')) ) {
            continue;
        }
        
        // if there is already a ticket pointing on the given $event linked to the current MemberCard, do not process
        if ( !$this->hasMemberCardTicketLinkedToEvent($mc, $event) ) {
            $mcp->Event = $event;
            $this->addMemberCardPriceToMemberCard($mcp, $mc, $values['quantity']);
        }
    }
    
    return $mcs;
  }
  
  private function hasMemberCardTicketLinkedToEvent(MemberCard $mc, Event $event)
  {
     foreach ( $mc->Tickets as $ticket ) {
        if ( $ticket->Manifestation->event_id == $event->id ) {
            return true;
        }
    }
    return false;
  }
  
  private function addMemberCardPriceToMemberCard(MemberCardPrice $mcp, MemberCard $mc, $quantity = 1)
  {
    $mcps = new Doctrine_Collection('MemberCardPrice');
    
    // nothing to add
    if ( $quantity == 0 ) {
        return $mcps;
    }
    
    // infinite quantity cases
    if ( $quantity < 0 ) {
        $quantity = 1;
    }
    
    // action !
    for ( $i = 0 ; $i < $quantity ; $i++ ) {
        $tmp = $mcp->copy();
        $tmp->MemberCard = $mc;
        $tmp->save();
        $mcps[] = $tmp;
    }
    
    return $mcps;
  }
  
  public function getActiveMemberCards($type_id = NULL)
  {
    $q = $this->createQueryForActiveMemberCards($type_id);
    return $q->execute();
  }
  
  public function getActiveMemberCardsForEvent(Event $event, $type_id = NULL)
  {
    $q = $this->createQueryForActiveMemberCards($type_id)
        ->leftJoin('mc.MemberCardPrices mcp')
        ->andWhere('mcp.event_id = ?', $event->id)
    ;
    return $q->execute();
  }
  
>>>>>>> 224760b... (tck) - Change the global behavior of member card associations - refs #4432 (#428)
  /**
   * This function returns available MemberCardPrices for a Transaction and optionaly a Manifestation
   *
   * @param $transaction    Transaction
   * @param $manifestation  Manifestation
   * @return Doctrine_Collection('MemberCardPrice')
   * @throw liEvenementException
   */
  public function getAvailableMCPrices(Transaction $transaction, Manifestation $manifestation = NULL)
  {
    $mcp = array();
    try {

    $mcs = $transaction->contact_id
      ? $transaction->getContact()->MemberCards
      : $transaction()->MemberCards;
    if ( $mcs->count() == 0 )
      return $mcp;
    
    // get back available prices
    foreach ( $mcs as $mc )
    if ( $mc->active || $mc->transaction_id == $transaction->id )
    foreach ( $mc->MemberCardPrices as $price )
    {
      $event_id = is_null($price->event_id) ? '' : $price->event_id;
      
      if ( $event_id && $manifestation instanceof Manifestation && $price->event_id != $manifestation->event_id )
        continue;
      
      if ( !isset($mcp[$price->price_id][$event_id]) )
        $mcp[$price->price_id][$event_id] = 0;
      
      $mcp[$price->price_id][$event_id]++;
    }
    
    // get back already booked tickets
    $q = Doctrine_Query::create()->from('Ticket tck')
      ->select('tck.*, m.event_id AS event_id')
      ->andWhere('tck.printed_at IS NULL')
      ->andWhere('tck.member_card_id IS NOT NULL OR t.id = ?', $transaction->id)
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('e.Manifestations em')
      ->leftJoin('tck.Price p')
      ->andWhere('p.member_card_linked = ?',true)
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.contact_id = ?',$transaction->Contact->id)
      ->leftJoin('t.Order o')
    ;
    if ( $manifestation )
      $q->andWhere('em.id = ?', $manifestation->id)
        ->andWhere('o.id IS NOT NULL OR t.id = ? AND tck.manifestation_id != em.id', $transaction->id)
      ;
    else
      $q->andWhere('o.id IS NOT NULL OR t.id = ?', $transaction->id);
      
    $tickets_to_count = $q->execute();
    foreach ( $tickets_to_count as $ticket )
    {
      if ( isset($mcp[$ticket->price_id][$ticket->event_id]) )
        $mcp[$ticket->price_id][$ticket->event_id]--;
      elseif ( isset($mpc[$ticket->price_id]['']) )
        $mcp[$ticket->price_id]['']--;
    }
    
    return $mcp;
    
    }
    catch ( liEvenementException $e )
    { return $mcp; }
  }
}
