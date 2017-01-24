<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_domain">
  <label for="location_domain"><?php echo __('Applied domain') ?></label>
  <div class="label ui-helper-clearfix"><?php echo __('A resource or a location is available to any instance set up on the same domain, but also on all sub-domains and parent domains.') ?></div>
  <div class="widget">
    <input type="text" disabled="disabled" value="<?php echo $form->getObject()->domain ?>" name="domain" />
  </div>
</div>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_current_domain">
  <label for="location_domain"><?php echo __('Current domain') ?></label>
  <div class="label ui-helper-clearfix"></div>
  <div class="widget">
    <input type="text" disabled="disabled" value="<?php echo sfConfig::get('project_internals_users_domain', '.') ?>" name="domain" />
  </div>
</div>
