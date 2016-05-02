<?php use_stylesheet('picture','first') ?>
<div class="sf_admin_form_row sf_admin_form_field_show_picture">
  <label for="payment_method_show_picture"><?php echo __('Pictogram') ?></label>
  <div class="widget">
  <?php if ( $form->getObject()->picture_id ): ?>
    <?php echo $form->getObject()->Picture->render() ?>
  <?php endif ?>
  </div>
</div>
