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
      throw new liApiNotFoundException("Ticket not found", 1001);
      
    // Check if ticket is integrated
    if ( !$ticket->integrated_at )
      throw new liApiNotFoundException("The ticket has not been printed", 1002);
      
    // Check if ticket manifestation matches event_id
    if ( $ticket->Manifestation->event_id != $event_id )
      throw new liApiBadRequestException("The ticket manifestation does not match the event id #" . $event_id, 1003);
      
    $checkpoint = null;
    // Get the checkpoint of the event
    if ( $ticket->Manifestation->Event->Checkpoints->count() == 0 )
      throw new liApiNotFoundException("No ENTRANCE checkpoint configured for event #" . $event_id, 1004);
    else
      $checkpoint = $ticket->Manifestation->Event->Checkpoints[0];
      
    // Check if the control checking is available for the manifestation
    $past = sfConfig::get('app_control_past') ? sfConfig::get('app_control_past') : '6 hours';
    $future = sfConfig::get('app_control_future') ? sfConfig::get('app_control_future') : '1 day';
    
    if ( $ticket->Manifestation->happens_at > date('Y-m-d H:i', strtotime('now + ' . $future)) )
      throw new liApiNotFoundException("The control for manifestation #" . $ticket->Manifestation->id . " is not available yet", 1005);
      
    // Check if the manifestation is over to allow control after the end
    if ( strtotime($ticket->Manifestation->ends_at) < strtotime('now - '.$past) && !$post )
      throw new liApiNotFoundException("The control for manifestation #" . $ticket->Manifestation->id . " is over", 1006);
    
    // Check if the ticket is already controled
    if ( $ticket->Controls->count() > 0 )
    {
      $failure = new FailedControl;
      $failure->complete([
        'ticket_id' => $ticket->id, 
        'checkpoint_id' => $checkpoint->id
      ]);
      
      throw new liApiNotFoundException("The ticket has already been controled", 1007);
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
