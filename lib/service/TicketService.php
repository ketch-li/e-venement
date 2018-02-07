<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TicketService
 *
 * @author libre-informatique.fr
 */
class TicketService extends EvenementService
{
  protected function checkTicket(Ticket $ticket)
  {
    if ( !$ticket )
      throw new liApiNotFoundException(__("Ticket not found", null, 'ticket_service'), 1001);
      
    // Check if ticket is integrated
    if ( !$ticket->integrated_at )
      throw new liApiNotFoundException(__("The ticket has not been printed", null, 'ticket_service'), 1002);
  }
  
  protected function createControl($user_id, $ticket_id, $checkpoint_id, $comment = '')
  {
    $control = new Control();
    $control->sf_guard_user_id = $user_id;
    $control->ticket_id = $ticket_id;
    $control->checkpoint_id = $checkpoint_id;
    $control->comment = $comment;
    $control->save();
    
    return $control;
  }
  
  // Control one ticket with QRCode
  protected function controlDemat($user_id, $event_id, Ticket $ticket, $post)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
    $this->checkTicket($ticket);

    // Check if ticket manifestation matches event_id
    if ( $ticket->Manifestation->event_id != $event_id )
      throw new liApiBadRequestException(__("The ticket manifestation does not match the event id #%%EVENT_ID%%", array('%%EVENT_ID%%' => $event_id), 'ticket_service'), 1003);
      
    // Get the checkpoint of the event
    $checkpoint = Doctrine::getTable('Checkpoint')->getFromManifestation($ticket->manifestation_id);
    
    if ( $checkpoint->event_id != $event_id )
      throw new liApiNotFoundException(__("No ENTRANCE checkpoint configured for event #%%EVENT_ID%%", array('%%EVENT_ID%%', $event_id), 'ticket_service'), 1004);
      
    // Check if the control checking is available for the manifestation
    $past = sfConfig::get('app_control_past') ? sfConfig::get('app_control_past') : '6 hours';
    $future = sfConfig::get('app_control_future') ? sfConfig::get('app_control_future') : '1 day';
    
    $start = date('Y-m-d H:i', strtotime('now + ' . $future));
    $end = strtotime('now - '.$past);
    
    if ( $ticket->Manifestation->happens_at > $start )
      throw new liApiNotFoundException(__("The control for manifestation #%%MANIFESTATION_ID%% is not available yet", array('%%MANIFESTATION_ID%%' => $ticket->Manifestation->id), 'ticket_service'), 1005);
      
    if ( $ticket->Manifestation->happens_at < $start && strtotime($ticket->Manifestation->ends_at) > $end && $post ) {
      throw new liApiNotFoundException(__("The manifestation #%%MANIFESTATION_ID%% is not over yet", array('%%MANIFESTATION_ID%%' => $ticket->Manifestation->id), 'ticket_service'), 1008);
    }
      
    // Check if the manifestation is over to allow control after the end
    if ( strtotime($ticket->Manifestation->ends_at) < $end && !$post )
      throw new liApiNotFoundException(__("The control for manifestation #%%MANIFESTATION_ID%% is over", array('%%MANIFESTATION_ID%%' => $ticket->Manifestation->id), 'ticket_service'), 1006);
    
    // Check if the ticket is already controlled
    if ( $ticket->Controls->count() > 0 )
    {
      $failure = new FailedControl;
      $failure->complete([
        'ticket_id' => $ticket->id, 
        'checkpoint_id' => $checkpoint->id
      ]);
      
      throw new liApiNotFoundException(__("The ticket #%%TICKET_ID%% has already been controlled", array('%%TICKET_ID%%' => $ticket->id), 'ticket_service'), 1007);
    }
    
    // The ticket is valid
    $control = $this->createControl($user_id, $ticket->id, $checkpoint->id, $post ? 'A posteriori' : '');
    $ticket->Controls->add($control);
    
    return $ticket;
  }
  
  public function singleAutoControlDemat($user_id, $ticket_code)
  {
    $ticket = Doctrine::getTable('Ticket')->getOneFromCode($ticket_code, 'entrance');
    $event_id = $ticket->Manifestation->event_id;
    
    return $this->controlDemat($user_id, $event_id, $ticket, false);
  }

  public function singleControlDemat($user_id, $event_id, $ticket_code, $post)
  {
    $ticket = Doctrine::getTable('Ticket')->getOneFromCode($ticket_code, 'entrance');
    
    return $this->controlDemat($user_id, $event_id, $ticket, $post);
  }

  public function controlExit($user_id, Ticket $ticket, $checkpoint_id)
  {
    $this->checkTicket($ticket);
    
    if ( !$ticket->isControlled('entrance') )
    {
      throw new Exception(__('The ticket #%%TICKET_ID%% has not been controlled at the entrance.', array('%%TICKET_ID%%' => $ticket->id), 'ticket_service'));
    }
    
    if ( $ticket->isControlled('exit') )
    {
      throw new Exception(__('The ticket #%%TICKET_ID%% has already been controlled at the exit.', array('%%TICKET_ID%%' => $ticket->id), 'ticket_service'));
    }
    
    $this->createControl($user_id, $ticket->id, $checkpoint_id);
    
    return $ticket;
  }
  
}
