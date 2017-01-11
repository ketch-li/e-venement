<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'autocomplete' => 'off',
  'target' => '_blank',
  'method' => 'get',
)) ?>
<?php echo $form->renderHiddenFields() ?>
<p class="field_code field">
  <?php echo $form['code']->renderLabel() ?>
  <?php echo $form['code'] ?>
</p>
<p class="submit">
  <button name="s" value="" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"><?php echo __('Add') ?></button>
</p>
</form>
