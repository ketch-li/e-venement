<p id="ids" class="order">
  <span class="name inline-modifiable"><?php echo sfConfig::get('app_transaction_order_name', __('Order', null, 'li_accounting')) ?> </span>
  <?php echo __('#<span class="order_id">%%oid%%</span>, for transaction #<span class="transaction_id">%%tid%%</span>', array('%%oid%%' => $order->id, '%%tid%%' => $transaction->id), 'li_accounting') ?>
</p>
