<?php 

  $soldout = __('Sold Out');

  foreach ($manifestation->Gauges as $gauge) {
    if ( $gauge->availableUnits > 0 ) {
      $soldout = false;
      break;
    }
  }

  echo $soldout;

?>