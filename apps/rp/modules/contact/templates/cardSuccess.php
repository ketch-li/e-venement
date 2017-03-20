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
  <?php
    $nb = 0;
    $transactions = array($transaction->id => $transaction->id);
    foreach ( $transaction->MemberCards as $mc )
    {
      $nb += $mc->BoughtProducts->count() + $mc->Tickets->count();
      foreach ( $mc->Tickets as $ticket )
        $transactions[$ticket->transaction_id] = $ticket->transaction_id;
    }
  ?>
  
  window.open('<?php echo cross_app_url_for('tck', ($nb > 0 ? 'transaction/edit' : 'ticket/pay').'?id='.array_shift($transactions)) ?>');
  
  <?php while ( $tid = array_shift($transactions) ): ?>
  window.open('<?php echo cross_app_url_for('tck', 'transaction/edit?id='.$tid) ?>');
  <?php endwhile ?>
  
  <?php if ( sfConfig::get('project_cards_auto_close', true) ): ?>
  window.close();
  <?php endif ?>
});
</script>
<?php include_partial('card', array('transaction' => $transaction, 'contact' => $contact)) ?>