<?php use_helper('Number') ?>
<?php if ( sfContext::getInstance()->getActionName() == 'debts' ): ?>
<span title="<?php echo __('Price to pay') ?>" class="price"><?php echo format_currency($price = $transaction->getPrice(),$sf_context->getConfiguration()->getCurrency()) ?></span>
-
<span title="<?php echo __('Amount already paid') ?>" class="paid"><?php echo format_currency($paid = $transaction->getPaid(),$sf_context->getConfiguration()->getCurrency()) ?></span>
=
<span title="<?php echo __('Total') ?>" class="debt"><?php echo format_currency($price - $paid,$sf_context->getConfiguration()->getCurrency()) ?></span>
<?php else: ?>
  N/A
<?php endif ?>
