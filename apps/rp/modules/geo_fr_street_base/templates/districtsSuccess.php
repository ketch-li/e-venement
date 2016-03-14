<?php $districts = $sf_data->getRaw('districts') ?>
<?php if ( !sfConfig::get('sf_web_debug', false) ): ?>
<?php echo json_encode($districts) ?>
<?php else: ?>
<pre>
  <?php print_r($districts) ?>
</pre>
<?php endif ?>
