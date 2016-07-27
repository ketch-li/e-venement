<?php use_helper('I18N', 'Date') ?>
<?php include_partial('event/assets') ?>
<?php $type = sfConfig::get('project_museums_type', 'museum') ?>

<div id="sf_admin_container" class="sf_admin_show ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <?php if ( $museum ): ?>
    <h1><?php echo __($type == 'museum' ? 'View Visit %%name%%' : 'View Opening %%name%%', array('%%name%%' => $event->name), 'messages') ?></h1>
    <?php else: ?>
    <h1><?php echo __('View Event %%name%%', array('%%name%%' => $event->name), 'messages') ?></h1>
    <?php endif ?>
  </div>

  <?php include_partial('global/flashes') ?>

  <div class="sf_admin_actions_block ui-widget">
      <?php include_partial('event/show_actions', array('form' => $form, 'event' => $event, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </div>

  <div class="ui-helper-clearfix"></div>

  <?php include_partial('show', array('form' => $form, 'event' => $event, 'configuration' => $configuration)) ?>

  <?php include_partial('show_footer', array('event' => $event, 'form' => $form, 'configuration' => $configuration)) ?>

  <?php include_partial('event/themeswitcher') ?>
</div>
