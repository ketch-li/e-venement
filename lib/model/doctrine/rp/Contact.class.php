<?php

/**
 * Contact
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Contact extends PluginContact
{
  protected $module = 'contact';
  
  public function __toString()
  {
    if ( !sfConfig::get('app_case_normalise') )
      return $this->name.' '.$this->firstname;
    else
      return strtoupper($this->name).' '.ucwords(strtolower($this->firstname));
  }
  
  public function getFormattedName()
  {
    return ucfirst($this->firstname).' '.strtoupper($this->name);
  }
  
  public function getDepartment()
  {
    if ( trim(strtolower($this->country)) !== 'france' && $this->country || !$this->postalcode )
      return false;
    
    return Doctrine::getTable('GeoFrDepartment')->fetchOneByNumCP(substr($this->postalcode,0,2));
  }
  public function getRegion()
  {
    if ( $dpt = $this->getDepartment() )
      return $dpt->Region;
    else
      return false;
  }
  
  public function getYOBsString()
  {
    $arr = array();
    foreach ( $this->YOBs as $YOB )
      $arr[] = (string)$YOB;
    sort($arr);
    return implode(', ',$arr);
  }
  
  public function getIdBarcoded()
  {
    $c = ''.$this->id;
    $n = strlen($c);
    for ( $i = 12-$n ; $i > 0 ; $i-- )
      $c = '0'.$c;
    return $c;
  }
  
  public function postInsert($event)
  {
    foreach ( $this->Professionals as $pro )
      $pro->contact_id = $this->id;
    
    foreach ( $this->Phonenumbers as $pn )
      $pn->contact_id = $this->id;
    
    parent::postInsert($event);
  }
}
