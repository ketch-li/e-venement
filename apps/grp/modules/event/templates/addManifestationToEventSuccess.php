<?php include_partial('assets') ?>
<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>

<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <?php include_partial('flashes') ?>
  
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Add a manifestation to an event') ?></h1>
  </div>

  <div class="sf_admin_flashes ui-widget"></div>

  <div id="sf_admin_header"></div>

  <div id="sf_admin_content">

<div class="sf_admin_form">
  <form method="get" autocomplete="off" action="">
    <input name="sf_method" value="put" type="hidden">

    <div class="ui-helper-clearfix"></div>
  
    <div aria-hidden="false" aria-expanded="true" role="tabpanel" aria-labelledby="ui-id-1" id="sf_fieldset_none" class="ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom">
    
    <?php foreach ( array('manifestation_id' => __('Manifestation')) as $key => $desc ): ?>
    <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_<?php echo $key ?>">
      <label for="<?php echo $key ?>"><?php echo $desc ?></label>
      <div class="label ui-helper-clearfix"></div>
      <div class="widget ">
        <?php echo $form[$key] ?>
      </div>
    </div>
    <?php endforeach ?>
    
    </div>
    
    <div class="sf_admin_actions_block ui-widget ui-helper-clearfix">
      <ul class="sf_admin_actions_form">
        <li class="sf_admin_action_list"><a class="fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for('event/index') ?>"><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span><?php echo __('List', null, 'sf_admin') ?></a></li>
        <li class="sf_admin_action_save"><button type="submit" class="fg-button ui-state-default fg-button-icon-left"><span class="ui-icon ui-icon-circle-check"></span><?php echo __('Save', null, 'sf_admin') ?></button></li>
      </ul>
    </div>
  </form>
</div>

</div>
</div>
