<?php
  $ev = array();
  if ( isset($manifestation) )
    $manifestations = array($sf_data->getRaw('manifestation'));
  else
    $manifestations = $sf_data->getRaw('manifestations');

  foreach ( $manifestations as $manifestation )
  if ( !isset($ev[$manifestation->event_id]) )
    $ev[$manifestation->event_id] = $manifestation->Event;
?>
<?php if ( count($ev) == 1 ): ?>
<div class="event-fields">
<?php $evt = array_pop($ev) ?>
<h2><?php echo $evt ?></h2>
<?php foreach ( array('description', 'extradesc', 'extraspec') as $field ): ?>
<div class="<?php echo $field ?>"><?php echo $evt->$field ?></div>
<?php endforeach ?>
</div>
<?php endif ?>

  <div class="social-description" itemprop="description" style="display:none">
    <?php if ( isset($manifestation) ): ?>
      <?php echo $manifestation->getSocialDescription() ?>
    <?php else: ?>
      <?php echo $evt->description ?>
    <?php endif; ?>
  </div>


