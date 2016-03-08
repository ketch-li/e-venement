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
<h1><?php echo __('Payment of your order') ?></h1>
<h3><?php echo __('Choose your payment method') ?> :</h3></br>
<?php foreach ( $sf_data->getRaw('online_payments') as $online_payment ): ?>
  <?php echo $online_payment ?>
<?php endforeach ?>
