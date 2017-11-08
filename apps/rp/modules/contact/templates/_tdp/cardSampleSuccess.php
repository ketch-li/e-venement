<?php use_helper('Date') ?>
<?php use_stylesheet('contact-card.css', '', array('media' => 'all')) ?>
<?php use_stylesheet('sample-card.css', '', array('media' => 'all')) ?>

  <?php
    foreach ( $transaction->MemberCards as $mc )
    {
      echo get_partial('card',array(
        'transaction' => $transaction,
        'duplicate' => $duplicate))
      ;    
    }
  ?>