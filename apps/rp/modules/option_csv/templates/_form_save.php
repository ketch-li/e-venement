<fieldset>

    <div class="line ui-corner-all sf_admin_form_row sf_admin_field_<?php echo $form['select-all']->getName() ?> <?php echo $form['select-all']->hasError() ? 'ui-state-error' : '' ?>">
      <?php echo $form['select-all']->renderLabel() ?>
      <?php echo $form['select-all'] ?>
      <?php if ($form['select-all']->hasError()): ?>
      <div class="errors">
        <span class="ui-icon ui-icon-alert floatleft"></span>
        <?php echo $form['select-all']->renderError() ?>
      </div>
      <?php endif; ?>
    </div>

</fieldset>

  <fieldset class="sf_admin_action_save">
    <button type="submit" class="fg-button ui-state-default fg-button-icon-left"><span class="ui-icon ui-icon-circle-check"></span>Mettre à jour</button>
  </fieldset>
  <div style="clear: both"></div>
