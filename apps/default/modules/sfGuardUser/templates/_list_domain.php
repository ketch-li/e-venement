<?php echo preg_replace('/\.?'.sfConfig::get('project_internals_users_domain', '').'$/', '', (string)$sf_guard_user->Domain[0]) ?>
