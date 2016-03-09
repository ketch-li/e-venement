<?php use_helper('Number') ?>
<?php echo format_currency($transaction->outcomes - $transaction->incomes,$sf_context->getConfiguration()->getCurrency()) ?>
