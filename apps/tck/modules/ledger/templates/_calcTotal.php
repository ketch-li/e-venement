<?php

  foreach($total as $key => $value) {
    if ( !is_array($value) )
      $superTotal[$key] += $value;
  }
  foreach($total['vat'] as $key => $value) {
      $superTotal['vat'][$key] += $value;
  }

?>