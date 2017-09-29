<?php
/**
 * @author Olivier Mansour
 * @author Baptiste Simon
 * @author Libre Informatique [http://www.libre-informatique.fr/]
 */

 function getAppRouting($route_file)
 {
   $frontendRouting = new sfPatternRouting(new sfEventDispatcher());

   $config = new sfRoutingConfigHandler();
   $routes = $config->evaluate(array(sfConfig::get('sf_apps_dir') . DIRECTORY_SEPARATOR . $route_file . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routing.yml'));

   $frontendRouting->setRoutes($routes);

   return $frontendRouting;
 }

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
function cross_app_url_for($appname, $parameters, $absolute = false, $env = null, $debug = NULL)
{

    $route = '';
    $fragment = '';

    if (is_string($parameters))
    {
      // absolute URL or symfony URL?
      if (preg_match('#^[a-z][a-z0-9\+.\-]*\://#i', $parameters))
      {
        return $parameters;
      }

      // relative URL?
      if (0 === strpos($parameters, '/'))
      {
        return $parameters;
      }

      if ($parameters == '#')
      {
        return $parameters;
      }

      // strip fragment
      if (false !== ($pos = strpos($parameters, '#')))
      {
        $fragment = substr($parameters, $pos + 1);
        $parameters = substr($parameters, 0, $pos);
      }

      list($route, $params) = sfContext::getInstance()->getController()->convertUrlStringToParameters($parameters);
    }
    else if (is_array($parameters))
    {
      if (isset($parameters['sf_route']))
      {
        $route = $parameters['sf_route'];
        unset($parameters['sf_route']);
      }
    }


  $url = getAppRouting($appname)->generate($route, $params, $absolute);
  
  // get the environment
  if ( $env == null )
  {
    $env = sfContext::getInstance()->getConfiguration()->getEnvironment();
  }

  $initial_app = sfContext::getInstance()->getConfiguration()->getApplication();
  $initial_web_controler = basename(sfContext::getInstance()->getRequest()->getScriptName());


  $web_url = str_replace(
    $initial_web_controler.'/'.$initial_web_controler,
    $initial_web_controler,
    $url
  );
  
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

  $web_url = '/' . $script_name . $web_url;

  return $web_url;
}

function cross_app_link_to($text, $appname, $url, $absolute = false, $env = null, $debug = false, $params = '')
{
  return '<a href="'.cross_app_url_for($appname, $url, $absolute, $env, $debug).'" '.$params.'>'.$text.'</a>';
}
