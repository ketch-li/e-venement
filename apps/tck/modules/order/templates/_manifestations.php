<?php
  $manifs = array();
  foreach ( $order->Transaction->Tickets as $ticket )
    $manifs[$ticket->Manifestation->happens_at.' '.$ticket->manifestation_id] =
      cross_app_link_to($ticket->Manifestation->Event->name, 'event', 'event/show?id='.$ticket->Manifestation->event_id)
      .' '.__('at').' '
      .cross_app_link_to($ticket->Manifestation->getMiniDate(),'event','manifestation/show?id='.$ticket->manifestation_id);
  sort($manifs);
?>
<?php echo implode('<br/>',array_reverse($manifs)) ?>
