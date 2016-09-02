<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../lib/BasesfGuardAuthActions.class.php');

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 23319 2009-10-25 12:22:23Z Kris.Wallsmith $
 */
class sfGuardAuthActions extends BasesfGuardAuthActions
{
  public function executeSignin($request)
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N'));
    sfConfig::set('app_sf_guard_plugin_retrieve_by_username_callable', array(Doctrine::getTable('sfGuardUser'), 'findLoggedUser'));
    $this->ipv6 = array(
      'ready' => filter_var($request->getRemoteAddress(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || sfConfig::get('project_network_ipv6_ready',true),
      'on' => filter_var($request->getRemoteAddress(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
    );

    $user = $this->getUser();
    if ($user->isAuthenticated())
    {
      return $this->redirect('@homepage');
    }

    $class = sfConfig::get('app_sf_guard_plugin_signin_form', 'sfGuardFormSignin'); 
    $this->form = new $class();
    $userName = $request->getParameter('signin')['username'];

    switch($this->isBanned($request))
    {
      case 'ban':

        sfContext::getInstance()->getUser()->setFlash('error',sfConfig::get('project_anti_brute_force_ban_msg', __('Please contact your administrator')));
        $this->form->getErrorSchema()->addError(new sfValidatorError(new sfValidatorSchema(), __('The username and/or password is invalid.')), 'username');
        $this->form->setDefault('username', $userName);
        break;
      case 'last':

        $this->signIn($request);
        sfContext::getInstance()->getUser()->setFlash('notice',sfConfig::get('project_anti_brute_force_last_attempt_msg', __('Last attempt !')));
        break;
      default:

        $this->signIn($request);
    }
  }
  public function executeError404(sfWebRequest $request)
  {
  }

  protected function isBanned($request)
  {
    $userName = $request->getParameter('signin')['username'];
    $ip = $request->getHttpHeader('addr','remote');

    $loginThreshold = sfConfig::get('project_anti_brute_force_login_threshold', 5);
    $ipThreshold    = sfConfig::get('project_anti_brute_force_ip_threshold', 20);
    $timeThreshold  = sfConfig::get('project_anti_brute_force_time_threshold', 30);

    $lastAttempt = false;
    $banned = false;

    $sameLoginAttempts = Doctrine::getTable('Authentication')->createQuery('a')
      ->where('a.description = ?', $userName)
      ->andWhere('a.created_at > (NOW() - ?::INTERVAL)', $timeThreshold)
      ->andWhere('a.success = false')
      ->fetchArray()
    ;
    
    $sameIpAttempts = Doctrine::getTable('Authentication')->createQuery('a')
      ->where('a.ip_address = ?', $ip)
      ->andWhere('a.created_at > (NOW() - ?::INTERVAL)', $timeThreshold)
      ->andWhere('a.success = false')
      ->fetchArray()
    ;

    if( count($sameLoginAttempts) > $loginThreshold || count($sameIpAttempts) > $ipThreshold )
    {
      return 'ban';
    }else if( count($sameLoginAttempts) == ($loginThreshold - 1) || count($sameIpAttempts) == ($ipThreshold - 1) )
    {
      return 'last';
    }
    return 'ok';
  }

  protected function signIn($request)
  {
    $user = $this->getUser();

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('signin'));

      if ($this->form->isValid())
      {
        $values = $this->form->getValues(); 
        $this->getUser()->signin($values['user'], array_key_exists('remember', $values) ? $values['remember'] : false);

        // always redirect to a URL set in app.yml
        // or to the referer
        // or to the homepage
        $signinUrl = sfConfig::get('app_sf_guard_plugin_success_signin_url', $user->getReferer($request->getReferer()));

        return $this->redirect('' != $signinUrl ? $signinUrl : '@homepage');
        }
        $this->dispatcher->notify(new sfEvent($this, 'user.auth_failed'));
      }
      else
      {
        if ( $request->isXmlHttpRequest() )
        {
          $this->getResponse()->setHeaderOnly(true);
          $this->getResponse()->setStatusCode(401);

          return sfView::NONE;
        }
        // if we have been forwarded, then the referer is the current URL
        // if not, this is the referer of the current request
        $user->setReferer($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());

        $module = sfConfig::get('sf_login_module');
        if ( $this->getModuleName() != $module )
        {
          return $this->redirect($module.'/'.sfConfig::get('sf_login_action'));
        }

        $this->getResponse()->setStatusCode(401);
        }
  }
}
