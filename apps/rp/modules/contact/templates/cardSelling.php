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
  <?php if ( sfConfig::get('project_cards_auto_close', true) ): ?>
  window.close();
  <?php endif ?>
});
</script>
