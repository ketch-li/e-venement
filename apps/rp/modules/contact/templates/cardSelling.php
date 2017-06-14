<?php use_helper('Date') ?>
<?php $sf_user->setFlash('notice',$sf_user->getFlash('notice')) ?>
<?php include_partial('contact/assets') ?>
<script type="text/javascript">
$(document).ready(function(){
  <?php if ( sfConfig::get('project_cards_pdf', false) ): ?>
  window.open('<?php echo $pdf_url ?>');
  <?php else: ?>
  window.print();
  <?php endif ?>

});
</script>
<?php if ( !sfConfig::get('project_cards_pdf', false) ): ?>
<?php include_partial('card', array('transaction' => $transaction, 'contact' => $contact)) ?>
<?php endif ?>