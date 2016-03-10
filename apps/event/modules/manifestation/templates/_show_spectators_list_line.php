    <?php
      $arr = array();
      if ( $contact['contact'] )
      {
        foreach ( $contact['contact']->YOBs as $yob )
          $arr[] = (string)$yob;
        foreach ( $contact['contact']->Phonenumbers as $pn )
          $arr[] = (string)$pn;
      }
    ?>
    <td class="name" title="<?php echo implode(', ', $arr) ?>">
      <?php echo cross_app_link_to($contact['contact'],'rp','contact/show?id='.$contact['contact']->id) ?>
      <span class="pictos"><?php if ( $contact['contact'] ) echo $sf_data->getRaw('transac')->Contact->groups_picto ?></span>
    </td>
    <td class="pro-groups"><?php echo $contact['pro'] ? $sf_data->getRaw('contact')['pro']->groups_picto : '' ?></td>
    <?php
      $arr = array();
      if ( $contact['pro'] )
      {
        if ( trim($contact['pro']->contact_number) )
          $arr[] = __('Direct phonenumber').': '.$contact['pro']->contact_number;
        foreach ( $contact['pro']->Organism->Phonenumbers as $pn )
          $arr[] = (string)$pn;
      }
    ?>
    <td class="organism" title="<?php echo implode(', ',$arr) ?>">
      <?php if ( $contact['pro'] ) echo cross_app_link_to($contact['pro']->Organism,'rp','organism/show?id='.$contact['pro']->Organism->id) ?>
      <?php echo $contact['pro'] ? $sf_data->getRaw('contact')['pro']->Organism->groups_picto : '' ?>
    </td>
    <td class="tickets"><?php include_partial('show_spectators_list_tickets',array('tickets' => $ws, 'show_workspaces' => $show_workspaces)) ?></td>
    <td class="price"><?php echo format_currency($contact['value'][$wsid],$sf_context->getConfiguration()->getCurrency()) ?></td>
    <td class="accounting"><?php if ( $contact['transaction']->Invoice[0]->id ): ?>#<?php echo $contact['transaction']->Invoice[0]->id ?><?php else: ?>-<?php endif ?></td>
    <td class="transaction" title="<?php echo __('Updated at %%d%% by %%u%%',array('%%d%%' => format_datetime($transac->updated_at), '%%u%%' => $transac->User)) ?>">
      <?php if ( trim($transac->description) ): ?><span class="ui-icon ui-icon-alert" title="<?php echo $transac->description ?>"></span><?php endif ?>
      #<?php echo cross_app_link_to($transac->id,'tck','ticket/sell?id='.$transac->id) ?>
    </td>
    <td class="ticket-ids"><?php include_partial('show_spectators_list_ids',array('tickets' => $contact['ticket-ids'][$wsid], 'show_workspaces' => $show_workspaces, 'num' => '#')) ?></td>
    <td class="ticket-nums"><?php include_partial('show_spectators_list_ids',array('tickets' => $contact['ticket-nums'][$wsid], 'show_workspaces' => $show_workspaces, 'num' => 'n°')) ?></td>
