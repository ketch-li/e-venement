<?php

/**
 * Gauge
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Gauge extends PluginGauge
{
  protected $seats = NULL;
  
  public function __toString()
  {
    return (string)$this->Workspace->name;
  }
  
  public function getFree($count_demands = false)
  {
    if ( !isset($this->printed) || !isset($this->ordered) || !isset($this->asked) )
    {
      $gauge = $this->getTable()->find($this->id);
      foreach ( array('printed', 'ordered', 'asked') as $field )
        $this->$field = $gauge->$field;
    }
    
    return $this->value
      - $this->printed
      - $this->ordered
      - ($count_demands ? $this->asked : 0);
  }
  
  public function getAvailableUnits()
  {
    return $this->getFree();
  }
  
  public function getHeldFreeSeats($refresh = false)
  {
    if ( $this->seats instanceof Doctrine_Collection )
      return $this->seats;
    
    $q = Doctrine::getTable('Seat')->createQuery('s')
      ->innerJoin('s.SeatedPlan sp')
      ->innerJoin('sp.Workspaces w')
      ->innerJoin('w.Gauges g')
      ->innerJoin('s.Holds h')
      ->andWhere('h.manifestation_id = g.manifestation_id')
      ->andWhere('g.id = ?', $this->id)
      ->select('s.*')
    ;
    return $this->seats = $q->execute();
  }
  
  public function preSave($event)
  {
    if ( is_null($this->value) )
      $this->value = 0;
    parent::preSave($event);
  }
  
  public function getSeatedPlan()
  {
    return $this->Manifestation->Location->getWorkspaceSeatedPlan($this->workspace_id);
  }
  
  public function getPriceMax($users = NULL)
  {
    $values = $this->getAllPriceValues($users);
    if ( count($values) == 0 )
      return 0;
    return max($values);
  }
  public function getPriceMin($users = NULL)
  {
    $values = $this->getAllPriceValues($users);
    if ( count($values) == 0 )
      return 0;
    
    return min($values);
  }
  public function getAllPriceValues($users = NULL)
  {
    if ( !$users )
      $users = NULL;
    
    $prices = array();
    foreach ( $this->PriceGauges as $pg )
    {
      $go = !(is_array($users) && count($users) > 0);
      if ( !$go )
      foreach ( $users as $user )
      if ( $user instanceof liGuardSecurityUser && $pg->Price->isAccessibleBy($user)
        || is_integer($user) && in_array($user, $pg->Price->Users->getPrimaryKeys()) )
      {
        $go = true;
        break;
      }
      
      if ( $go )
        $prices[$pg->price_id] = $pg->value;
    }
    
    foreach ( $this->Manifestation->PriceManifestations as $pm )
    if ( !isset($prices[$pm->price_id]) )
    {
      $go = !(is_array($users) && count($users) > 0);
      if ( !$go )
      foreach ( $users as $user )
      if ( $user instanceof liGuardSecurityUser && $pm->Price->isAccessibleBy($user)
        || is_integer($user) && in_array($user, $pm->Price->Users->getPrimaryKeys()) )
      {
        $go = true;
        break;
      }
      
      if ( $go )
        $prices[$pm->price_id] = $pm->value;
    }
    
    if ( $users && count($prices) == 0 )
      $prices = $this->getAllPriceValues();
    return $prices;
  }
  
  public function isAccessibleBy(Doctrine_Collection $users, $online = NULL, $everybody_or_nothing = false)
  {
    if ( $users->getTable()->getComponentName() != 'sfGuardUser' )
      throw new liEvenementException('Gauge::isAccessibleBy expects a Doctrine_Collection from the sfGuardUser model. '.$users->getTable()->getComponentName().' given.');
    
    if ( $users->count() == 0 )
      return true;
    
    $ok = false;
    foreach ( $users as $user )
    {
      if ( !in_array($user->id, $this->Workspace->Users->getPrimaryKeys()) )
      {
        if ( $everybody_or_nothing ) break;
        continue;
      }
      if ( !in_array($user->id, $this->Manifestation->Event->MetaEvent->Users->getPrimaryKeys()) )
      {
        if ( $everybody_or_nothing ) break;
        continue;
      }
      
      if ( is_null($online) )
        $ok = true;
      else
        $ok = $this->online === $online;
      
      if ( !$everybody_or_nothing && $ok )
        break;
    }
    
    return $ok;
  }
}
