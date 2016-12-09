  <?php echo $form->renderHiddenFields() ?>
  <?php foreach ( $form->widgets as $fname => $fieldset ): if ( $fname != 'out' ): ?>
  <fieldset class="<?php echo $fname ?> check">
    <div class="ui-corner-all ui-widget-content">
      <?php if ( $fname ): ?>
      <div class="fg-toolbar ui-widget-header ui-corner-all">
        <h2><?php echo __(strtoupper(substr($fname,0,1)).substr($fname,1),array(),'messages') ?></h2>
      </div>
      <?php endif ?>
      <?php foreach ( $fieldset as $name => $value ): ?>
      <div class="line ui-corner-all sf_admin_form_row sf_admin_field_<?php echo $form[$name]->getName() ?> <?php echo $form[$name]->hasError() ? 'ui-state-error' : '' ?>">
        <?php echo $form[$name]->renderLabel() ?>
        <?php echo $form[$name] ?>
        <?php if ($form[$name]->hasError()): ?>
        <div class="errors">
          <span class="ui-icon ui-icon-alert floatleft"></span>
          <?php echo $form[$name]->renderError() ?>
        </div>
        <?php endif; ?>
        <?php if ( is_array($value) && isset($value['helper']) && $value['helper'] ): ?>
        <div class="help">
          <span class="ui-icon ui-icon-help floatleft"></span>
          <?php echo __($value['helper']) ?>
        </div>
        <?php endif ?>
        <div style="clear: both"></div>
      </div>
      <?php endforeach ?>
    </div>
  </fieldset>
  <?php endif; endforeach ?>
