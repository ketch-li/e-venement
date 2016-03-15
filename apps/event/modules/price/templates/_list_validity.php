<?php echo format_number_choice('[0]Only once|[1]%%days%% day|(1,+Inf]%%days%% days', array('%%days%%' => $price->x_days_valid), $price->x_days_valid) ?>
