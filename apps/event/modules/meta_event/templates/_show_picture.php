<?php if ( $form->getObject()->picture_id ): ?>
<?php use_stylesheet('picture','first') ?>
<div class="sf_admin_form_row sf_admin_form_field_show_picture">
  <label for=""><?php echo __('Tickets background') ?></label>
  <div class="widget">
    <?php echo $form->getObject()->Picture->render() ?>
  </div>
</div>
<?php endif ?>
