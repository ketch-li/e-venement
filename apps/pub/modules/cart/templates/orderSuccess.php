<?php include_partial('global/ariane',array('active' => 4)) ?>
<?php include_partial('global/oplog') ?>
<script type="text/javascript"><!--
  $(document).ready(function(){
    if ( $('a.autofollow, form.autosubmit').length == 1 )
    {
      if ( $('a.autofollow').length > 0 )
        window.location = $('a.autofollow').prop('href');
      else
        $('form.autosubmit').submit();
    }
  });
--></script>
<h1><?php echo __('Payment of your order #%%tid%%', array('%%tid%%' => $tid)) ?></h1>
<?php $currency = sfConfig::get('project_internals_currency', array()) ?>
<?php if ( isset($currency['conversions']) && is_array($currency['conversions']) && $currency['conversions']
        && !$sf_request->getPostParameter('currency', false) ): ?>
<h3><?php echo __('Choose your currency') ?> :</h3>
  <?php foreach ( $arr = array($currency['iso'] => $currency) + $currency['conversions'] as $iso => $cur ): ?>
  <form action="#<?php echo $cur['symbol'] ?>" method="post" class="currency-<?php echo $iso ?> currency-choice">
    <?php foreach ( $sf_data->getRaw('sf_request')->getPostParameters() as $name => $value ): ?>
      <?php if ( is_array($value) ): ?>
        <?php foreach ( $value as $n => $v ): ?>
        <input type="hidden" name="<?php echo $name ?>[<?php echo $n ?>]" value="<?php echo $v ?>" />
        <?php endforeach ?>
      <?php else: ?>
        <input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>" />
      <?php endif ?>
    <?php endforeach ?>
    <input type="hidden" name="currency" value="<?php echo $iso ?>" />
    <input type="submit" name="symbol" value="<?php echo $cur['symbol'] ?>" />
  </form>
  <?php endforeach ?>
  <p class="currency-choice-explanation">... <?php echo __('before proceeding to payment') ?> ...</p>
<?php else: ?>
  <h3><?php echo __('Choose your payment method') ?> :</h3>
  <?php foreach ( $sf_data->getRaw('online_payments') as $online_payment ): ?>
    <?php echo $online_payment ?>
  <?php endforeach ?>
<?php endif ?>
