<?php
  $manifs = array();
  
  foreach ( $order->Transaction->Tickets as $ticket )
  {
    $key = $ticket->manifestation_id;
    if ( !array_key_exists($key, $manifs) )
    {
      $links = cross_app_link_to($ticket->Manifestation->Event->name, 'event', 'event/show?id='.$ticket->Manifestation->event_id)
        .' '.__('at').' '
        .cross_app_link_to($ticket->Manifestation->getMiniDate(),'event','manifestation/show?id='.$ticket->manifestation_id);
      
      $manifs[$key] = $links;
    }
  }

?>
<?php echo implode('<br/>',array_reverse($manifs)) ?>
