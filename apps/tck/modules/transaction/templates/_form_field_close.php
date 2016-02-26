<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'method' => 'get',
  'target' => '_blank',
)) ?>
<?php echo $form ?>
<span class="show-seated-plan"><?php echo __('Display the seated plan') ?></span>
<span class="confirmation"><?php echo __('Did you really want to exit this transaction?') ?></span>
<ul class="overbooking">
  <li class="msg block"><?php echo __('The blinking quantities mean that those gauges are or will be full.') ?><br/><?php echo PHP_EOL ?><?php echo __('You cannot proceed your action though.') ?></li>
  <li class="msg warn"><?php echo __('The blinking quantities mean that those gauges are or will be full.') ?><br/><?php echo PHP_EOL ?><?php echo __('Please confirm that you allow this overbooking.') ?></li>
  <li class="type" data-type="<?php echo sfConfig::get('app_transaction_gauge_block') && !$sf_user->hasCredential('tck-admin') ? 'block' : 'warn' ?>"></li>
</ul>
<ul class="print">
  <li class="pay-before"><?php echo __('You must record the payment(s) before printing the ticket(s)') ?></li>
  <li class="partial-print-error"><?php echo __('You must have at least one manifestation selected.') ?></li>
  <li class="give-price-to-wip"><?php echo __('You always need to give a price to every seated-only tickets before printing or booking.') ?></li>
  <li class="direct-printing-info"><?php echo __('e-venement will print the tickets directly on your printer') ?></li>
  <script type="text/javascript"><!--
    LI.usb = <?php echo json_encode(array_merge(sfConfig::get('software_internals_usb', array()), sfConfig::get('project_internals_usb', array()))) ?>;
  --></script>
  <li class="usb-printers" data-json="<?php echo json_encode(sfConfig::get('project_internals_usb', sfConfig::get('software_internals_usb'))) ?>"></li>
</ul>
<ul class="payments">
  <li class="translinked"><?php echo __('This payment is linked to the cancelling transaction #%%id%%') ?></li>
</ul>
<ul class="prices">
  <li class="free-price"><?php echo __('Pay what you want') ?></li>
  <li class="free-price-default"><?php echo sfConfig::get('project_tickets_free_price_default', 1) ?></li>
</ul>
<ul class="messages">
  <li class="ok"><?php echo __('yes',null,'sf_admin') ?></li>
  <li class="cancel"><?php echo __('no',null,'sf_admin') ?></li>
</ul>
<a
  class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"
  href="<?php echo url_for('ticket/reset?id='.$transaction->id) ?>"
  title="<?php echo __('Abandon') ?>"
  id="abandon"
  onclick="javascript: if ( !confirm('<?php echo __('Are you sure?') ?>') ) { setTimeout(function(){ $('#transition .close').click(); },200); return false; }"
><span class="ui-icon ui-icon-circle-close"></span></a>
<a
  class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"
  href="<?php echo cross_app_url_for('pub','transaction/sendEmail?id='.$transaction->id.'&token='.md5($transaction->id.'|*|*|'.sfConfig::get('project_eticketting_salt', 'e-venement'))) ?>"
  title="<?php echo __('Resend confirmation email') ?>"
  id="resend-email"
  target="_blank"
><span class="ui-icon ui-icon-mail-closed"></span></a>
<a
  class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"
  href="#"
  title="<?php echo __('Switch to simplified GUI / back from ...') ?>"
  id="simplified-gui"
><span class="ui-icon ui-icon-transferthick-e-w"></span></a>
<a
  class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"
  href="<?php echo url_for('transaction/directSurveys?id='.$transaction->id) ?>"
  title="<?php echo __('Edit transaction surveys...') ?>"
  id="direct-surveys"
  target="_blank"
><span class="ui-icon ui-icon-gear"></span></a>
</form>
