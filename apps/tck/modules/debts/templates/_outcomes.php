<?php use_helper('Number') ?>
<?php echo format_currency(-$transaction->outcomes,$sf_context->getConfiguration()->getCurrency()) ?>
