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
    if ( $request->hasParameter('debug') && $this->getContext()->getConfiguration()->getEnvironment() == 'dev' )
    {
      $this->getResponse()->setContentType('text/html');
      sfConfig::set('sf_debug',true);
      $this->setLayout('nude');
    }
    else
    {
      sfConfig::set('sf_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
    
    $this->addresses = array();
    
    $address  = explode(' ',$addr = $this->sanitizeSearch($request->getParameter('address')));
    $city    = strtoupper(str_replace(array(' '), array('-'), $this->sanitizeSearch($request->getParameter('city'))));
    $cityst  = str_replace(array('STE-', 'ST-'), array('SAINTE-', 'SAINT-'), $city);
    $zip  = preg_replace('/\s+/','',$this->sanitizeSearch($request->getParameter('zip')));
    
    if ( !$zip || !$city || mb_strlen($addr) < 5 )
      return 'Success';
    
    $q = Doctrine::getTable('GeoFrStreetBase')->createQuery('sb')
      ->andWhere('sb.zip = ?', $zip)
      ->andWhere('sb.city = ? OR sb.city = ?', array($city, $cityst))
      ->orderBy('sb.address')
      ->limit($request->getParameter('limit', 30))
      ->select('sb.address')
    ;
    foreach ( $address as $elt )
      $q->andWhere('sb.address ILIKE ?', '%'.$elt.'%');
    foreach ( $q->fetchArray() as $sb )
      $this->addresses[] = $sb['address'];
  }
  
  protected function sanitizeSearch($str)
  {
    return GeoFrStreetBaseForm::sanitizeSearch($str);
  }
}
