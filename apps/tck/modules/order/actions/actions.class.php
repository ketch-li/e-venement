<?php

require_once dirname(__FILE__).'/../lib/orderGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/orderGeneratorHelper.class.php';

/**
 * order actions.
 *
 * @package    e-venement
 * @subpackage order
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class orderActions extends autoOrderActions
{
  public function executeCancel(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    
    if ( intval($id = intval($request->getParameter('id'))) > 0 )
    {
      Doctrine::getTable('Order')->findOneById($id)
        ->delete();
      $this->getUser()->setFlash('notice',__('The given order has been cancelled successfully'));
    }
    else
      $this->getUser()->setFlash('error',__('Unable to find the given order for cancellation'));
    
    $this->redirect('@order');
  }
  
  public function executeSearch(sfWebRequest $request)
  {
    self::executeIndex($request);

    $query = $this->pager->getQuery();
    $search = $this->sanitizeSearch($request->getParameter('s'));
    $query->andWhere('c.name ILIKE ?', $search.'%');    
    
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();

    $this->setTemplate('index');
  }
  
  public static function sanitizeSearch($search)
  {
    $nb = mb_strlen($search);
    $charset = sfConfig::get('software_internals_charset');
    $transliterate = sfConfig::get('software_internals_transliterate',array());
    
    $search = str_replace(preg_split('//u', $transliterate['from'], -1), preg_split('//u', $transliterate['to'], -1), $search);
    $search = str_replace(MySearchAnalyzer::$cutchars,' ',$search);
    $search = preg_replace(array('!^[\d\w] !', '! [\d\w] !'), ' ', $search);
    $search = mb_strtolower(iconv($charset['db'],$charset['ascii'], mb_substr($search,$nb-1,$nb) == '*' ? mb_substr($search,0,$nb-1) : $search));
    return $search;
  }
  
  protected function getFilters()
  {
    $filters = parent::getFilters();
    if ( !isset($filters['closed']) )
    {
      $filters['closed'] = 'no';
      $this->setFilters($filters);
    }
    return parent::getFilters();
  }
  
  protected function addSortQuery($query)
  {
    if (array(null, null) == ($sort = $this->getSort()))
      return;
 
    if (!in_array(strtolower($sort[1]), array('asc', 'desc')))
      $sort[1] = 'asc';
 
    switch ($sort[0]) {
      case 'contact':
        $sort[0] = 'c.name';
        break;
    }
 
    $query->addOrderBy($sort[0] . ' ' . $sort[1]);
  }  
  
}
