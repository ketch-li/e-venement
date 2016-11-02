<?php

require_once dirname(__FILE__).'/../lib/ControlGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/ControlGeneratorHelper.class.php';

/**
 * web_origin actions.
 *
 * @package    e-venement
 * @subpackage web_origin
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ControlActions extends autoControlActions
{
  public function executeBatchExceptions(sfWebRequest $request)
  {
    $this->filters = $this->getUser()->getAttribute($this->getModuleName().'.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    $this->filters['excluded_ids'] = $request->getParameter('ids');
    $this->getUser()->setAttribute($this->getModuleName().'.filters', $this->filters, 'admin_module');
  }
  
  public function executeJson(sfWebRequest $request)
  {
    $this->debug($request);
    $data = $this->getData(true);
    $this->lines = array();
    $total = 0;
    
    foreach ( $data as $line )
        $total += $line[0];
      
    foreach ( $data as $line )
      $this->lines[intval($line[1])] = array(
        'value'   => $line[0],
        'percent' => number_format(round($line[0]*100/$total,2))
      );
  }

  protected function debug(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date', 'I18N'));
    if ( sfConfig::get('sf_web_debug', true) && $request->hasParameter('debug') )
    {
      $this->setLayout('layout');
      $this->getResponse()->setContentType('text/html');
      $this->getResponse()->setHttpHeader('Content-Disposition', NULL);
      $this->getResponse()->sendHttpHeaders();
    }
    else
      sfConfig::set('sf_web_debug', false);
    
    return sfConfig::get('sf_web_debug', false);
  }

  protected function getData($sysdate = false)
  {
    $data = array();
    for ( $i = 0 ; $i < 23 ; $i++ )
      $data[$i] = 0;
    
    $dql = $this->buildQuery()->removeDqlQueryPart('orderby');
    $a = $dql->getRootAlias();
    $dql->groupBy("date_part('hour', $a.created_at)")
        ->orderBy("date_part('hour', $a.created_at)")
        ->select("count($a.id) AS nb, date_part('hour', $a.created_at) AS hour")
    ;
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $stmt = $pdo->prepare($dql->getRawSql());
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    return $data;
  }
}
