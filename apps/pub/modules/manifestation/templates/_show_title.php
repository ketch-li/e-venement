<?php use_helper('Date') ?>
<h1><?php echo __('Choose tickets') ?></h1>

<?php if ( sfConfig::get('app_options_home', 'event') == 'meta_event' ): ?>
  <div id="meta_event">&laquo;&nbsp;<?php echo link_to($manifestation->Event->MetaEvent, 'event/index?meta-event='.$manifestation->Event->MetaEvent->slug) ?></div>
<?php endif ?>

<div id="event"><?php echo $manifestation->Event ?></div>
<div id="subtitle"><?php echo $manifestation->Event->subtitle ?></div>

<?php if ( $manifestation->depends_on ): ?>
  <div id="depends_on">+ <?php echo $manifestation->DependsOn->Event ?></div>
<?php endif ?>

<div id="manifestation"><?php echo __('on') ?> <?php echo $manifestation->getFormattedDate() ?></div>
<div id="location"><?php echo __('location') ?> : <?php echo $manifestation->Location ?></div>
