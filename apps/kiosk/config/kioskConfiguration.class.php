<?php

class kioskConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
  }

  public static function getText($var, $default = '')
  { 
    $txt = sfConfig::get($var, $default);
    $culture = sfContext::hasInstance() && sfContext::getInstance()->getUser() instanceof sfUser
      ? sfContext::getInstance()->getUser()->getCulture()
      : false;
    
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
}
