<?php use_helper('Date') ?>
<?php //use_stylesheet('base-ticket.css', '', array('media' => 'all')) ?>

  <?php
    foreach ( $MemberCards as $mc )
    {
      $mct = $mc->MemberCardType;
      
      if ( $mct->custom_id )
      {
        echo '<style type="text/css">'.$mct->Custom->css.'</style>';
      }

      echo get_partial('card',array(
        'MemberCards' => $MemberCards,
        'duplicate' => $duplicate))
      ;    
    }
  ?>