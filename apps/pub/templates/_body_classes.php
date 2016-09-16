<?php foreach ( is_array(sfConfig::get('pub.meta_event.slug','null')) ? sfConfig::get('pub.meta_event.slug') : array(sfConfig::get('pub.meta_event.slug','null')) as $me ): ?>
  <?php echo 'meta-event-'.$me ?>
<?php endforeach ?>
<?php echo
  ' '.
  'app-'.$sf_context->getConfiguration()->getApplication().' '.
  'mod-'.$sf_context->getModuleName().' '.
  'action-'.$sf_context->getActionName().' '.
  'culture-'.$sf_user->getCulture().' '.
  (sfConfig::get('sf_web_debug', false) ? 'env-debug' : '').
  'layout-'.(sfConfig::get('app_options_layout', 'default')).
  ''
?>
