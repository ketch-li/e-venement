<?php

/**
 * Location
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Location extends PluginLocation
{
  protected $module = 'location';

  public function setUp()
  {
    parent::setUp();
    $this->_table->getTemplate('Doctrine_Template_Searchable')
      ->getPlugin()
      ->setOption('analyzer',new MySearchAnalyzer());
  }
  
  public function getFullAddress()
  {
    $arr = array();
    if ( trim($this->address) )
      $arr[] = $this->address;
    $arr[] = $this->postalcode.' '.$this->city;
    $arr[] = $this->country;
    return implode("\n",$arr);
  }
  
  /**
   * function getWorkspaceSeatedPlan
   * @param $workspace_id integer
   * @return FALSE if no seated plan has been found, SeatedPlan elsewhere
   **/
  public function getWorkspaceSeatedPlan($workspace_id)
  {
    if ( intval($workspace_id) == 0 )
      throw new liSeatedException('Bad workspace_id given.');
    
    foreach ( $this->SeatedPlans as $seated_plan )
    foreach ( $seated_plan->Workspaces as $ws )
    if ( $ws->id == $workspace_id )
      return $seated_plan;
    
    return false;
  }
  
  public function __toStringWithPrefix()
  {
    return (!$this->place ? 'R: ' : '').$this;
  }
}
