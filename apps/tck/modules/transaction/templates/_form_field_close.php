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
  <li class="delayed-payment-contact-needed"><?php echo __('A contact is needed for printing the ticket(s) if it is a delayed payment.') ?></li>
  <li class="partial-print-error"><?php echo __('You must have at least one manifestation selected.') ?></li>
  <li class="force-contact"><?php echo __('A contact is needed before closing the transaction.') ?></li>

  <li class="CP-print-error"><?php echo __('You must enter a postal code before printing the ticket(s).') ?></li>

  <li class="give-price-to-wip"><?php echo __('You always need to give a price to every seated-only tickets before printing or booking.') ?></li>
  <li class="direct-printing-info"><?php echo __('e-venement will print the tickets directly on your printer') ?></li>
  <script type="text/javascript"><!--
    LI.usb = <?php echo json_encode(array_merge(sfConfig::get('software_internals_usb', array()), sfConfig::get('project_internals_usb', array()))) ?>;
    LI.serial = <?php echo json_encode(array_merge(sfConfig::get('software_internals_serial', array()), sfConfig::get('project_internals_serial', array()))) ?>;
    LI.ept_wait_transaction_end = <?php echo sfConfig::get('app_transaction_ept_wait_transaction_end', false) ? 'true' : 'false' ?>;
  --></script>
  <li class="usb-printers" data-json="<?php echo json_encode(sfConfig::get('project_internals_usb', sfConfig::get('software_internals_usb'))) ?>"></li>
</ul>
<ul class="payments">
  <li class="translinked"><?php echo __('This payment is linked to the cancelling transaction #%%id%%') ?></li>
  <li class="currency"><?php echo format_currency(0, $sf_context->getConfiguration()->getCurrency()) ?></li>
</ul>
<ul class="displays">
  <li class="display-total"><?php echo __('Total:') ?></li>
  <li class="display-left"><?php echo __('Left to pay:') ?></li>
  <li class="display-default"><?php echo __('Hello !') ?></li>
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
  href="#"
  title="<?php echo __('Switch to simplified GUI / back from ...') ?>"
  id="simplified-gui"
><span class="ui-icon ui-icon-transferthick-e-w"></span></a>
<a
  class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"
  href="<?php echo url_for('ticket/reset?id='.$transaction->id) ?>"
  title="<?php echo __('Abandon') ?>"
  id="abandon"
  onclick="javascript: if ( !confirm('<?php echo __('Are you sure?') ?>') ) { setTimeout(function(){ $('#transition .close').click(); },200); return false; }"
><span class="ui-icon ui-icon-trash"></span></a>
<a
  class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"
  href="<?php echo cross_app_url_for('pub','transaction/sendEmail?id='.$transaction->id.'&token='.md5($transaction->id.'|*|*|'.sfConfig::get('project_eticketting_salt', 'e-venement'))) ?>"
  title="<?php echo __('Resend confirmation email') ?>"
  id="resend-email"
  target="_blank"
  data-text-error="<?php echo __('Unable to send an email: no email address available.') ?>"
><span class="ui-icon ui-icon-mail-closed"></span></a>
<a
  class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"
  href="<?php echo cross_app_url_for('pub','cart/order?transaction_id='.$transaction->id.'&token='.md5($transaction->id.'|*|*|'.sfConfig::get('project_eticketting_salt', 'e-venement'))) ?>"
  title="<?php echo __('Pay by card online (only efficient if this transaction has a contact)') ?>"
  data-text-prefix="<?php echo __('This is the URL to use: ') ?>"
  id="pay-online"
  target="_blank"
  onclick="javascript: var anchor = $('<a></a>').text($(this).prop('href')).prop('href', $(this).prop('href')); LI.alert($(this).attr('data-text-prefix')+anchor.html(),'success', 8000); return false;"
><span class="ui-icon ui-icon-suitcase"></span></a>
<a
  class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"
  href="<?php echo url_for('transaction/directSurveys?id='.$transaction->id) ?>"
  title="<?php echo __('Edit transaction surveys...') ?>"
  id="direct-surveys"
  target="_blank"
><span class="ui-icon ui-icon-gear"></span></a>
</form>
<script>
  LI.directPrintLogUrl = "<?php echo url_for('transaction/directPrintLog?id='.$transaction->id, true) ?>";
  LI.transactionId = <?php echo $transaction->id ?>;
</script>
