<?php use_helper('I18N') ?>
    
    <p class="submit">
      <button class="fg-button ui-state-default fg-button-icon-left" type="submit" id="duplicate">
        <span class="ui-icon ui-icon-circle-check"></span>
        <?php echo __('Duplicate') ?>
      </button>
      <button class="fg-button ui-state-default fg-button-icon-left" id="again" name="again">
        <span class="ui-icon ui-icon-circle-plus"></span>
        <?php echo __('Duplicate and repeat') ?>
      </button>
      <button class="fg-button ui-state-default fg-button-icon-left" id="calendar" name="calendar">
        <span class="ui-icon ui-icon-calendar"></span>
        <?php echo __('Duplicate and show in calendar') ?>
      </button>
      <?php foreach ( $manifestations as $manif ): ?>
        <input type="hidden" name="periodicity[manifestation_id][]" value="<?php echo $manif->id ?>" />
      <?php endforeach ?>
      <?php if ( $manifestations->count() == 1 ): ?>
        <input type="hidden" name="id" value="<?php echo $manifestations[0]->id ?>" />
      <?php endif ?>
      <input type="hidden" name="periodicity[_csrf_token]" value="<?php echo $form->getCSRFToken() ?>" />
      <input type="hidden" name="sf_method" value="put" />
      <?php echo $form->renderHiddenFields() ?>
    </p>