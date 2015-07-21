<?php

/**
 * Product
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Product extends PluginProduct implements liUserAccessInterface
{
  public function setUp()
  {
    parent::setUp();
    $this->_table->getTemplate('Doctrine_Template_Searchable')
      ->getPlugin()
      ->setOption('analyzer',new MySearchAnalyzer());
  }
  
  public function isAccessibleBy(sfSecurityUser $user, $option = NULL)
  {
    // "online" or not
    if ( $user instanceof pubUser && !$this->Category->online )
      return false;
    
    // meta event
    if ( $this->meta_event_id && !in_array($this->meta_event_id, array_keys($user->getMetaEventsCredentials())) )
      return false;
    
    // prices
    if ( $this->Prices->count() == 0 )
      return false;
    foreach ( $this->Prices as $price )
    if ( !in_array($user->getId(), $price->Users->getPrimaryKeys()) )
      return false;
    
    // declinations
    if ( $this->Declinations->count() == 0 )
      return false;
    
    return true;
  }
  
  /**
   * @param user  sfSecurityUser  the current user if a credentials check is expected
   * @return FALSE|array  array('value' => integer, 'price' => PriceProduct)
   **/
  public function getMostExpansivePrice(sfSecurityUser $user = NULL)
  {
    $max = array('value' => -1, 'price' => NULL);
    if ( $this->Prices->count() == 0 )
      return false;
    foreach ( $this->PriceProducts as $pp )
    if (!( $user && !$pp->Price->isAccessibleBy($user) ))
    if ( !is_null($pp->value) && $pp->value > $max['value'] )
      $max = array('value' => $pp->value, 'price' => $pp);
    return $max;
  }
  
  public function getStocksData($texts = array(
    'critical'  => 'critical',
    'correct'   => 'correct',
    'perfect'   => 'perfect',
  ), $json)
  {
    $data = array(
      'id' => $this->id,
      'declinations' => array(),
      'texts' => $texts,
    );
    
    foreach ( $this->Declinations as $declination )
      $data['declinations'][$declination->id] = array(
        'name' => $declination->name,
        'code' => $declination->code,
        'id'   => $declination->id,
        'current'  => $declination->stock,
        'critical' => $declination->stock_critical,
        'perfect'  => $declination->stock_perfect,
      );
    
    return $json ? json_encode($data) : $data;
  }
}
