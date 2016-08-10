<?php 

  $json = $sf_data->getRaw('lines');
  $json['csvHeaders'] = [
      __('Description'),
      __('Value')
  ];
  
  echo json_encode($json)
?>
