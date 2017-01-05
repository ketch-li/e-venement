<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/productGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/productGeneratorHelper.class.php';

/**
 * product actions.
 *
 * @package    e-venement
 * @subpackage product
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class productActions extends autoProductActions
{
  public function executeState(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');

    $q = Doctrine::getTable('Product')->createQuery('p')
      ->andWhere('p.id = ?', $request->getParameter('id'))
      ->orderBy('d.code');
    $this->forward404Unless($pdt = $q->fetchOne());

    $this->json = $pdt->getStocksData(array(
      'critical'  => __('Critical'),
      'correct'   => __('Correct'),
      'perfect'   => __('Good'),
    ));

    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
      return 'Success';
    return 'Json';
  }

  public function executeDeclinationsTrends(sfWebRequest $request)
  {
    $q = Doctrine::getTable('ProductDeclination')->createQuery('d')
      ->leftJoin('d.Translation dt WITH dt.lang = ?', $this->getUser()->getCulture())
      ->leftJoin('d.Product p')
      ->leftJoin('p.Translation pt WITH pt.lang = dt.lang')
      ->select('d.id, d.code, dt.id, dt.name, dt.lang, d.product_id, p.id, pt.id, pt.name, pt.lang')
      ->leftJoin('d.BoughtProducts bp WITH bp.integrated_at IS NOT NULL')
      ->addSelect('count(DISTINCT bp.id) AS sales')
      ->groupBy('d.id, d.code, dt.id, dt.name, dt.lang, d.product_id, p.id, pt.id, pt.name, pt.lang')
      ->andWhere('d.product_id = ?', $request->getParameter('id'));
    $this->forward404Unless($declinations = $q->execute());

    $this->json = array();
    foreach ( $declinations as $declination )
    if ( $request->hasParameter('full') )
    {
      $this->json[] = array(
        'id'    => $declination->id,
        'code'  => $declination->code,
        'name'  => $declination->name,
        'product' => (string)$declination->Product,
        'product_id' => $declination->product_id,
        'quantity' => $declination->sales,
      );
    }
    else
      $this->json[] = array($declination->name, $declination->sales);

    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
      return 'Success';
    return 'Json';
  }

  public function executeSalesTrends(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->json = array('all' => array(
      'id' => 0,
      'code' => 'all',
      'name' => __('Global'),
      'dates' => array(),
    ));
    for ( $i = 365 ; $i >= 0 ; $i-- )
      $this->json['all']['dates'][date('Y-m-d', strtotime($i.' days ago'))] = 0;

    $q = Doctrine::getTable('Product')->createQuery('p')
      ->leftJoin('p.Declinations pd')
      ->leftJoin('pd.BoughtProducts bp')
      ->andWhere('bp.integrated_at IS NOT NULL')
      ->andWhere('bp.integrated_at > ?', date('Y-m-d', strtotime('1 year ago')))
      ->andWhere('p.id = ?', $request->getParameter('id'))
    ;

    $this->forward404Unless($pdt = $q->fetchOne());
    foreach ( $pdt->Declinations as $declination )
    foreach ( $declination->BoughtProducts as $bp )
    {
      $date = date('Y-m-d', strtotime($bp->integrated_at));
      if ( !isset($this->json[$bp->product_declination_id]) )
        $this->json[$bp->product_declination_id] = array(
          'id'    => $bp->product_declination_id,
          'code'  => $bp->Declination->code,
          'name'  => $bp->Declination->name,
          'dates' => array(),
        );
      if ( count($this->json[$bp->product_declination_id]['dates']) == 0 )
      for ( $i = 365 ; $i >= 0 ; $i-- )
        $this->json[$bp->product_declination_id]['dates'][date('Y-m-d', strtotime($i.' days ago'))] = 0;
      if ( !isset($this->json[$bp->product_declination_id]['dates'][$date]) )
        $this->json[$bp->product_declination_id]['dates'][$date] = 0;
      if ( !isset($this->json['all']['dates'][$date]) )
        $this->json['all']['dates'][$date] = 0;

      $this->json[$bp->product_declination_id]['dates'][$date]++;
      $this->json['all']['dates'][$date]++;
    }

    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
      return 'Success';
    return 'Json';
  }
  public function executeDuplicate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->executeEdit($request);

    $copy = $this->product->copy();
    $copy->slug = NULL;
    foreach ( array('Translation', 'Declinations', 'PriceProducts',) as $cols )
    foreach ( $this->product->$cols as $col )
    {
      $ccol = $col->copy();

      if ( $col->getTable()->hasColumn('code') )
        $ccol->code = NULL;
      if ( $col->getTable()->hasColumn('name') )
        $ccol->name = $ccol->name.' ('.__('Copy').')';

      if ( $col->getTable()->hasRelation('Translation') )
      foreach ( $col->Translation as $i18n )
        $ccol->Translation[] = $i18n->copy();

      $copy->{$cols}[] = $ccol;
    }

    // links
    foreach ( array('LinkedManifestations', 'LinkedPrices', 'LinkedWorkspaces', 'LinkedMetaEvents') as $cols )
    foreach ( $this->product->$cols as $col )
      $copy->{$cols}[] = $col;

    $copy->save();
    $this->redirect('product/edit?id='.$copy->id);
  }
  public function executeAddDeclination(sfWebRequest $request)
  {
    $this->redirect('declination/new?product-id='.$request->getParameter('id'));
  }
  public function executeDelPicture(sfWebRequest $request)
  {
    Doctrine::getTable('Product')->find($request->getParameter('id', 0))->Picture->delete();
    return sfView::NONE;
  }
  public function executeDelDeclination(sfWebRequest $request)
  {
    Doctrine::getTable('ProductDeclination')->find($request->getParameter('declination_id', 0))->delete();
    return sfView::NONE;
  }

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
    $search  = iconv($charset['db'],$charset['ascii'],strtolower($request->getParameter('q')));

    $q = Doctrine::getTable('Product')->createQuery('pdt')
      ->limit($request->getParameter('limit', $request->getParameter('max', 10)))
      ->leftJoin('pdt.MetaEvent me')
      ->andWhereIn('me.id IS NULL OR me.id', array_keys($this->getUser()->getMetaEventsCredentials()))
      ->andWhere('pt.lang = ?', $this->getUser()->getCulture())
      ->orderBy('pt.name')
    ;
    if ( ($tid = intval($request->getParameter('except_transaction', false))).'' === ''.$request->getParameter('except_transaction', false) )
    if ( $request->getParameter('all', false) !== 'true' )
      $q->andWhere('pdt.id NOT IN (SELECT bpd.product_id FROM BoughtProduct bp LEFT JOIN bp.Declination bpd WHERE bp.transaction_id = ? AND bp.product_declination_id IS NOT NULL)',$tid);

    // huge hack to look for declinations' codes AND product_index
    $q->andWhere('(TRUE')
      ->andWhere('d.code ILIKE ?', $request->getParameter('q').'%')
      ->orWhere('TRUE');
    $q = Doctrine_Core::getTable('Product')
      ->search($search.'*',$q);
    $q->andWhere('TRUE)');

    $this->getContext()->getConfiguration()->loadHelpers('Url');
    $this->products = array();
    foreach ( $q->execute() as $product )
    if ( $product->isAccessibleBy($this->getUser()) )
    if ( $request->hasParameter('keep-order') )
    {
      $this->products[] = array(
        'id'    => $product->id,
        'color' => (string)$product->Category->Color,
        'name'  => (string)$product,
        'gauge_url' => url_for('product/state?id='.$product->id),
      );
    }
    else
      $this->products[$product->id] = $request->hasParameter('with_colors')
        ? array('name' => (string)$product, 'color' => (string)$product->Category->Color,)
        : (string) $product;
  }

  public function executeShow(sfWebRequest $request)
  {
    $this->getResponse()->addJavascript('pos-ro');
    $this->forward('product', 'edit');
  }
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);

    if ( !$this->getUser()->hasCredential('pos-product-edit') )
      $this->getResponse()->addJavascript('pos-ro');
  }

  public function executeCsv(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('Number','Date'));
    $this->executeIndex($request);

    $query = $this->pager->getQuery()
      ->leftJoin('r.MetaEvent mev')
      ->leftJoin('r.Vat vat')
      ->leftJoin('r.Category cat')
      ->leftJoin('r.Prices pri')
      ->removeDqlQueryPart('limit')
      ->removeDqlQueryPart('offset')
    ;
    $products = $query->execute();

    $breaks = array("<br />","<br>","<br/>");
    $currency = $this->getContext()->getConfiguration()->getCurrency();

    $this->lines = array();
    foreach ( $products as $p ) {
      $prices = array();
      foreach ( $p->Prices as $price )
        $prices[] = format_currency($price->value, $currency);
      foreach ( $p->Declinations as $d ) {
        $p_description = html_entity_decode(strip_tags(str_ireplace($breaks, "\r\n", $p->description)));
        $d_description = html_entity_decode(strip_tags(str_ireplace($breaks, "\r\n", $d->description)));
        $this->lines[] = array(
          'p_name'         => $p->name,
          'short_name'     => $p->short_name,
          'p_description'  => $p_description,
          'category'       => (string)$p->Category,
          'meta_event'     => (string)$p->MetaEvent,
          'prices'          => implode(' / ', $prices),
          'vat'            => (string)$p->Vat,
          'online'         => $p->online ? __("yes") : __("no"),
          'online_limit'   => $p->online_limit,
          't_online_limit' => $p->online_limit_per_transaction,
          'd_name'         => (string)$d,
          'd_description'  => $d_description,
          'code'           => $d->code,
          'prioritary'     => $d->prioritary ? __("yes") : __("no"),
          'weight'         => $d->weight,
          'use_stock'      => $d->use_stock ? __("yes") : __("no"),
          'stock'          => $d->stock,
          'stock_perfect'  => $d->stock_perfect,
          'stock_critical' => $d->stock_critical,
        );
      }
    }

    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'tunnel' => false,
      'noheader' => false,
      'fields'   => array(
        'p_name',
        'short_name',
        'p_description',
        'category',
        'meta_event',
        'prices',
        'vat',
        'online',
        'online_limit',
        't_online_limit',
        'd_name',
        'd_description',
        'code',
        'prioritary',
        'weight',
        'use_stock',
        'stock',
        'stock_perfect',
        'stock_critical',
      ),
    );

    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset = sfConfig::get('software_internals_charset');

    sfConfig::set('sf_escaping_strategy', false);
    $confcsv = sfConfig::get('software_internals_csv'); if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);

    if ( $this->getContext()->getConfiguration()->getEnvironment() == 'dev' && $request->hasParameter('debug') )
    {
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout('layout');
    }
    else
      sfConfig::set('sf_web_debug', false);
  }

  public function executeSearch(sfWebRequest $request)
  {
    self::executeIndex($request);
    $table = Doctrine::getTable('Product');

    $query = $table->createQuery()
        ->andWhere('d.code = ?', $request->getParameter('s'));

    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();

    $this->setTemplate('index');
  }
}
