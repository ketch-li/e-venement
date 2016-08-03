<?php $museum = $sf_context->getConfiguration()->getApplication() == 'museum' ?>
<h2><?php echo $museum ? __('Confirmed visitors') : __('Confirmed spectators') ?></h2>
<table class="printed">
  <tbody>
  <?php $workspaces = array(); $total = array('qty' => array(), 'value' => 0, 'perso' => 0, 'pro' => 0,) ?>
  <?php $overlined = true ?>
  <?php if ( !isset($spectators) ) $spectators = $form->spectators ?>
  <?php foreach ( $spectators as $transac ): ?>
  <?php foreach ( array(true, false) as $has_direct_contact ): ?>
  <?php
    $transaction = $contact = $pro = array();
    $contact = array();
    foreach ( array('value' => array(), 'prices' => array(), 'tickets-nums' => array(), 'ticket-ids' => array()) as $key => $value )
      $contact[$key] = $value;
    $contact['transaction'] = $transac;
    $contact['contact'] = $transac->contact_id ? $transac->Contact : false;
    $contact['pro'] = $transac->professional_id ? $transac->Professional : false;
    $count = false;
    
    if ( !isset($transac->printed) ) // if the display needs to be totally explicit
    {
      foreach ( $transac->Tickets as $t )
      if ( ($t->printed_at || $t->integrated_at) && !$t->hasBeenCancelled()
        && ($has_direct_contact ? $t->getRawValue()->contact_id : !$t->getRawValue()->contact_id)
      )
      {
        // init data on every loop for tickets that has an embedded contact
        if ( $t->getRawValue()->contact_id )
        {
          foreach ( array('value' => array(), 'prices' => array(), 'tickets-nums' => array(), 'ticket-ids' => array()) as $key => $value )
            $contact[$key] = $value;
          $contact['pro'] = NULL;
          $contact['contact'] = $t->getRawValue()->DirectContact;
        }
        
        if ( !isset($contact['ticket-ids'][$t->Gauge->workspace_id]) )
          $contact['ticket-ids'][$t->Gauge->workspace_id] = array('name' => $t->Gauge->Workspace->name);
        $contact['ticket-ids'][$t->Gauge->workspace_id][$t->id] = $t->id;
        if ( !isset($contact['ticket-nums'][$t->Gauge->workspace_id]) )
          $contact['ticket-nums'][$t->Gauge->workspace_id] = array('name' => $t->Gauge->Workspace->name);
        if ( $t->numerotation )
          $contact['ticket-nums'][$t->Gauge->workspace_id][$t->id] = $t->numerotation;
        
        if ( !isset($contact['prices'][$t->Gauge->workspace_id]) )
          $contact['prices'][$t->Gauge->workspace_id] = array('name' => $t->Gauge->Workspace->name);
        isset($contact['prices'][$t->Gauge->workspace_id][$t->price_name])
          ? $contact['prices'][$t->Gauge->workspace_id][$t->price_name]++
          : $contact['prices'][$t->Gauge->workspace_id][$t->price_name] = 1;
        
        if ( !isset($contact['value'][$t->Gauge->workspace_id]) )
          $contact['value'][$t->Gauge->workspace_id] = 0;
        $contact['value'][$t->Gauge->workspace_id] += $t->value;
        
        if ( !isset($total['qty'][$t->gauge_id]) ) $total['qty'][$t->gauge_id] = 0;
        
        $total['qty'][$t->gauge_id]++;
        $workspaces[$t->gauge_id] = $t->Gauge->Workspace->name;
        $total['value'] += $t->value;
        
        // display tickets that has an embedded contact
        if ( $t->getRawValue()->contact_id )
        {
          ?><tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>"><?php
          include_partial('show_spectators_list_line',array(
            'transac' => $transac,
            'contact' => $contact,
            'ws'      => $contact['prices'][$t->Gauge->workspace_id],
            'show_workspaces' => $show_workspaces,
            'wsid'    => $t->Gauge->workspace_id,
          ));
          ?></tr><?php
          $contact['ticket-ids'] = array();
        }
        
        $count = true;
      }
    }
    elseif ( $transac->printed > 0 )
    {
      $contact['ticket-nums'][] = '-';
      $contact['ticket-ids'][] = '-';
      $contact['prices'][''] = $transac->printed;
      $contact['value'] = $transac->printed_value;
      $total['qty'][0] += $transac->printed;
      $total['value'] += $transac->printed_value;
      $count = $transac->printed > 0;
    }
    
    if ( $count )
    {
      if ( $transac->contact_id )
        $total['perso']++;
      if ( $transac->professional_id )
        $total['pro']++;
    }
  ?>
  <?php if ( $contact['ticket-ids'] ): ?>
  <?php foreach ( $contact['prices'] as $wsid => $ws ): ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <?php include_partial('show_spectators_list_line',array(
      'transac' => $transac,
      'contact' => $contact,
      'ws'      => $ws,
      'show_workspaces' => $show_workspaces,
      'wsid'    => $wsid,
    )) ?>
  </tr>
  <?php endforeach ?>
  <?php endif ?>
  <?php endforeach ?>
  <?php endforeach ?>
  </tbody>
  <?php include_partial('show_spectators_list_table_footer',array('total' => $total, 'workspaces' => $workspaces)) ?>
  <?php include_partial('show_spectators_list_table_header') ?>
</table>
