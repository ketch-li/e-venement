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
  // Control one ticket with QRCode
  public function singleControlDemat($user, $event_id, $ticket_code, $post)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
    // Get ticket from code    
    $ticket = Doctrine::getTable('Ticket')->createQuery('tck')
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('e.Translation et')
      ->leftJoin('e.Checkpoints cp WITH cp.type = ?', 'entrance')
      ->leftJoin('tck.Controls c WITH c.checkpoint_id = cp.id')
      ->andWhere('tck.barcode = ?', $ticket_code)
      ->fetchOne();
    
    if ( !$ticket )
      throw new liApiNotFoundException(__("Ticket not found", null, 'ticket_service'), 1001);
      
    // Check if ticket is integrated
    if ( !$ticket->integrated_at )
      throw new liApiNotFoundException(__("The ticket has not been printed", null, 'ticket_service'), 1002);
      
    // Check if ticket manifestation matches event_id
    if ( $ticket->Manifestation->event_id != $event_id )
      throw new liApiBadRequestException(__("The ticket manifestation does not match the event id #%%EVENT_ID%%", array('%%EVENT_ID%%' => $event_id), 'ticket_service'), 1003);
      
    $checkpoint = null;
    // Get the checkpoint of the event
    if ( $ticket->Manifestation->Event->Checkpoints->count() == 0 )
      throw new liApiNotFoundException(__("No ENTRANCE checkpoint configured for event #%%EVENT_ID%%", array('%%EVENT_ID%%', $event_id), 'ticket_service'), 1004);
    else
      $checkpoint = $ticket->Manifestation->Event->Checkpoints[0];
      
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
    
    // Check if the ticket is already controled
    if ( $ticket->Controls->count() > 0 )
    {
      $failure = new FailedControl;
      $failure->complete([
        'ticket_id' => $ticket->id, 
        'checkpoint_id' => $checkpoint->id
      ]);
      
      throw new liApiNotFoundException(__("The ticket #%%TICKET_ID%% has already been controled", array('%%TICKET_ID%%' => $ticket->id), 'ticket_service'), 1007);
    }
    
    // The ticket is valid
    $control = new Control();
    $control->sf_guard_user_id = $user->getId();
    $control->ticket_id = $ticket->id;
    $control->checkpoint_id = $checkpoint->id;
    $control->comment = $post ? 'A posteriori' : '';
    $control->save();
    
    $ticket->Controls->add($control);
    
    return $ticket->id;
  }
  
}
