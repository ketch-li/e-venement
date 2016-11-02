<?php if ( !$web_origin->WebOrigin->isNew() ): ?>
  #<?php echo cross_app_link_to($web_origin->WebOrigin->transaction_id, 'tck', 'transaction/edit?id='.$wo->transaction_id) ?>
<?php endif ?>
