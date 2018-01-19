<?php 

  $soldout = false;

  if ( $manifestation->tickets > $manifestation->online_limit ) {
    $soldout = __('Sold Out');
  }

  echo $soldout;

?>