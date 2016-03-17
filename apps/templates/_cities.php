<?php use_javascript('geo-zipcities-helper') ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_cities">
  <select name="cities" size="3" data-url="<?php echo cross_app_url_for('rp','postalcode/ajax') ?>">
    <option></option>
  </select>
</div>
