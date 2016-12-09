<?php

/**
 * OptionPubTexts form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OptionPubTextsForm extends BaseOptionPubTextsForm
{
  /**
   * @see OptionForm
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    parent::configure();

    $this->model = 'OptionPubTexts';
    
    foreach ( array('type','name','value','sf_guard_user_id','created_at','updated_at',) as $id )
    {
      unset($this->widgetSchema   [$id]);
      unset($this->validatorSchema[$id]);
    }
    
    $helpers = $this->getHelpers();
    $this->widgets = array();
    foreach ( $this->getDBOptions() as $key => $text )
    {
      $struct = $this->getStructuredFromRawName($key);
      $name = ucwords(str_replace('_', ' ', $struct['name']));
      $lang = $struct['lang'];
      
      if ( !isset($this->widgets[$name]) )
        $this->widgets[$name] = array();
      
      $this->widgets[$name][$key] = array(
        'label' => strtoupper($lang),
        'type' => 'string',
        'helper' => isset($helpers[$struct['name']]) ? $helpers[$struct['name']] : null,
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
    return array(
      'payment_onthespot_info'     => __("Content of the popup which appears after a 'on site' payment"),
      'terms_conditions_file'      => __("Adding a file here will overwrite your Terms & Conditions text..."),
      // TODO: complete, if necessary, with other params
    );
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
        'manifestation_bottom'    => $i18n->__("Placement libre.\nPaiement par carte bancaire."),
        'payment_onthespot_info'  => $i18n->__('Thank you in advance to send us your payment as soon as possible so that we can confirm your order'),
        'cart_bottom'             => '',
        'cart_end'                => '',
        'contact_new'             => '',
        'email_confirmation'      => '',
        'manifestation_closed'    => '',
        'manifestation_bottom'    => '',
        'manifestation_full'      => '',
        'when_closed'             => '',
        'event_bottom'            => '',
        'store_nomore'            => '',
        'meta_events_header'      => '',  // empty by default
        'meta_events_title'       => '',  // empty by default
        'meta_events_bottom'      => '',  // empty by default
        'card_bottom'             => '',
        'contact_bottom'          => '',  // empty by default
        'seated_plan_loading'     => '',  // empty by default
        'terms_conditions'        => '',  // empty by default
        'terms_conditions_file'   => '',  // empty by default
        'email_footer'            => '',  // empty by default
        'email_confirmation'      => '
Voici le récapitulatif de votre commande en date du %%DATE%% :

%%COMMAND%%

Au plaisir de vous accueillir,

%%NOTICES%%

--
Ma Structure
27 avenue de la Libération
99250 LIBERTALIA
+999 53 20 16 45

<a href="mailto:contact@mastructure.tld">mailto:contact@mastructure.tld</a>
<a href="http://www.mastructure.tld/">http://www.mastructure.tld</a>
',
        // TODO: complete like the previous example with all the texts from online sales, foundable in apps/pub/config/app.yml.template
        // careful: the i18n of those terms have to be placed in the "ws" app, whereas their storage for production use will be located in the DB
      );
      ksort($terms[$lang]);
    }
    $langs = array_keys($langs);

    // switching into the "pub" environment
    $initial_app = sfContext::getInstance()->getConfiguration()->getApplication();
    $initial_web_controler = basename(sfContext::getInstance()->getRequest()->getScriptName());
    $initial_config = sfConfig::getAll();
    $stack = sfContext::getInstance()->getActionStack();
    $env = sfContext::getInstance()->getConfiguration()->getEnvironment();
    $context = !sfContext::hasInstance('pub')
      ? sfContext::createInstance(ProjectConfiguration::getApplicationConfiguration('pub', $env, sfConfig::get('sf_web_debug')), 'pub')
      : sfContext::getInstance('pub');
    
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
    
    // get out of the "pub" environment
    sfContext::switchTo($initial_app);
    sfConfig::add($initial_config);
    unset($context);
    if ( sfContext::getInstance()->getActionStack()->getSize() != $stack->getSize() )
    {
      while ( sfContext::getInstance()->getActionStack()->popEntry() );
      while ( $entry = $stack->popEntry() )
        sfContext::getInstance()->getActionStack()->addEntry($entry->getModuleName(), $entry->getActionName(), $entry);
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
    return $q = Doctrine::getTable('OptionPubTexts')->createQuery('o')
      ->andWhere('o.sf_guard_user_id IS NULL')
      ->andWhere('o.type = ?', 'pub-texts');
  }
}
