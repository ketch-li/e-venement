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

require_once dirname(__FILE__).'/../lib/priceGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/priceGeneratorHelper.class.php';

/**
 * price actions.
 *
 * @package    e-venement
 * @subpackage price
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class priceActions extends autoPriceActions
{
  public function executeChangeRank(sfWebRequest $request)
  {
    foreach ( array('id', 'smaller_than', 'bigger_than') as $param )
    if ( intval($request->getParameter($param)).'' !== ''.$request->getParameter($param) )
      $request->setParameter($param, 0);
    
    $this->prices = Doctrine::getTable('Price')->createQuery('e')
      ->andWhereIn('e.id', array($request->getParameter('id'), $request->getParameter('smaller_than'), $request->getParameter('bigger_than')))
      ->execute();
    $this->forward404Unless($this->prices && $this->prices->count() > 1);
    
    $before = new Price;
    $before->rank = 0;
    $after = new Price;
    $after->rank = 0xFFFFFFFF;
    $newRank = 0;
    
    $prices = array(
      'current' => NULL,
      'before'  => $before,
      'after'   => $after,
    );
    
    foreach ( $this->prices as $price )
    {
        switch ( $price->id ) {
        case $request->getParameter('smaller_than'):
          $prices['after'] = $price;
          break;
        case $request->getParameter('id'):
          $prices['current'] = $price;
          break;
        case $request->getParameter('bigger_than'):
          $prices['before'] = $price;
          break;
        }
    }
    
    $q = Doctrine_Query::create()
        ->from('Price p')
        ->update();

    // Id previous price rank > selected price rank, the price went down in the list (the rank has risen)
    if ($prices['before']->rank > $prices['current']->rank) 
    {
        $newRank = $prices['before']->rank;        
        $q->set('rank', 'rank - 1')
          ->where('rank BETWEEN ? AND ?', array($prices['current']->rank, $prices['before']->rank));
    }
    // If next price rank < selected price rank, the price went up in the list (the rank has lowered)
    if ($prices['after']->rank < $prices['current']->rank) 
    {
        $newRank = $prices['after']->rank;
        $q->set('rank', 'rank + 1')
          ->where('rank BETWEEN ? AND ?', array($prices['after']->rank, $prices['current']->rank));           
    }

    $q->execute();
    
    $prices['current']->rank = $newRank;
    $prices['current']->save();
    
    $this->price  = $prices['current'];
    $this->reload = false;
    return 'Success';
  }
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],strtolower($request->getParameter('q')));
    $this->json = array();
    
    if ( !$search )
      return 'Json';
    
    if (!( $max = $request->getParameter('max',sfConfig::get('app_manifestations_max_ajax')) ))
      $max = 10;
    
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->andWhere('pt.lang = ?', $this->getUser()->getCulture())
      ->andWhere('pt.name ILIKE ? OR pt.description ILIKE ?', array("%$search%", "%$search%"))
      ->orderBy('pt.name, pt.description')
      ->limit($request->getParameter('limit',$max));
    
    foreach ( $q->execute() as $price )
    {
      $this->getContext()->getConfiguration()->loadHelpers('Url');
      $this->json[$price->id] = $price->name.($price->description ? ' ('.$price->description.')' : '');
    }
    
    if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) )
      return 'Success';
    else
      return 'Json';
  }
  
  protected function getSort()
  {
    return array('rank', 'ASC');
  }
}
