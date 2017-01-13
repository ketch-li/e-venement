<?php include_partial('global/assets') ?>

<div class="sf_admin_form ui-widget-content ui-corner-all sf_admin_edit full-lines" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Selling screen configuration') ?></h1>
  </div>
  <?php include_partial('global/flashes') ?>
  <form action="<?php echo url_for('sellingOptions/update') ?>" method="post" class="data">
      <fieldset class="check">
        <div class="ui-corner-all ui-widget-content">
          <?php echo $form ?> 
        </div> 
      </fieldset>
      <fieldset class="sf_admin_action_save">
        <button type="submit" class="fg-button ui-state-default fg-button-icon-left"><span class="ui-icon ui-icon-circle-check"></span><?php echo __('Update') ?></button>
      </fieldset>
      <div style="clear: both"></div>
  </form>
</div>
