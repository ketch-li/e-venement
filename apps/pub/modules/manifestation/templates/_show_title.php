<?php use_helper('Date') ?>

<?php $sf_response->addMeta('og:title', $manifestation->Event); ?>
<h1><?php echo __('Choose tickets') ?></h1>

<?php include_partial('global/promo_code') ?>

<?php if ( sfConfig::get('app_options_home', 'event') == 'meta_event' ): ?>
  <div id="meta_event">&laquo;&nbsp;<?php echo link_to($manifestation->Event->MetaEvent, 'event/index?meta-event='.$manifestation->Event->MetaEvent->slug) ?></div>
<?php endif ?>

<div id="event" itemprop="name"><?php echo $manifestation->Event ?></div>

<?php if ( $manifestation->depends_on ): ?>
  <div id="depends_on">+ <?php echo $manifestation->DependsOn->Event ?></div>
<?php endif ?>

<div id="manifestation">
  <?php echo __('on') ?> <?php echo $manifestation->getFormattedDate() ?>
  <span itemprop="doorTime" style="display: none"><?php echo date('c', strtotime($manifestation->happens_at)) ?></span>
</div>
<div id="location"><?php echo __('location') ?> : <span itemprop="location"><?php echo $manifestation->Location ?></span></div>
