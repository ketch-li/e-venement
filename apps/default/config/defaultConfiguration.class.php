<?php

require_once dirname(__FILE__).'../../../../config/autoload.inc.php';

class defaultConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
    
    $this->dispatcher->connect('user.change_authentication', array($this, 'logAuthentication'));
    $this->dispatcher->connect('user.auth_failed', array($this, 'logAttempt'));
  }
  
  public function logAuthentication(sfEvent $event)
  {
    $params   = $event->getParameters();
    $user     = sfContext::getInstance()->getUser();
    $request  = sfContext::getInstance()->getRequest();

    if (( sfConfig::get('project_login_alert_beginning_at', false) && sfConfig::get('project_login_alert_beginning_at') < time() || !sfConfig::get('project_login_alert_beginning_at', false) )
      &&( sfConfig::get('project_login_alert_ending_at', false) && sfConfig::get('project_login_alert_ending_at') > time() || !sfConfig::get('project_login_alert_ending_at', false) )
      && sfConfig::get('project_login_alert_message', false)
      && $user )
      $user->setFlash('error', sfConfig::get('project_login_alert_message'));

    $auth = new Authentication();

    $auth->sf_guard_user_id = $user->getId();
    $auth->description      = is_object($user) ? (string)$user : $request->getParameter('signin')['username'];
    $auth->ip_address       = $request->getHttpHeader('addr','remote');
    $auth->user_agent       = $request->getHttpHeader('User-Agent');
    $auth->referer          = $request->getReferer();
    $auth->success          = $params['authenticated'];

    $auth->save();
  }

  public function logAttempt(sfEvent $event)
  {
    $request  = sfContext::getInstance()->getRequest();
    $auth = new Authentication();

    $auth->description = $request->getParameter('signin')['username'];
    $auth->ip_address  = $request->getHttpHeader('addr','remote');
    $auth->user_agent  = $request->getHttpHeader('User-Agent');
    $auth->referer     = $request->getReferer();
    $auth->success     = false;

    $auth->save();
  }
  
  public function initialize()
  {
    ProjectConfiguration::initialize();
  }
}
