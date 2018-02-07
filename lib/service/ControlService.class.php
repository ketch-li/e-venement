<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ControlService
 *
 * @author libre-informatique.fr
 */
class ControlService extends EvenementService
{
  protected function getUserId()
  {
    $id = null;
    $sf_user = sfContext::getInstance()->getUser();
    
    if ( $sf_user )
    {
      $id = $sf_user->getId();
    }
    
    return $id;
  }
  
  protected function getTicketService()
  {
    return sfContext::getInstance()->getContainer()->get('ticket_service');
  }
  
  protected function isTicketCode($code)
  {
    return preg_match('/^[a-f0-9]{32}$/', $code); // md5 match
  }

  protected function isMemberCardCode($code)
  {
    $card_id = null;
    
    $tmp = json_decode($code, true, 512, JSON_BIGINT_AS_STRING);
    
    if ( is_array($tmp) && array_key_exists('type', $tmp) && $tmp['type'] == 'MemberCard' )
    {
       $card_id = $tmp['member_card_id'];
    }
    
    return $card_id;
  }

  protected function isExitCode($code)
  {
    return $code == 'exit';
  }

  protected function createTicket($card, $manifestation)
  {
    $mc_price = Doctrine::getTable('MemberCardPrice')->getOneByCard($card->id, $manifestation->event_id, $manifestation->id);
    if ( !$mc_price )
    {
      throw new Exception(
        __('The membercard "%%mc%%" is not valid for the event "%%event%%".', 
          array('%%mc%%' => $card->id, '%%event%%' => $manifestation->Event)),
        1101
      );
    }
    
    $gauge_id = null;
    foreach ($manifestation->Gauges as $gauge)
    {
      if ( $gauge->free > 0  && in_array($gauge->workspace_id, $mc_price->Price->Workspaces->getPrimaryKeys()) )
      {
        $gauge_id = $gauge->id;
      }
    }
    
    if ( !$gauge_id )
    {
      throw new Exception(__('No gauge available at the moment.'), 1102);
    }
    
    $transaction = new Transaction();
    $transaction->contact_id = $card->contact_id;
    
    $ticket = new Ticket();
    $ticket->Transaction = $transaction;
    $ticket->automatic = true;
    $ticket->Manifestation = $manifestation;
    $ticket->Price = $mc_price->Price;
    $ticket->price_id = $mc_price->price_id;
    $ticket->value = $mc_price->Price->value;
    $ticket->gauge_id = $gauge_id;
    $ticket->save();
    
    // Update ticket to auto-integrate and link to member card
    $ticket->integrated_at = date('Y-m-d H:i:s');
    $ticket->save();
    
    return $ticket;
  }

  protected function getCurrentManifestation()
  {
    $manifestation = Doctrine::getTable('Manifestation')->retrieveCurrent();
    
    if ( !$manifestation )
    {
      throw new Exception(__('No manifestation currently available'), 1103);
    }
    
    return $manifestation;
  }

  public function controlCard($card_id)
  {
    $card = Doctrine::getTable('MemberCard')->findOneById($card_id);
    if ( !$card )
    {
      throw new Exception(__('The membercard "%%mc%%" does not exist.', array('%%mc%%' => $card_id)), 1104);
    }
    
    $manifestation = $this->getCurrentManifestation();    
    $ticket = Doctrine::getTable('Ticket')->getOneFromMembercard($card->id, $manifestation->id);
    if ( !$ticket )
    {
      $ticket = $this->createTicket($card, $manifestation);
    }
    
    return $this->controlTicket($ticket->barcode);
  }
  
  public function controlTicket($ticket_code)
  {
    return $this->getTicketService()->singleAutoControlDemat($this->getUserId(), $ticket_code);
  }

  public function controlExit()
  {
    $manifestation = $this->getCurrentManifestation();
    $ticket = Doctrine::getTable('Ticket')->getLastControlled($manifestation->id);
    
    if ( !$ticket )
    {
      throw new Exception(__('No ticket to control.'), 1105);
    }
    
    $checkpoint = Doctrine::getTable('Checkpoint')->getFromManifestation($manifestation->id, 'exit');
    
    return $this->getTicketService()->controlExit($this->getUserId(), $ticket, $checkpoint->id);
  }

  public function control($code)
  {
    if ( !$code )
    {
      throw new Exception(__('No valid code provided.'), 1100);
    }
    
    if ( $this->isExitCode($code) )
    {
      return $this->controlExit();
    }
    
    if ( $card_code = $this->isMemberCardCode($code) )
    {
      return $this->controlCard($card_code);
    }
    
    if ( $this->isTicketCode($code) )
    {
      return $this->controlTicket($code);
    }
  }
}
