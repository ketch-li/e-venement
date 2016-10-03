<?php

require_once dirname(__FILE__).'/../lib/geo_fr_street_baseGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/geo_fr_street_baseGeneratorHelper.class.php';

/**
 * geo_fr_street_base actions.
 *
 * @package    e-venement
 * @subpackage geo_fr_street_base
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class geo_fr_street_baseActions extends autoGeo_fr_street_baseActions
{
  public function executeAjax(sfWebRequest $request)
  {
    if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) )
    {
      $this->getResponse()->setContentType('text/html');
      $this->setLayout('nude');
    }
    else
    {
      sfConfig::set('sf_web_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
    
    $this->addresses = array();
    $transliterate = sfConfig::get('software_internals_transliterate');
    
    $addr = explode("\n",$this->sanitizeSearch($request->getParameter('address')));
    $address  = explode(' ',$addr[count($addr)-1]);
    $city    = strtoupper(str_replace(array(' '), array('-'), $this->sanitizeSearch($request->getParameter('city'))));
    $cityst  = str_replace(array('STE-', 'ST-'), array('SAINTE-', 'SAINT-'), $city); // a french specificity
    $zip  = preg_replace('/\s+/','',$this->sanitizeSearch($request->getParameter('zip')));
    
    if ( !$zip || !$city || mb_strlen(implode('', $addr)) < 5 )
      return 'Success';
    
    $q = Doctrine::getTable('GeoFrStreetBase')->createQuery('sb')
      ->andWhere('sb.zip = ?', $zip)
      ->andWhere('sb.city = ? OR sb.city = ?', array($city, $cityst))
      ->orderBy('sb.address')
      ->limit($request->getParameter('limit', 30))
      ->select('sb.address')
    ;
    foreach ( $address as $elt )
      $q->andWhere(sprintf("TRANSLATE(sb.address, '%s', '%s') ILIKE ?", $transliterate['from'], $transliterate['to']), '%'.$elt.'%');
    foreach ( $q->fetchArray() as $sb )
      $this->addresses[] = $sb['address'];
  }
  
  public function executeDistricts(sfWebRequest $request)
  {
    if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) )
    {
      $this->getResponse()->setContentType('text/html');
      $this->setLayout('nude');
    }
    else
    {
      sfConfig::set('sf_web_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
    
    $this->districts = array();
    $search = $this->sanitizeSearch($request->getParameter('q'));
    
    $q = Doctrine::getTable('GeoFrDistrictBase')->createQuery('db')
      ->orderBy('db.name, db.id')
      ->limit($request->getParameter('limit', 10))
      ->select('db.id, db.name')
      ->andWhere('db.name ILIKE ?', '%'.$search.'%')
      ->select('db.*')
      
      ->leftJoin('db.GeoFrStreetBase sb')
      ->andWhere('sb.id IS NOT NULL')
    ;
    foreach ( $q->fetchArray() as $db )
      $this->districts[$db['id']] = $db['name'];
  }
  
  protected function sanitizeSearch($str)
  {
    return GeoFrStreetBaseForm::sanitizeSearch($str);
  }
}
