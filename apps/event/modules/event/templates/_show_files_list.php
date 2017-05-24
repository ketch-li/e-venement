<div class="sf_admin_form_field_files_list">
  <label><?php echo __('Event files') ?></label>
  <ul class="event_files">
    <?php foreach ( $event->Files as $file ): ?>
      <li><a target="_blank" href="<?php echo $file->getUrl(); ?>"><?php echo $file->name; ?></a></li>
    <?php endforeach ?>
  </ul>
</div>


