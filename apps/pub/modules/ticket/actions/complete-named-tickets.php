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
    $this->debug($request);
    if (!( $mid = $request->getParameter('manifestation_id', false) ))
      throw new liEvenementException('The required parameter "manifestation_id" has not been found');
    
    $tickets = new Doctrine_Collection('Ticket');
    $manifs = $contacts = array();
    foreach ( $this->getUser()->getTransaction()->Tickets as $ticket )
    {
      if ( !isset($manifs[$ticket->manifestation_id]) )
        $manifs[$ticket->manifestation_id] = array();
      $manifs[$ticket->manifestation_id][] = $ticket;
      if ( $ticket->contact_id )
        $contacts[$ticket->manifestation_id][] = $ticket->contact_id;
      if ( $mid == $ticket->manifestation_id )
        $tickets[] = $ticket;
    }
    
    if ( $tickets->count() == 0 )
      return sfView::NONE;
    
    // templates the contacts from the current manifestation
    $biggest = array();
    foreach ( $contacts as $manifid => $contactids )
    if ( $manifid != $mid ) // every manifs except the current one
    {
      if ( count($biggest) < count($contactids) )
         $biggest = $contactids;
    }
    
    // removes the contacts already "taken"
    foreach ( $manifs[$mid] as $ticket )
    if ( $ticket->contact_id && in_array($ticket->contact_id, $biggest) )
      unset($biggest[array_search($ticket->contact_id, $biggest)]);
    
    // adds the rest of the contacts
    foreach ( $manifs[$mid] as $ticket )
    if ( $biggest && !$ticket->contact_id )
      $ticket->contact_id = array_shift($biggest);
    
    $tickets->save();
    $this->forward('ticket', 'modNamedTickets');
    return sfView::NONE;
?>
