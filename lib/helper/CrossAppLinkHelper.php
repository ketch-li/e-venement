<?php
/**
 * @author Olivier Mansour
 * @author Baptiste Simon
 * @author Libre Informatique [http://www.libre-informatique.fr/]
 */

/**
 * return an url for a given symfony application and an internal url
 *
 * @author Olivier Mansour
 * @author Baptiste Simon
 * @author Libre Informatique [http://www.libre-informatique.fr/]
 *
 * @param string $appname
 * @param string $url
 * @param boolean $absolute
 * @param string $env
 * @param boolean $debug
 * @return string
 */
function cross_app_url_for($appname, $url, $absolute = false, $env = null, $debug = NULL)
{
  $initial_app = sfContext::getInstance()->getConfiguration()->getApplication();
  $initial_web_controler = basename(sfContext::getInstance()->getRequest()->getScriptName());
  $initial_config = sfConfig::getAll();
  $stack = sfContext::getInstance()->getActionStack();
  
  // get the environment
  if ( $env == null )
  {
    $env = sfContext::getInstance()->getConfiguration()->getEnvironment();
  }
  
  // get the debug context
  if ( $debug === NULL )
  {
    $debug = sfConfig::get('sf_debug', false);
  }
  
  // context creation
  if (!sfContext::hasInstance($appname))
  {
    $context = sfContext::createInstance(ProjectConfiguration::getApplicationConfiguration($appname, $env, $debug), $appname);
    $context->getEventdispatcher()->notify(new sfEvent($context, 'context.load_factories'));
  }
  else
  {
    $context = sfContext::getInstance($appname);
  }
  $web_url = str_replace(
    $initial_web_controler.'/'.$initial_web_controler,
    $initial_web_controler,
    $context->getController()->genUrl($url, $absolute)
  );
  sfContext::switchTo($initial_app);
  sfConfig::add($initial_config);
  unset($context);
  if ( sfContext::getInstance()->getActionStack()->getSize() != $stack->getSize() )
  {
    while ( sfContext::getInstance()->getActionStack()->popEntry() );
    while ( $entry = $stack->popEntry() )
      sfContext::getInstance()->getActionStack()->addEntry($entry->getModuleName(), $entry->getActionName(), $entry);
  }

  //remove initial web controler
  // genUrl use $this->context->getRequest()->getScriptName();, its a call to $_SERVER
  // so starting the shameless part !
  $script_name = $appname;
  if (($env != 'prod') and $env)
  {
    $script_name.='_'.$env;
  }
  elseif ($script_name == "frontend")
  {
    $script_name="index";
  }
  $script_name.='.php';
  
  // check if this file exist
  if (!file_exists(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$script_name))
    throw new sfException('can t find '.$script_name.' in the web directory');
  
  $web_url = str_replace ($initial_web_controler, $script_name, $web_url);

  return $web_url;
}

function cross_app_link_to($text, $appname, $url, $absolute = false, $env = null, $debug = false, $params = '')
{
  return '<a href="'.cross_app_url_for($appname, $url, $absolute, $env, $debug).'" '.$params.'>'.$text.'</a>';
}
