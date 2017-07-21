<?php

class kioskConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
  }

  public static function getText($var, $default = '')
  { 
    self::checkConfig();

    $texts = sfConfig::get('app_ui_texts');

    if( isset($texts[$var]) )
      return self::getAppropriateTranslation($texts[$var], self::getUserCulture());

    return $default;
  }

  public static function getTexts()
  {
    $texts = array();
    $culture = self::getUserCulture();

    self::checkConfig();

    foreach( sfConfig::get('app_ui_texts') as $name => $txt )
    {  
      $texts[$name] = self::getAppropriateTranslation($txt, $culture);
    }

    return $texts;
  }

  private static function checkConfig() {
    if( !sfConfig::has('app_ui_texts') )
    {
      $texts = array();

      foreach ( OptionKioskTextsForm::getStructuredDBOptions() as $name => $value )
        $texts[$name] = $value;

      sfConfig::set('app_ui_texts', $texts);
    }
  }

  private static function getAppropriateTranslation($txt, $culture)
  {
    // no translation
    if ( !is_array($txt) )
      return $txt;
    
    // no translation available, keep the first term coming
    if ( !$culture )
      return array_shift($txt);
    
    // the current translation
    if ( isset($txt[$culture]) )
      return $txt[$culture];
    
    // no translation available
    foreach ( $txt as $culture => $value )
    if ( strlen($culture) > 2 )
      return $txt;
    
    // the first translation
    return array_shift($txt);
  }

  private static function getUserCulture()
  {
    return sfContext::hasInstance() && sfContext::getInstance()->getUser() instanceof sfUser
      ? sfContext::getInstance()->getUser()->getCulture()
      : false;
  }
}
