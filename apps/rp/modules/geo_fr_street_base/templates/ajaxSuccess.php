<?php $addresses = $sf_data->getRaw('addresses') ?>
<?php if ( !sfConfig::get('sf_web_debug', false) ): ?>
<?php echo json_encode($addresses) ?>
<?php else: ?>
<pre>
  <?php print_r($addresses) ?>
</pre>
<?php endif ?>
