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
*    Copyright (c) 2006-2011 Romain SANCHEZ <romain.sanchez AT libre-informatique.fr>
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * OptionKioskTexts form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OptionKioskTextsForm extends BaseOptionKioskTextsForm
{
  /**
   * @see OptionForm
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    parent::configure();

    $this->model = 'OptionKioskTexts';
    
    foreach ( array('type','name','value','sf_guard_user_id','created_at','updated_at',) as $id )
    {
      unset($this->widgetSchema   [$id]);
      unset($this->validatorSchema[$id]);
    }
    
    $helpers = $this->getHelpers();
    $this->widgets = array();
    $Options = $this->getDBOptions();
    foreach ( $Options as $key => $text )
    {
      $struct = $this->getStructuredFromRawName($key);
      $name = ucwords(str_replace('_', ' ', $struct['name']));
      $lang = $struct['lang'];
      
      if ( !isset($this->widgets[$name]) )
        $this->widgets[$name] = array();
    
      $helperContent = isset($helpers[$struct['name']]) ? $helpers[$struct['name']] : null;
      
      if ( strpos($struct['name'], '_file') !== false )
      {
          $helperContent .= '<br>'.$Options[str_replace('_file', '', $key)];
      }
      
      $this->widgets[$name][$key] = array(
        'label' => strtoupper($lang),
        'type' => 'string',
        'helper' => $helperContent,
        'default' => $text,
      );
    }
    
    $this->convertConfiguration($this->widgets);
    
    foreach ( $this->widgetSchema->getFields() as $name => $widget )
    if ( strpos($name, '_file((') !== false )
    {
      // a file for terms & conditions
      $widget = $this->widgetSchema[$name];
      $this->widgetSchema[$name] = new sfWidgetFormInputFile;
      $this->widgetSchema[$name]->setLabel($widget->getLabel());
    }
  }
  
  protected function convertConfiguration($widgets)
  {
    foreach ( $widgets as $fieldset )
    foreach ( $fieldset as $name => $value )
    {
      $validator_class = 'sfValidator'.strtoupper(substr($value['type'],0,1)).strtolower(substr($value['type'],1));
      
      if ( !isset($this->widgetSchema[$name]) )
	      $this->widgetSchema[$name]    = new sfWidgetFormTextArea(array(
	          'label'                 => $value['label'],
	          'default'               => $value['default'],
	        ),
	        array(
	          'title'                 => __('previous:').' '.$value['default'].' '.$value['helper'],
	      ));
	  
      $this->validatorSchema[$name] = new $validator_class(array(
        'required' => false,
      ));
    }
  }
  
  protected static function getStructuredFromRawName($name)
  {
    preg_match('/^(.+)\(\((\w+)\)\)/', $name, $matches);
    
    return array('lang' => $matches[2], 'name' => $matches[1]);
  }
  
  public static function getHelpers()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    return array();
  }
  
  public static function getDefaultValues()
  {
    $terms = array();
    foreach ( $langs = sfConfig::get('project_internals_cultures', array('fr' => 'Français')) as $lang => $desc )
    {
      $i18n = new sfI18N(
        sfContext::getInstance()->getConfiguration(),
        new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir'))),
        array('culture' => $lang)
      );
      
      $terms[$lang] = array(
        'menu_manifestations'     => '',
        'menu_museum'             => '',
        'menu_store'              => '',
        'breadcrumb_home'         => '',
        'breadcrumb_products'     => '',
        'cart_total'              => '',
        'cart_validate'           => '',
        'location_title'          => '',
        'location_close'          => '',
        'postcode'                => '',
        'postcode_validation'     => '',
        'retry'                   => '',
        'cancel'                  => '',
        'hardware_title'          => '',
        'hardware_details'        => '',
        'payment_title'           => '',
        'payment_failure'         => '',
        'payment_success_title'   => '',
        'payment_success_details' => '',
        'ticket_failure'          => '',
        'final_title'             => '',
        'final_details'           => '',
      );
      ksort($terms[$lang]);
    }
    
    $langs = array_keys($langs);
    
    $defaults = array();
    foreach ( $terms as $lang => $texts )
    foreach ( $texts as $key => $text )
    if ( !sfConfig::has('app_texts_'.$key) )
      $defaults["$key(($lang))"] = $text;
    else
    {
      if ( is_array($tmp = sfConfig::get('app_texts_'.$key)) )
        $defaults["$key(($lang))"] = isset($tmp[$lang]) ? $tmp[$lang] : $text;
      else
        $defaults["$key(($lang))"] = $lang == $langs[0] ? $tmp : $text;
    }
    
    return $defaults;
  }
  
  public static function getStructuredDBOptions()
  {
    $structured = array();
    foreach ( self::getDBOptions() as $name => $value )
    {
      $name = self::getStructuredFromRawName($name);
      $structured[$name['name']][$name['lang']] = $value;
    }
    
    return $structured;
  }

  public static function getDBOptions()
  {
    $cultures = sfConfig::get('project_internals_cultures', array('fr' => 'Français'));
    $r = array();
    $r = self::getDefaultValues();
    
    // DB values
    foreach ( self::buildOptionsQuery()->execute() as $text )
    {
      if ( !isset($text['name']) )
        $text['name'] = '';
      $r[$text['name']] = $text['value'];
    }
    
    return $r;
  }

  protected static function buildOptionsQuery()
  {
    return $q = Doctrine::getTable('OptionKioskTexts')->createQuery('o')
      ->andWhere('o.sf_guard_user_id IS NULL')
      ->andWhere('o.type = ?', 'kiosk-texts');
  }
}
