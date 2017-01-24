<div class="sf_admin_form_row">
  <label><?php echo __('Excluded users') ?>:</label>
  <ul>
  <?php foreach ( $payment_method->Users as $user ): ?>
    <li><?php echo $user ?></li>
  <?php endforeach ?>
  </ul>
</div>
