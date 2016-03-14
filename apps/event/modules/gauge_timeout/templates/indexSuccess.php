<?php include_partial('global/assets') ?>
<div class="sf_admin_form ui-widget-content ui-corner-all sf_admin_edit full-lines" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Automatic gauge timeout') ?></h1>
  </div>
  <?php include_partial('form_header',array('form' => $form,)); ?>
  <?php include_partial('global/flashes') ?>
  <form action="<?php echo url_for('gauge_timeout/update') ?>" method="post" class="data">
    <?php include_partial('global/option_form',array('form' => $form,)); ?>
    <?php include_partial('form_save',array('form' => $form,)); ?>
  </form>
</div>
