<div class="ui-corner-all ui-widget-content">

<?php echo $form['store']->integrate->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'method' => 'get',
  'target' => '_blank',
  'autocomplete' => 'off',
  'class' => 'store-print',
)) ?>
  <?php echo $form['store']->integrate ?>
  <input class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" type="submit" value="<?php echo __('Products delivered') ?>" name="integrate"></input>
</form>

<?php echo $form['store']->member_card->renderFormTag(cross_app_url_for('rp', 'contact/card?duplicate=true&qty=1&selling=true'), array(
  'method' => 'post',
  'target' => '_blank',
  'autocomplete' => 'off',
  'class' => 'store-mc-print',
  'data-token' => $form['store']->member_card->_csrf_token,
)) ?>
  <?php 
    echo $form['store']->member_card;
  ?>
</form>
</div>

<?php include_partial('global/assets_jqplot') ?>
<?php use_javascript('pos-stocks.lib.js') ?>
