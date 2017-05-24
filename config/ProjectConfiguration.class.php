<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/autoload.inc.php';

class ProjectConfiguration extends sfProjectConfiguration implements liGarbageCollectorInterface
{
  public $yob;
  public $charset       = array();
  public $transliterate = array('from' => '', 'to' => '');
  public $overload_config_file;

  protected $routings = array();
  protected $extraMenus = array();
  
  public function setup()
  {
    if (!( defined('SF_USE_LOCAL_RW_DIRS') && SF_USE_LOCAL_RW_DIRS ))
    {
      $ucache = basename($this->getRootDir()).'-'.md5($this->getRootDir());
      $this->setCacheDir("/tmp/$ucache/cache");
      $this->setLogDir("/tmp/$ucache/log");
      
      foreach ( array("/tmp/$ucache/cache", "/tmp/$ucache/log") as $dir )
      if ( !file_exists($dir) )
      {
        mkdir($dir, 0777, true);
        chmod(dirname($dir), 0777); // to be sure...
        chmod($dir, 0777); // to be sure...
      }
    }
    
    // year of birth
    $this->yob = array();
    for ( $i = 0 ; $i < 80 ; $i++ )
      $this->yob[date('Y')-$i] = date('Y') - $i;
    
    // trick to add plugins from an outside-of-the-versioned-project file
    $extraPlugins = is_readable(__DIR__.'/extra-plugins.php') ? require(__DIR__.'/extra-plugins.php') : array();
    if ( !is_array($extraPlugins) )
      $extraPlugins = array('plugins' => array());
    if (!( isset($extraPlugins['plugins']) && is_array($extraPlugins['plugins']) ))
      $extraPlugins['plugins'] = array();
    
    $this->enablePlugins(array_merge(array(
      'sfDoctrineMasterSlavePlugin',
      'sfDoctrinePlugin',
      'sfFormExtraPlugin',
      'sfDoctrineGraphvizPlugin',
      'sfDoctrineGuardPlugin',
      'sfAdminThemejRollerPlugin',
      'cxFormExtraPlugin',
      'sfWebBrowserPlugin',
      'sfFeed2Plugin',
      'sfiCalCreatorPlugin',
      'liOfcPlugin',
      'nvDoctrineSessionStoragePlugin',
    ), $extraPlugins['plugins']));
    
    $this->loadProjectConfiguration();
    
    // transliteration & hyphenation
    $this->charset       = sfConfig::get('software_internals_charset',array());
    $this->transliterate = sfConfig::get('software_internals_transliterate',array('from' => '', 'to' => ''));
    setlocale(LC_ALL,sfConfig::get('project_locale',sfConfig::get('software_internals_locale'))); // w/o it, sometimes transliteration fails
  }
  
  protected function loadProjectConfiguration()
  {
    if ($this instanceof sfApplicationConfiguration)
    {
      require_once $this->getConfigCache()->checkConfig('config/project.yml');
      require_once $this->getConfigCache()->checkConfig('config/e-venement.yml');
    }
  }
  
  public function configureDoctrine(Doctrine_Manager $manager)
  {
    $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'liDoctrineQuery');
  }
  
  public function initialize()
  {
    $this->enableSecondWavePlugins(sfConfig::get('project_internals_plugins', array()));
    $this->loadSecondWavePlugins();
    $this->failover();
    if ( sfConfig::get('project_network_proxy', false) )
    {
      stream_context_set_default($var = array(
        'http'  => array(
          'proxy' => sfConfig::get('project_network_proxy'),
          'request_fulluri' => true,
        ),
        'https' => array(
          'proxy' => sfConfig::get('project_network_proxy'),
          'request_fulluri' => true,
        ),
        'ssl' => array('SNI_enabled' => false), // Disable SNI for https over http proxies
      ));
    }
  }
  
  // pass-by the native symfony restriction, if and only if the plugin developper knows what's going on
  public function enableSecondWavePlugins($plugins)
  {
    if (!is_array($plugins))
    {
      if (func_num_args() > 1)
        $plugins = func_get_args();
      else
        $plugins = array($plugins);
    }
    
    foreach ( is_array($plugins) ? $plugins : (func_num_args() > 1 ? func_get_args() : array($plugins)) as $plugin )
      $this->plugins[] = $plugin;
  }
  
  public function loadSecondWavePlugins()
  {
    $this->pluginPaths = array();
    foreach ( $paths = parent::getPluginPaths() as $path ) // so weird that $this->getPluginPaths() can be called only once whereas parent::getPluginPaths() is ok
    {
      if ( $plugin = array_search($path, $this->overriddenPluginPaths) === false )
        $plugin = basename($path);
      
      if ( isset($this->pluginConfigurations[$plugin]) )
        continue;
      
      $class = $plugin.'Configuration';
      if ( is_readable($file = sprintf('%s/config/%s.class.php', $path, $class)) )
      {
        require_once $file;
        $configuration = new $class($this, $path, $plugin);
      }
      else
        $configuration = new sfPluginConfigurationGeneric($this, $path, $plugin);

      $this->pluginConfigurations[$plugin] = $configuration;
      
      // initialize plugin configuration objects
      if ( false === $configuration->initialize() && is_readable($config = $configuration->getRootDir().'/config/config.php') )
        require $config;
    }
  }
  
  /**
   * @function preparing extra submenus coming from plugins (for instance)
   *
   * @param $menus  array  representing a new menu ex: array('ticketting' => array('Label' => array('url' => array('app' => 'tck', 'route' => 'oc_group/index'), 'credentials' => array('oc-group'),)))
   * @return $this
   *
   **/
  public function appendMenus(array $menus)
  {
    $this->extraMenus = array_merge($menus, $this->extraMenus);
    return $this;
  }
  public function getAppendedMenus($menu = NULL)
  {
    if ( is_null($menu) || !(string)$menu )
      return $this->extraMenus;
    return $this->extraMenus[$menu];
  }
  
  public function failover()
  {
    if ( file_exists($file = sfConfig::get('sf_cache_dir').'/e-venement.failover.trigger') )
    {
      $failover = sfConfig::get('project_about_failover', array());
      header('Location: '.$failover['url']);
      die('failover: this platform is temporarily down.');
    }
    return $this;
  }
  
  // @see liGarbageCollectorInterface
  public function executeGarbageCollectors($names = NULL, $id = NULL)
  {
    if ( is_null($names) )
      $names = array_keys($this->collectors);
    
    if ( !is_array($names) )
      $names = array($names);
    
    foreach ( $names as $name )
    {
      $fct = $this->getGarbageCollector($name);
      if ( $fct instanceof Closure )
        $fct($id);
    }
    
    return $this;
  }
  public function getGarbageCollector($name)
  {
    if ( !isset($this->collectors[$name]) )
      return FALSE;
    return $this->collectors[$name];
  }
  public function addGarbageCollector($name, Closure $function)
  {
    if ( isset($this->collectors[$name]) )
      throw new liEvenementException('A collector with the name "'.$name.'" already exists. Maybe you wanted to replace it ?');
    return $this->addOrReplaceGarbageCollector($name, $function);
  }
  public function addOrReplaceGarbageCollector($name, Closure $function)
  {
    $this->collectors[$name] = $function;
    return $this;
  }
  public function initGarbageCollectors(sfCommandApplicationTask $task = NULL)
  { }
  
  public function getCurrency()
  {
    $currency = sfConfig::get('project_internals_currency', array('iso' => 978, 'symbol' => '€'));
    return isset($currency['symbol']) ? $currency['symbol'] : '€';
  }
  
  protected function catchError(Exception $e)
  {
    // avoid any mistake
    error_log($e->getMessage());
  }
  protected function stdout($section, $message, $style = 'INFO')
  {
    $section = str_pad($section,20);
    if ( !$this->task )
      echo "$section $message";
    else
      $this->task->logSection($section, $message, null, $style);
    return;
  }
}
