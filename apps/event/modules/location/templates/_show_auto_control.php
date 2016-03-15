<div class="sf_admin_form_row sf_admin_form_field_auto_control">
  <label><?php echo __('Automatic control') ?>:</label>
  <?php
    echo $location->auto_control
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png')
  ?>
</div>
