<div class="sf_admin_form_row">
  <label><?php echo __('Transaction') ?>:</label>
  <?php echo '#'.cross_app_link_to($member_card->transaction_id, 'tck', 'transaction/edit?id='.$member_card->transaction_id); ?>
</div>
