<div class="sf_admin_text sf_admin_form_field_codes ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
      <h3><?php echo __("Usable codes in the content of the email") ?></h3>
  </div>
  <ul>
    <?php foreach ( sfConfig::get('app_email_codes') as $id => $rpcode ): ?>
    <li><?php echo preg_replace('/(%.*%)(.*)/', '<strong>$1</strong>$2', $rpcode) ?></li>
    <?php endforeach ?>
  </ul>
</div>