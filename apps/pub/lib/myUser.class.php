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

class myUser extends pubUser
{
  const CREDENTIAL_METAEVENT_PREFIX = 'event-metaevent-';
  const CREDENTIAL_WORKSPACE_PREFIX = 'event-workspace-';
  
  protected $metaevents = array();
  protected $workspaces = array();
  protected $transaction = NULL;
  protected $auth_exceptions = array();
  protected $origin_id = NULL;
  
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    parent::initialize($dispatcher, $storage, $options);
    $dispatcher->connect('pub.pre_execute', array($this, 'mustAuthenticate'));
    $dispatcher->connect('pub.before_showing_prices', array($this, 'checkAvailability'));
    $dispatcher->connect('pub.before_adding_tickets', array($this, 'checkAvailability'));
    $dispatcher->connect('pub.after_adding_tickets', array($this, 'addDefaultDirectContact'));
    
    if ( $this->getAttribute(sfConfig::get('app_user_session_ns').'_online_store', NULL) === NULL
      || time() > strtotime($this->getAttribute('online_store_timeout', NULL)) )
    {
      if ( !sfConfig::get('app_store_disabled', false) )
      {
        $q = Doctrine::getTable('ProductCategory')->createQuery('pc')
          ->andWhere('pc.online = ?', true)
          ->leftJoin('pc.Products p')
          ->andWhereIn('p.meta_event_id IS NULL OR p.meta_event_id', array_keys($this->getMetaEventsCredentials()))
          ->andWhere('p.id IS NOT NULL')
          ->leftJoin('p.Declinations d')
          ->andWhere('d.id IS NOT NULL')
        ;
        $online_store = $q->count() > 0;
      }
      else
        $online_store = false;
      
      $this->setAttribute(sfConfig::get('app_user_session_ns').'_online_store', $online_store);
      $this->setAttribute('online_store_timeout', date('Y-m-d H:i:s', strtotime('+20 minutes')));
    }
  }
  
  public function addDefaultDirectContact(sfEvent $event)
  {
    if ( isset($event['direct_contact']) && $event['direct_contact'] === false )
      return;
    
    // detecting if one ticket has to be affected to the current contact
    if ( $this->getTransaction()->contact_id )
    {
      $manifs = array();
      foreach ( $this->getTransaction()->Tickets as $ticket )
      {
        if ( !isset($manifs[$ticket->manifestation_id]) )
          $manifs[$ticket->manifestation_id] = array();
        $manifs[$ticket->manifestation_id][] = $ticket;
      }
      
      foreach ( $manifs as $manifid => $tickets )
      {
        $nocontactatall = true;
        foreach ( $tickets as $ticket )
        if ( $ticket->contact_id )
        {
          $nocontactatall = false;
          break;
        }
        
        if ( $nocontactatall )
        {
          $ticket->contact_id = $this->getTransaction()->contact_id;
          if ( !$ticket->seat_id )
            $ticket->Seat = NULL; // this is a hack to avoid errors after inserting a shadow seat
          $ticket->save();
        }
      }
    }
  }
  
  public function checkAvailability(sfEvent $event)
  {
    $event->setReturnValue(true);
    $manifestation = $event['manifestation'];
    $vel = sfConfig::get('app_tickets_vel', array());
    $max = array();
    
    // controlling the global max_per_manifestation parameter
    $vel['max_per_manifestation'] = isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9;
    if ( !(isset($vel['no_online_limit_from_manifestations']) && $vel['no_online_limit_from_manifestations'])
      && $manifestation->online_limit_per_transaction && $manifestation->online_limit_per_transaction < $vel['max_per_manifestation'] )
      $vel['max_per_manifestation'] = $manifestation->online_limit_per_transaction;
    foreach ( $this->getTransaction()->Tickets as $ticket )
    if ( !$ticket->hasBeenCancelled() && $manifestation->id == $ticket->manifestation_id )
      $vel['max_per_manifestation']--;
    $max[] = $vel['max_per_manifestation'];
    if ( $vel['max_per_manifestation'] < 0 )
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
      $event['message'] = __('You cannot book a ticket on this date because you have already reached the limit of tickets for %%manif%%', array(
        '%%manif%%' => $ticket->Manifestation,
        '%%transaction%%' => $ticket->transaction_id
      ));
      $event->setReturnValue(false);
    }
    
    if ( !$this->hasContact() )
      $transactions = false;
    else
    {
      $q = Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.contact_id = ?',$this->getContact()->id)
        ->leftJoin('m.Event e')
        ->andWhereIn('e.meta_event_id', array_keys($this->getMetaEventsCredentials()))
        ->leftJoin('m.Gauge g')
        ->andWhereIn('g.workspace_id', array_keys($this->getWorkspacesCredentials()))
        ->leftJoin('t.Order o')
      ;
      $transactions = $q->execute();
    }
    if ( $transactions )
    {
      $transactions = new Doctrine_Collection('Transaction');
      $transactions[] = $this->getTransaction();
    }
    
    // controlling if there is any max_per_event_per_contact conflict
    $vel['max_per_event_per_contact'] = isset($vel['max_per_event_per_contact']) ? $vel['max_per_event_per_contact'] : false;
    if ( $vel['max_per_event_per_contact'] && $vel['max_per_event_per_contact'] > 0 && $event->getReturnValue() )
    {
      $last_conflict = NULL;
      foreach ( $transactions as $transaction )
      foreach ( $transaction->Tickets as $ticket )
      if (( $ticket->printed_at || $ticket->integrated_at || $transaction->Order->count() > 0 || $ticket->transaction_id == $this->getTransaction()->id )
        && !$ticket->hasBeenCancelled()
        && $manifestation->id != $ticket->manifestation_id
        && $manifestation->event_id == $ticket->Manifestation->event_id
      )
      {
        $vel['max_per_event_per_contact']--;
        $last_conflict = $ticket;
      }
      $max[] = $vel['max_per_event_per_contact'];
      if ( $vel['max_per_event_per_contact'] <= 0 )
      {
        $event->setReturnValue(false);
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
        $event['message'] = __('You cannot book a ticket on this date because you already have a ticket booked for %%manif%%', array(
          '%%manif%%' => $last_conflict->Manifestation,
        ));
        $event->setReturnValue(false);
      }
    }
    
    // checks member cards / prices linked to member cards
    
    $event['max'] = min($max);
    
    // controlling if there is any time conflict
    if ( ($delay = sfConfig::get('app_tickets_no_conflict', false)) && $event->getReturnValue() )
    {
      $manifs = array();
      foreach ( $transactions as $transaction )
      foreach ( $transaction->Tickets as $ticket )
      if (( $ticket->transaction_id == $this->getTransaction()->id || $ticket->printed_at || $ticket->integrated_at || $transaction->Order->count() > 0 )
        && !$ticket->hasBeenCancelled()
        && ($manifestation->id != $ticket->manifestation_id || $ticket->transaction_id != $this->getTransaction()->id)
        && !isset($manifs[$ticket->manifestation_id])
      )
        $manifs[$ticket->manifestation_id] = $ticket;
      
      foreach ( $manifs as $ticket )
      if ( $manifestation->Event->meta_event_id == $ticket->Manifestation->Event->meta_event_id )
      {
        $start = strtotime('- '.$delay, strtotime($ticket->Manifestation->happens_at));
        $stop  = strtotime('+ '.$delay, strtotime($ticket->Manifestation->ends_at));
        if ( strtotime($manifestation->happens_at) <= $stop
          && strtotime($manifestation->ends_at) >= $start )
        {
          sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
          if ( $ticket->transaction_id == $this->getTransaction()->id )
            $event['message'] = __('You cannot book a ticket on this date because you already have a ticket booked for %%manif%%', array(
              '%%manif%%' => $ticket->Manifestation,
              '%%transaction%%' => $ticket->transaction_id
            ));
          else
            $event['message'] = __('You cannot book a ticket on this date because you already have a ticket booked for %%manif%% (in an other transaction #%%transaction%%)', array(
              '%%manif%%' => $ticket->Manifestation,
              '%%transaction%%' => $ticket->transaction_id
            ));
          $event->setReturnValue(false);
        }
      }
    }
  }
  
  public function isStoreActive()
  {
    return $this->getAttribute(sfConfig::get('app_user_session_ns').'_online_store', false);
  }
  
  public function mustAuthenticate(sfEvent $event)
  {
    $sf_action = $event->getSubject();
    
    // the action it self
    if ( in_array(array($sf_action->getModuleName(), $sf_action->getActionName()), $this->auth_exceptions) )
      return;
    
    // the user...
    if (!( method_exists($sf_action, 'isAuthenticatingModule') && $sf_action->isAuthenticatingModule() ))
    {
      if ( !sfConfig::get('app_user_must_authenticate', false) )
        return;
      
      if ( $this->hasContact() )
        return;
      
      // for plateforms that require authenticated visitors
      $sf_action->forward('login','index');
    }
  }
  public function addAuthException($module, $action)
  {
    $this->auth_exceptions[] = array($module, $action);
    return $this;
  }
  
  public function getGuardUser()
  {
    if ( !sfConfig::get('app_open', false) )
      return false;
    
    if (!$this->user )
      $this->user = Doctrine::getTable('sfGuardUser')->retrieveByUsername(sfConfig::get('app_user_templating',-1));
    
    if (!$this->user)
    {
      // the user does not exist anymore in the database
      $this->signOut();
      
      throw new sfException('The user does not exist anymore in the database.');
    }
    
    return $this->user;
  }

  public function getWorkspacesCredentials()
  {
    $this->getGuardUser();
    if ( $this->workspaces )
      return $this->workspaces;
    
    $this->workspaces = array();
    
    if ( !$this->user )
      return $this->workspaces;
    
    foreach ( $this->user->Workspaces as $ws )
      $this->workspaces[$ws->id] = myUser::CREDENTIAL_WORKSPACE_PREFIX.$ws->id;
    
    return $this->workspaces;
  }
  public function getMetaEventsCredentials()
  {
    $this->getGuardUser();
    if ( $this->metaevents )
      return $this->metaevents;
    
    $this->metaevents = array();
    
    if ( !$this->user )
      return $this->metaevents;
    
    foreach ( $this->user->MetaEvents as $me )
      $this->metaevents[$me->id] = myUser::CREDENTIAL_METAEVENT_PREFIX.$me->id;
    
    return $this->metaevents;
  }
  
  public function getContact()
  {
    if ( is_null($this->getTransaction()->contact_id) )
      throw new liOnlineSaleException('Not yet authenticated.');
    
    return $this->getTransaction()->Contact;
  }
  public function hasContact()
  {
    return !is_null($this->getTransaction()->contact_id);
  }
  public function setContact(Contact $contact)
  {
    if ( !$contact->id )
      throw new liOnlineSaleException('Your contact is not yet recorded or does not fit the system requirements');
    
    $this->getTransaction()->Contact = $contact;
    $this->getTransaction()->contact_id = $contact->id;
    foreach ( $this->getTransaction()->MemberCards as $mc )
      $mc->Contact = $contact;
    $this->getTransaction()->save();
    return $this;
  }
  
  public function getTransactionId()
  {
    if ( !$this->hasAttribute('transaction_id') )
    {
      $this->transaction = new Transaction;
      $this->dispatcher->notify(new sfEvent($this, 'pub.transaction_before_creation', array(
        'transaction' => $this->transaction,
        'user' => $this,
      )));
      
      $this->transaction->save();
      $this->setTransaction($this->transaction);
      
      $this->dispatcher->notify(new sfEvent($this, 'pub.transaction_after_creation', array(
        'transaction' => $this->transaction,
        'user' => $this,
      )));
    }
    
    return $this->getAttribute('transaction_id');
  }
  public function getTransaction($reset = false)
  {
    $tid = $this->getTransactionId();
    if ( !$reset && $this->transaction instanceof Transaction )
      return $this->transaction;
      
    $q = Doctrine::getTable('Transaction')->createQuery('t')
      ->leftJoin('t.Order o')
      ->leftJoin('t.Contact c')
      ->leftJoin('c.Professionals p WITH p.id = t.professional_id')
      ->leftJoin('c.MemberCards cmc WITH (cmc.active = ? AND cmc.expire_at > NOW() OR cmc.transaction_id = t.id)', true)
      //->leftJoin('cmc.MemberCardPrices cmcp') // <- can be very very long if member cards are componed by a lot of prices, and this can be found back automatically w/ doctrine w/o any side-effect
      ->andWhere('t.id = ?',$tid)
    ;
    
    if ( sfConfig::get('sf_web_debug', false) )
    {
      if ( ($this->transaction = $q->fetchOne())
        && $this->transaction->Order->count() == 0 )
        return $this->transaction;
    }
    else
    if ( $this->transaction = $q->fetchOne() )
      return $this->transaction;
    
    $this->resetTransaction();
    return $this->getTransaction();
  }
  public function setTransaction(Transaction $transaction)
  {
    $this->transaction = $transaction;
    $this->setAttribute('transaction_id',$this->transaction->id);
  }
  
  public function resetTransaction()
  {
    $professional_id = $contact = false;
    if ( $this->getAttribute('transaction_id',false) && $this->hasContact() )
    {
      $contact = $this->getContact();
      if ( sfConfig::get('app_contact_professional', false) )
        $professional_id = $this->getTransaction()->professional_id;
    }
    
    $this->setOriginId();
    $this->getAttributeHolder()->remove('transaction_id');
    $this->transaction = NULL;
    $this->getTransaction();
    
    if ( $contact )
    {
      if ( sfConfig::get('app_contact_professional', false) && $professional_id )
        $this->getTransaction()->professional_id = $professional_id;
      $this->setContact($contact);
    }
    
    return $this;
  }
  public function logout()
  {
    if ( $this->getTransaction()->Order->count() == 0 )
    foreach ( $this->transaction->Tickets as $i => $ticket )
    if ( is_null($ticket->printed_at) && is_null($ticket->integrated_at) )
      $this->transaction->Tickets[$i]->delete();
    
    $this->setOriginId();
    $this->getAttributeHolder()->remove('transaction_id');
    $this->transaction = NULL;
    $this->getTransaction();
    
    return $this;
  }
  
  public function setOriginId()
  {
    $this->origin_id = $this->getAttribute('transaction_id',false);
    return $this;
  }
  public function getOriginId()
  {
    return $this->origin_id;
  }
  
  
  public function setDefaultCulture(array $languages)
  {
    $cultures = array_keys(sfConfig::get('project_internals_cultures', array('fr' => 'Français')));
    
    if ( !$this->getAttribute('global_culture_forced', false) )
    {
      // all the browser's languages
      $user_langs = array();
      foreach ( $languages as $lang )
      if ( !isset($user_lang[substr($lang, 0, 2)]) )
        $user_langs[substr($lang, 0, 2)] = $lang;
      
      // comparing to the supported languages
      $done = false;
      foreach ( $user_langs as $culture => $lang )
      if ( in_array($culture, $cultures) )
      {
        $done = $culture;
        $this->setCulture($culture);
        break;
      }
      
      // culture by default
      if ( !$done )
        $this->setCulture($cultures[0]);
    }
    
    return $this;
  }
  
  public function getAvailableMCPrices($manifestation = NULL)
  {
    $mcp = array();
    try {

    $mcs = $this->getTransaction()->contact_id
      ? $this->getContact()->MemberCards
      : $this->getTransaction()->MemberCards;
    if ( $mcs->count() == 0 )
      return $mcp;
    
    // get back available prices
    foreach ( $mcs as $mc )
    if ( $mc->active || $mc->transaction_id == $this->getTransactionId() )
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
      ->andWhere('tck.member_card_id IS NOT NULL OR t.id = ?', $this->getTransactionId())
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('e.Manifestations em')
      ->leftJoin('tck.Price p')
      ->andWhere('p.member_card_linked = ?',true)
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.contact_id = ?',$this->getContact()->id)
      ->leftJoin('t.Order o')
    ;
    if ( $manifestation )
      $q->andWhere('em.id = ?', $manifestation->id)
        ->andWhere('o.id IS NOT NULL OR t.id = ? AND tck.manifestation_id != em.id', $this->getTransactionId())
      ;
    else
      $q->andWhere('o.id IS NOT NULL OR t.id = ?', $this->getTransactionId());
      
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
