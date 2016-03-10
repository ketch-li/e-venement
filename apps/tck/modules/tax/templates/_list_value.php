<?php use_helper('Number') ?>
<?php echo $tax->type == 'percentage' ? $tax->value.'%' : format_currency($tax->value,$sf_context->getConfiguration()->getCurrency()) ?>
