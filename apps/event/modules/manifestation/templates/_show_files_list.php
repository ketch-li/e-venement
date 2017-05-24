<div class="sf_admin_form_row sf_admin_form_field_files_list">
  <label><?php echo __('Event files') ?></label>
  <ul class="event_files">
    <?php foreach ( $manifestation->Event->Files as $file ): ?>
      <li><a target="_blank" href="<?php echo $file->getUrl(); ?>"><?php echo $file->name; ?></a></li>
    <?php endforeach ?>
  </ul>
</div>
<div class="sf_admin_form_row sf_admin_form_field_files_list">
  <label><?php echo __('Manifestation files') ?></label>
  <ul class="manifestation_files">
    <?php foreach ( $manifestation->Files as $file ): ?>
      <li><a target="_blank" href="<?php echo $file->getUrl(); ?>"><?php echo $file->name; ?></a></li>
    <?php endforeach ?>
  </ul>
</div>

