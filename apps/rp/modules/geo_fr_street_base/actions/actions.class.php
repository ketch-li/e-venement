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
      $this->setLayout('layout');
    }
    else
    {
      sfConfig::set('sf_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
    
    $charset = sfConfig::get('software_internals_charset');
    $street  = iconv($charset['db'],$charset['ascii'],$request->getParameter('street'));
    $city    = strtoupper(str_replace(array(' '), array('-'), iconv($charset['db'],$charset['ascii'],$request->getParameter('city')));
    $cityst  = str_replace(array('STE-', 'ST-'), array('SAINTE-', 'SAINT-'), $city);
    $zip  = preg_replace('/\s+/','',iconv($charset['db'],$charset['ascii'],$request->getParameter('zip')));
    
    if ( !$zip || !$city || mb_strlen($street) < 5 )
    {
      $this->streets = array();
      return 'Success';
    }
    
    $q = Doctrine::getTable('GeoFrStreetBase')->createQuery('sb')
      ->andWhere('sb.street ILIKE ?', $street.'%')
      ->andWhere('sb.zip = ?', $zip)
      ->andWhere('sb.city = ? OR sb.city = ?', array($city, $cityst))
    ;
    $streets = array();
    foreach ( $q->fetchArray() as $sb )
      $streets[] = $sb['street'];
    
    print_r($streets);
    die();
  }
}
