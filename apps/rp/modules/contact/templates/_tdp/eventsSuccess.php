<ul>
    <?php use_helper('Number') ?>
    <?php $type = $sf_request->getParameter('type','contact') == 'contact' ? 'Contact' : 'Professional' ?>
    <?php $total = array('ids' => array(), 'value' => 0) ?>
    <?php if ( $transactions->count() > 0 ): ?>
    <li class="events-<?php echo $type != 'Professional' ? 'object' : 'subobject-'.$sf_request->getParameter('id') ?>">
      <h3>
        <?php echo $object ?>
      </h3>
      <ul class="metaevents">
        <?php
          $events = $sort = array();
          
          // transactions
          foreach ( $transactions as $transaction )
          if ( $type == 'Contact'      && is_null($transaction->professional_id) && $transaction->contact_id === $object->id
            || $type == 'Professional' && $transaction->professional_id === $object->id )
          foreach ( $transaction->Tickets as $ticket )
            require(sfConfig::get('sf_app_template_dir').'/_tdp/side_widget_object_events_process_tickets.php');
          
          // direct tickets
          if ( $type == 'Contact' )
          foreach ( $transactions as $transaction )
          if ( $type == 'Contact'      && is_null($transaction->professional_id) && $transaction->contact_id !== $object->id
            || $type == 'Professional' && $transaction->professional_id !== $object->id )
          foreach ( $transaction->Tickets as $ticket )
          if ( $ticket->contact_id == $object->id )
            require(sfConfig::get('sf_app_template_dir').'/_tdp/side_widget_object_events_process_tickets.php');
          
          // sorting by manifestation's date
          foreach ( $events as $key => $metaevt )
            array_multisort($sort[$key],$events[$key]);
          $events = array_reverse($events, true);
        ?>
        <?php foreach ( $events as $meid => $meta_event ): ?>
        <!-- METAEVT -->
        <!-- <?php echo $meid ?> -->
        <li class="metaevent <?php echo !in_array($meid, array_keys($sf_user->getMetaEventsCredentials()->getRawValue())) ? 'hidden' : '' ?>" data-meta-event-id="<?php echo $meid ?>">
        <?php foreach ( $meta_event as $id => $event ): ?>
        <?php if ( $id === 'name' ): ?>
          <?php if ( method_exists($object->getRawValue(), 'getStatsSeatRank') ): ?>
            <?php $stats = $transaction->$type->getStatsSeatRank($meid); ?>
            <span class="seat-rank">
              <span class="qty"><?php echo __('Qty: %%qty%%', array('%%qty%%' => $stats['qty'])) ?></span>
              <span class="avg"><?php echo __('Avg: %%avg%%', array('%%avg%%' => format_number(number_format($stats['avg'],1)))) ?></span>
              <span class="std-dev"><?php echo __('Std deviation: %%sd%%', array('%%sd%%' => format_number(number_format($stats['std-dev'],1)))) ?></span>
            </span>
          <?php endif ?>
          <span class="name"><?php echo $event ?></span>
          <ul class="events">
        <?php else: ?>
          <!-- EVENT -->
          <li>
            <?php echo cross_app_link_to($event['event'],'event','event/show?id='.$event['event']->id,false,null,false, 'title="'.$event['title'].'"') ?>:
            <?php if ( $sf_user->hasCredential('tck-transaction') ): ?><span class="transactions ui-widget-content ui-corner-all"><?php echo implode('<br/>', $event['transaction_links']) ?></span><?php endif ?>
            <span class="nb"><?php echo count($event['ids']) ?></span>
            <?php if ( $sf_user->hasCredential('tck-ledger-sales') ): ?><span class="value"><?php echo format_currency($event['value'],$sf_context->getConfiguration()->getCurrency()) ?></span><?php endif ?>
          </li>
          <!-- /EVENT -->
        <?php endif ?>
        <?php endforeach ?>
        </ul></li>
        <!-- /METAEVT -->
        <?php endforeach ?>
        <li class="total">
          <?php if ( count($total['ids']) > 0 ): ?>
          <span class="event">Total</span>:
          <span class="nb"><?php echo count($total['ids']) ?></span>
            <?php if ( $sf_user->hasCredential('tck-ledger-sales') ): ?><span class="value"><?php echo format_currency($total['value'],$sf_context->getConfiguration()->getCurrency()) ?></span><?php endif ?>
          <?php endif ?>
        </li>
      </ul>
    </li>
    <?php else: ?>
    <li><?php echo __('No result',null,'sf_admin') ?></li>
    <?php endif ?>
</ul>
