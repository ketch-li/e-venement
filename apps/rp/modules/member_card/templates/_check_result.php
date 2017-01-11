<div class="form">

<?php include_partial('check_result_card',array('member_card' => $member_card)) ?>
<?php include_partial('check_result_cards',array('member_cards' => $member_cards, 'member_card' => $member_card)) ?>
<?php $object = $member_card->Contact ?>
<a target="_blank"
    href="<?php echo cross_app_url_for('tck', 'transaction/new?'.strtolower(get_class($object->getRawValue())).'_id='.$object->id) ?>"
    class="new-transaction fg-button-mini fg-button ui-state-default fg-button-icon-left"
    ><span class="ui-icon ui-icon-cart"></span><?php echo __('Sell') ?>
</a>
<div style="clear: both"></div>
</div>
