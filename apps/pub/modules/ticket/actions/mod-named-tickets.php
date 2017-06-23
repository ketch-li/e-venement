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
  $this->debug($request);
  if ( !$request->getParameter('manifestation_id', false) )
    throw new liOnlineSaleException('To access named tickets, you need a manifestation_id parameter. None given');
  
  $this->getContext()->getConfiguration()->loadHelpers('Number');
  
  $q = Doctrine::getTable('Ticket')->createQuery('tck')
    ->andWhere('tck.manifestation_id = ?', $request->getParameter('manifestation_id'))
    ->andWhere('tck.transaction_id = ?', $request->getParameter('transaction_id', $this->getUser()->getTransactionId()))
    //->andWhere('tck.printed_at IS NULL')
    //->andWhere('tck.integrated_at IS NULL')
    ->andWhere('tck.cancelling IS NULL')
    //->andWhere('tck.duplicating IS NULL')
    //->andWhere('tck.price_id IS NOT NULL')
    
    ->leftJoin('tck.Seat s')
    ->leftJoin('tck.DirectContact c')
    ->leftJoin('tck.Gauge g')
    ->leftJoin('g.Workspace ws')
    ->leftJoin('tck.Price p')
    ->leftJoin('p.Translation pt WITH pt.lang = ?', $this->getUser()->getCulture())
    
    ->leftJoin('tck.Transaction t')
    ->andWhere('t.closed = ?', false)
    
    ->leftJoin('tck.DirectContact dc')
    ->select('tck.*, dc.*')
    ->orderBy('ws.name, pt.name, tck.value')
  ;
  if ( $this->getUser()->getTransaction()->contact_id )
    $q->andWhere('t.contact_id = ?', $this->getUser()->getTransaction()->contact_id);
  
  // current transaction
  if ( !$request->getParameter('transaction_id') )
    $q->leftJoin('t.Order o')
      ->andWhere('o.id IS NULL');
  else // a transaction already booked
    $q->leftJoin('tck.Manifestation m')
      ->andWhere('m.happens_at > now()');
  
  // for a specific ticket
  if ( $request->getParameter('ticket_id', false) && intval($request->getParameter('ticket_id')).'' == ''.$request->getParameter('ticket_id') )
    $q->andWhere('tck.id = ?', $request->getParameter('ticket_id'));
  $tickets = $q->execute();
  
  // POST data
  $data = $request->getParameter('ticket');
  if ( isset($data['%%ticket_id%%']) )
    unset($data['%%ticket_id%%']);
  
  /* we deal with this in JS...
  // reset every other ticket that has %%ME%% as a contact
  $force = false;
  foreach ( $data as $id => $ticket )
  if ( isset($ticket['contact']['force']) && $data[$ticket->id]['contact']['force'] == 'true' )
    $force = true;
  if ( $force )
  foreach ( $data as $id => $ticket )
  if (!( isset($ticket['contact']['force']) && $data[$ticket->id]['contact']['force'] == 'true' )
    && $ticket['contact']['id'] == $this->getUser()->getContact()->id )
  foreach ( $ticket['contact'] as $name => $value )
    $data[$id][$name] = '';
  */
  
  $to_delete = new Doctrine_Collection('Ticket');
  $this->data = array();
  foreach ( $tickets as $key => $ticket )
  {
    $no_direct_contact = false; // true if it is a deletion of the current contact
    
    // the DB data
    if ( isset($data[$ticket->id]) )
    {
      if ( isset($data[$ticket->id]['comment']) && $ticket->comment != $data[$ticket->id]['comment'] )
        $ticket->comment = $data[$ticket->id]['comment'];
      
      if ( isset($data[$ticket->id]['contact']['force']) && $data[$ticket->id]['contact']['force'] == 'true' )
      {
        // force contact to "me" / current contact_id, w/o updating contact's information
        try { $ticket->DirectContact = $this->getUser()->getContact(); }
        catch ( liOnlineSaleException $e ) {}
      }
      else
      {
        // if one field is not set
        foreach ( array('name', 'firstname', 'email') as $field )
        if (!( isset($data[$ticket->id]['contact']) && isset($data[$ticket->id]['contact'][$field]) && $data[$ticket->id]['contact'][$field] )
         && !( $ticket->contact_id && $data[$ticket->id]['contact'][$field] == $ticket->DirectContact->$field ))
        {
          if ( $ticket->DirectContact instanceof Contact && $ticket->DirectContact->confirmed )
            $ticket->DirectContact = NULL;
          else
            unset($ticket->DirectContact);
          $ticket->contact_id = NULL;
          
          $no_direct_contact = true;
          break;
        }
        
        // if it is not a deletion case...
        if ( !$no_direct_contact )
        foreach ( array('title', 'name', 'firstname', 'email') as $field )
        // if one field is different from its predecessor
        if ( isset($data[$ticket->id]['contact'][$field]) && $data[$ticket->id]['contact'][$field] )
        if (!( $ticket->contact_id && $ticket->DirectContact->$field == $data[$ticket->id]['contact'][$field] ))
        {
          // if no direct contact is defined yet
          // or if the last contact was not confirmed yet
          // we can modify such a contact getting back its homonyms
          if (!( $ticket->contact_id && $ticket->DirectContact->confirmed ))
          {
            $get_keywords = function($search)
            {
              $nb = mb_strlen($search);
              $charset = sfConfig::get('software_internals_charset');
              $transliterate = sfConfig::get('software_internals_transliterate',array());
              
              $search = str_replace(preg_split('//u', $transliterate['from'], -1), preg_split('//u', $transliterate['to'], -1), $search);
              $search = str_replace(MySearchAnalyzer::$cutchars,' ',$search);
              $search = mb_strtolower(iconv($charset['db'],$charset['ascii'], mb_substr($search,$nb-1,$nb) == '*' ? mb_substr($search,0,$nb-1) : $search));
              
              return explode(' ', preg_replace('/\s+/', ' ', $search));
            };
            
            // Search for an existing contact
            $q = Doctrine_Query::create()->from('Contact c')
              ->orderBy('c.updated_at DESC')
              ->limit(1) // minimize the result treatment
              ->andWhere('c.email = ?', strtolower(trim($data[$ticket->id]['contact']['email'])))
            ;
            
            $i = 0;
            foreach ( array('firstname', 'name',) as $field )
            foreach ( $get_keywords($data[$ticket->id]['contact'][$field]) as $keyword )
            {
              $i++;
              $alias = "ci$i";
              $s = Doctrine::getTable('ContactIndex')->createQuery($alias)
                ->select("$alias.id")
                ->andWhere("$alias.field = ?", $field)
                ->andWhere("$alias.keyword = ?", $keyword)
              ;
              $q->andWhere("c.id IN ($s)", $s->getParams()['where']);
            }
            $contact = $q->fetchOne();
            
            if ( $contact )
              $ticket->DirectContact = $contact;
            else
            {
              $ticket->DirectContact = new Contact;
              if ( $ticket->Transaction->Order->count() == 0 )
                $ticket->DirectContact->confirmed = false;
            }
          } // we can modify such a contact getting back its homonyms
          
          // modifying the fields
          foreach ( array('title', 'name', 'firstname', 'email') as $field )
            $ticket->DirectContact->$field = trim($data[$ticket->id]['contact'][$field]);
          $ticket->DirectContact->email = strtolower($ticket->DirectContact->email);
          
          $validator = new sfValidatorEmail;
          try {
            $ticket->DirectContact->email = $validator->clean($ticket->DirectContact->email);
          }
          catch ( sfValidatorError $e )
          {
            error_log('bad contact informations');
            if ( $ticket->DirectContact->confirmed )
              $ticket->contact_id = NULL;
            else
              unset($ticket->DirectContact);
          }
          
          break; // only 1 loop is sufficient to process 1 ticket/contact, if at least 1 field differs
        }
      }
      
      // delete the ticket (if not getting back a transaction already paid)
      if ( !$request->getParameter('transaction_id')
        && !( isset($data[$ticket->id]['price_id']) && $data[$ticket->id]['price_id'] ))
      {
        $to_delete[] = $ticket;
        continue;
      }
      // set another price_id (if not getting back a transaction already paid)
      if ( !$request->getParameter('transaction_id')
        && $this->getUser()->getTransaction()->Order->count() == 0
        && $this->getUser()->getTransaction()->Payments->count() == 0
        && !$ticket->printed_at && !$ticket->integrated_at
        && $data[$ticket->id]['price_id'] != $ticket->price_id
        && in_array($ticket->price_id, $ticket->Gauge->Workspace->Prices->getPrimaryKeys())
        && ($price = Doctrine::getTable('Price')->find($data[$ticket->id]['price_id']))
        && $price->isAccessibleBy($this->getUser(), $ticket->Manifestation) )
      {
        $ticket->value    = NULL;
        $ticket->price_id = $data[$ticket->id]['price_id'];
      }
      
      $ticket->save();
    }
    
    // available prices
    $order = $prices = $tmp = array();
    if ( sfConfig::get('app_options_synthetic_plans', false) )
    {
      foreach ( $ticket->Manifestation->PriceManifestations as $pm )
      if ( $pm->Price->isAccessibleBy($this->getUser(),  $ticket->Manifestation) )
      if ( in_array($ticket->Gauge->workspace_id, $pm->Price->Workspaces->getPrimaryKeys()) )
      {
        $order[$pm->price_id] = $pm->value;
        $tmp[$pm->price_id] = ($pm->Price->description ? $pm->Price->description : (string)$pm->Price).' ('.format_currency($pm->value,$this->getContext()->getConfiguration()->getCurrency()).')';
      }
      foreach ( $ticket->Gauge->PriceGauges as $pg )
      if ( $pg->Price->isAccessibleBy($this->getUser(), $ticket->Manifestation) )
      if ( $ticket->gauge_id == $pg->gauge_id )
      {
        $order[$pg->price_id] = $pg->value;
        $tmp[$pg->price_id] = ($pg->Price->description ? $pg->Price->description : (string)$pg->Price).' ('.format_currency($pg->value,$this->getContext()->getConfiguration()->getCurrency()).')';
      }
      arsort($order);
      foreach ( $order as $pid => $value )
        $prices[''.$pid] = $tmp[$pid];
    }
    
    // the json data
    $this->data[$ticket->manifestation_id.' '.$ticket->Seat.' '.$ticket->id] = array(
      'id'                => $ticket->id,
      'seat_name'         => (string)$ticket->Seat,
      'seat_id'           => $ticket->seat_id,
      'price_name'        => !$ticket->price_id ? '' : ($ticket->Price->description ? $ticket->Price->description : (string)$ticket->Price),
      'price_id'          => $ticket->price_id,
      'prices_list'       => $prices,
      'value'             => $ticket->price_id ? format_currency($ticket->value, $this->getContext()->getConfiguration()->getCurrency()) : '',
      'taxes'             => floatval($ticket->taxes) ? format_currency($ticket->taxes, $this->getContext()->getConfiguration()->getCurrency()) : '',
      'gauge_name'        => $ticket->Gauge->group_name ? $ticket->Gauge->group_name : (string)$ticket->Gauge,
      'gauge_id'          => $ticket->gauge_id,
      'contact_id'        => $ticket->contact_id,
      'contact_title'     => $ticket->contact_id ? $ticket->DirectContact->title     : NULL,
      'contact_name'      => $ticket->contact_id ? $ticket->DirectContact->name      : NULL,
      'contact_firstname' => $ticket->contact_id ? $ticket->DirectContact->firstname : NULL,
      'contact_email'     => $ticket->contact_id ? $ticket->DirectContact->email     : NULL,
      'force'             => '',
      'comment'           => $ticket->comment,
    );
  }
  
  $event = new sfEvent($this, 'pub.after_adding_tickets', array(
    'tickets' => $tickets,
    'direct_contact' => sfConfig::get('app_tickets_direct_contact', 'auto') == 'auto'
  ));
  if ( sfConfig::get('app_tickets_direct_contact', 'auto') == 'auto' && isset($no_direct_contact) && $no_direct_contact )
    $event['direct_contact'] = false;
  $this->dispatcher->notify($event);
  
  ksort($this->data);
  // delete stored-for-deletion tickets (if not getting back a transaction already paid)
  if ( !$request->getParameter('transaction_id') )
    $to_delete->delete();
  return 'Success';
