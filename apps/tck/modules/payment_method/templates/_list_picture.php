<?php if ( $payment_method->picture_id ): ?>
  <?php echo $payment_method->getRawValue()->Picture->render() ?>
<?php endif ?>
