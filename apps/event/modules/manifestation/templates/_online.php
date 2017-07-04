<?php $online = FALSE ;
foreach ($manifestation->Gauges as $gauge) 
  if ($gauge->online === TRUE) { 
    $online = TRUE;
    echo $gauge->online
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png'); 
    break;
  } 
    
?>
