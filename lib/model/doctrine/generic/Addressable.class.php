<?php

/**
 * Addressable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @author     Ayoub HIDRI <ayoub.hidri AT gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Addressable extends PluginAddressable
{
  public function setUp()
  {
    parent::setUp();
    $this->_table->getTemplate('Doctrine_Template_Searchable')
      ->getPlugin()
      ->setOption('analyzer',new MySearchAnalyzer());
  }
  
  public function getUpdatedAtIso8601()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Date8601'));
    return format_datetime_iso8601($this->updated_at);
  }
  public function getCreatedAtIso8601()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Date8601'));
    return format_datetime_iso8601($this->created_at);
  }

  public function getJSSlug()
  {
    return str_replace('-','_',$this->slug);
  }
  
  public function setLatitude($value)
  {
    if ( !$value )
      $this->latitude = NULL;
    else
      $this->latitude = $value;
    return $this;
  }
  public function setLongitude($value)
  {
    if ( !$value )
      $this->longitude = NULL;
    else
      $this->longitude = $value;
    return $this;
  }
  
  // methods stolen from Traceable
  public function preSave($event)
  {
    if ( $this->isModified() )
    {
      if ( sfContext::hasInstance() && sfContext::getInstance()->getUser()->getId() )
      {
        $this->last_accessor_id = sfContext::getInstance()->getUser()->getId();
        $this->automatic = false;
      }
      else
        $this->automatic = true;
      $this->updated_at = date('Y-m-d H:i:s');
    }
    parent::preSave($event);
  }
  
  public function preInsert($event)
  {
    if ( sfContext::hasInstance() && sfContext::getInstance()->getUser()->getId() )
    {
      if ( is_null($this->last_accessor_id) )
        $this->last_accessor_id = sfContext::getInstance()->getUser()->getId();
    }
    else
    {
      $this->last_accessor_id = NULL;
      $this->automatic = true;
    }
    
    if ( is_null($this->created_at) )
      $this->created_at = date('Y-m-d H:i:s');
    parent::preInsert($event);
  }
  
  public function copy($deep = FALSE)
  {
    $t = parent::copy($deep);
    
    $t->updated_at = NULL;
    $t->created_at = NULL;
    $t->last_accessor_id = NULL;
    
    return $t;
  }
}
