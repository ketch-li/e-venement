<?php 
	$json = $sf_data->getRaw('lines'); 
	$json['csvHeaders'] = [__('Date'),__('Outcome'),__('Income'),__('Debt')];
	echo json_encode($json);
?>
