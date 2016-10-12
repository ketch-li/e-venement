<?php

require_once dirname(__FILE__).'/../lib/price_manifestationGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/price_manifestationGeneratorHelper.class.php';

/**
 * price_manifestation actions.
 *
 * @package    e-venement
 * @subpackage price_manifestation
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class price_manifestationActions extends autoPrice_manifestationActions
{
  protected $manifid = 0;

  public function executeBatchEdit(sfWebRequest $request)
  {
    if ( intval($this->manifid = $request->getParameter('id')).'' != $request->getParameter('id') )
      throw new sfError404Exception();

    $q = Doctrine::getTable('PriceManifestation')->createQuery('pm')
      ->leftJoin('pm.Price p')
      ->leftJoin("p.Translation pt WITH pt.lang = '".$this->getUser()->getCulture()."'")
      ->where('manifestation_id = ?',$this->manifid)
      ->orderBy('pm.value DESC, pt.name');
    $this->sort = array('value','desc');

    $this->pager = $this->configuration->getPager('PriceManifestation');
    $this->pager->setQuery($q);
    if ( $request->hasParameter('page') )
      $this->setPage($request->getParameter('page'));
    $this->pager->setPage($this->getPage());
    $this->pager->init();

    // let's avoid over numbered page
    if ( $this->getPage() > $this->pager->getLastPage() )
    {
      $this->setPage(1);
      $this->pager->setPage($this->getPage());
      $this->pager->init();
    }

    $this->hasFilters = $this->getUser()->getAttribute('price_manifestation.list_filters', $this->configuration->getFilterDefaults(), 'admin_module');
  }

  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));

    $this->getRoute()->getObject()->delete();

    //$this->getUser()->setFlash('notice', 'The item was deleted successfully.');

    //$this->redirect('@price_manifestation?blank=1');

    return sfView::NONE;
  }

  protected function setPage($page)
  {
    $this->getUser()->setAttribute('price_manifestation.'.$this->manifid.'.page', $page, 'admin_module');
    return $this;
  }

  protected function getPage()
  {
    return $this->getUser()->getAttribute('price_manifestation.'.$this->manifid.'.page', 1, 'admin_module');
  }
}
