<p id="ids" class="invoice">
  <?php echo __('<span class="name inline-modifiable">Invoice</span> #%%prefix%%<span class="invoice_id">%%iid%%-%%version%%</span><span class="transaction">, for transaction #<span class="transaction_id inline-modifiable">%%tid%%</span> from the <span class="date inline-modifiable">%%d%%</span> <span class="time inline-modifiable">%%t%%</span></span>', array(
    '%%d%%' => format_date(strtotime($transaction->created_at)),
    '%%t%%' => format_date(strtotime($transaction->created_at), 't'),
    '%%iid%%' => $invoice->id,
    '%%tid%%' => $transaction->id,
    '%%prefix%%' => sfConfig::get('app_seller_invoice_prefix'),
    '%%version%%' => $invoice->version,
  ), 'li_accounting') ?>
</p>
