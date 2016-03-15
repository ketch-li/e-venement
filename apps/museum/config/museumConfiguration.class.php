<?php

require_once dirname(__FILE__).'../../../../config/autoload.inc.php';

class museumConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');

    $this->dispatcher->connect('user.change_authentication', array($this, 'logAuthentication'));
  }

  public function logAuthentication(sfEvent $event)
  {
    $params   = $event->getParameters();
    $user     = sfContext::getInstance()->getUser();
    $request  = sfContext::getInstance()->getRequest();
    if ( !is_object($user) )
      return false;

    if (( sfConfig::get('project_login_alert_beginning_at', false) && sfConfig::get('project_login_alert_beginning_at') < time() || !sfConfig::get('project_login_alert_beginning_at', false) )
      &&( sfConfig::get('project_login_alert_ending_at', false) && sfConfig::get('project_login_alert_ending_at') > time() || !sfConfig::get('project_login_alert_ending_at', false) )
      && sfConfig::get('project_login_alert_message', false) )
      $user->setFlash('error', sfConfig::get('project_login_alert_message'));

    $auth = new Authentication();
    $auth->sf_guard_user_id = $user->getId();
    $auth->description      = $user;
    $auth->ip_address       = $request->getHttpHeader('addr','remote');
    $auth->user_agent       = $request->getHttpHeader('User-Agent');
    $auth->referer          = $request->getReferer();
    $auth->success          = $params['authenticated'];

    $auth->save();
  }

  public function initGarbageCollectors(sfCommandApplicationTask $task = NULL) {
    $this->task = $task;
    $this->addGarbageCollector('gauge-timeout', function($id = NULL) {
      $section = 'Gauge timeout';

      // Check configuration
      if ( !sfConfig::get('project_museums_enable', false) || !sfConfig::get('app_manifestation_exit_on_timeout', false) ) {
        $this->stdout($section, 'Skipped (not enabled)', 'INFO');
        return;
      }

      // Get timeout value (in seconds)
      $option = Doctrine_Query::create()->from('OptionGaugeTimeout ogt')
        ->andWhere('ogt.type = ?', 'gauge_timeout')
        ->andWhere('ogt.name = ?', 'timeout')
        ->fetchOne();
      $timeout = $option ? (int)$option->getValue() * 60 : 0;
      if ( !$timeout ) {
        $this->stdout($section, 'Skipped (timeout == 0 or tiemout is not set)', 'INFO');
        return;
      }

      $context = sfContext::getInstance();
      $action = $context->getController()->getAction('gauge_timeout', 'autoExit');
      $request = new sfWebRequest($context->getEventDispatcher());
      $request->setParameter('since', time() - $timeout);
      $action->executeAutoExit($request);

      $this->stdout($section, 'Done', 'INFO');
    });
  }
}
