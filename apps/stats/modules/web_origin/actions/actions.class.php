<?php

require_once dirname(__FILE__).'/../lib/web_originGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/web_originGeneratorHelper.class.php';

/**
 * web_origin actions.
 *
 * @package    e-venement
 * @subpackage web_origin
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class web_originActions extends autoWeb_originActions
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
    $wich = $request->getParameter('which', 'referers');
    $data = $this->getData($wich, true);
    $this->lines = array();
    $total = 0;
    
    $previous = NULL;
    foreach ( $data as $date => $value )
    {
      if ( $previous && strtotime($previous) < strtotime('-1 day', strtotime($date)) )
      {
        $tmp = strtotime($previous);
        while ( ($tmp = strtotime('+1 day', $tmp)) < strtotime($date) )
          $this->lines[date('Y-m-d H:i:s', $tmp)] = 0;
      }
      $this->lines[$date] = $value;
      $previous = $date;
    }

    foreach ( $this->lines as $line )
        $total += $line;
      
    foreach ( $this->lines as $key => $line )
      $this->lines[$key] = array(
        'value'   => $line,
        'percent' => number_format(round($line*100/$total,2))
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

  protected function getData($which = 'referers', $sysdate = false)
  {
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $limit = 10;
    $dql = $this->buildQuery()->removeDqlQueryPart('orderby');
    $dql->leftJoin('t.MemberCards m');
    $dql->andWhere('wo.next_id IS NULL OR p.id IS NOT NULL');
    $sql = preg_replace('/^SELECT .* FROM/', '', $dql->getRawSql());
    
    switch ( $which ) {
    case 'referers':
      $domain = "regexp_replace(w.referer, '".WebOriginFormFilter::SQL_REGEX_URL_FORMAT."', '\\2', 'ix')";
      $q = "SELECT $domain AS criteria, count(w.id) AS nb FROM $sql GROUP BY $domain";
      break;
    case 'campaigns':
      $q = "SELECT (CASE WHEN m.detail IS NOT NULL AND m.detail != '' THEN 'Promo: '||m.detail ELSE w.campaign END) AS criteria, count(w.id) AS nb FROM $sql GROUP BY w.campaign, m.detail";
      $limit++; // to remove the empty campaign in the post production
      break;
    case 'deal_done':
      $criteria = '(SELECT DISTINCT ppp.id IS NOT NULL OR ooo.id IS NOT NULL FROM transaction ttt LEFT JOIN payment ppp ON ppp.transaction_id = ttt.id LEFT JOIN order_table ooo ON ooo.transaction_id = ttt.id WHERE ttt.id = w.transaction_id)';
      $q = "SELECT CASE WHEN $criteria THEN 'ok' ELSE 'x' END AS criteria, count(w.id) AS nb FROM $sql GROUP BY $criteria";
      break;
    case 'evolution':
      $criteria = "date_trunc('day', w.created_at)";
      $q = "SELECT $criteria AS criteria, count(w.id) AS nb FROM $sql GROUP BY $criteria";
      $limit = 30;
      break;
    }
    
    switch ( $which ) {
    case 'referers':
    case 'campaigns':
    case 'deal_done':
      $q .= " ORDER BY count(w.id) DESC LIMIT $limit";
      break;
    case 'evolution':
      $q .= " ORDER BY $criteria DESC LIMIT $limit";
      break;
    }
    
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    $data = $stmt->fetchAll();
    $this->type = 'pie';
    
    // post production
    foreach ( $data as $key => $values )
    {
      unset($data[$key]);
      $data[$values['criteria']] = $values['nb'];
    }
    switch ( $which ) {
    case 'campaigns':
      foreach ( $data as $criteria => $value )
      if ( !$criteria )
        unset($data[$criteria]);
    case 'referers':
      if ( ($value = $dql->count() - array_sum($data)) > 0 )
        $data[__('empty information')] = $dql->count() - array_sum($data); // the rest
      break;
    case 'evolution':
      $this->type = 'line';
      foreach ( $data as $criteria => $value )
      if ( strtotime($criteria) < strtotime('-1 month') )
        unset($data[$criteria]);
      
      // completing empty days
      for ( $i = 0 ; $i < 31 ; $i++ )
      {
        $key = date('Y-m-d 00:00:00', strtotime("-$i day"));
        $data[$key] = isset($data[$key]) ? $data[$key] : 0;
      }
      ksort($data);
      $tmp = array();
      foreach ( $data as $key => $value )
        $tmp[$sysdate ? $key : format_date($key,'d')] = $value; // to have human readable dates
      $data = $tmp;
      break;
    }
    
    return $data;
  }
}
