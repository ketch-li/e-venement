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


/**
 * price actions.
 *
 * @package    e-venement
 * @subpackage price
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class pos_priceActions extends autoPos_priceActions
{
  public function executeChangeRank(sfWebRequest $request)
  {
    foreach ( array('id', 'smaller_than', 'bigger_than') as $param )
    if ( intval($request->getParameter($param)).'' !== ''.$request->getParameter($param) )
      $request->setParameter($param, 0);
    
    $max = Doctrine::getTable('Price')->count();
    
    $this->prices = Doctrine::getTable('Price')->createQuery('e')
      ->andWhereIn('e.id', array($request->getParameter('id'), $request->getParameter('smaller_than'), $request->getParameter('bigger_than')))
      ->execute();
    $this->forward404Unless($this->prices && $this->prices->count() > 1);
    
    $dom = sfConfig::get('project_internals_users_domain', false);
    
    $before = new PriceRank;
    $before->rank = 0;
    $after = new PriceRank;
    $after->rank = $max+1;
    $newRank = 0;
    $update = false;
    
    $prices = array(
      'current' => NULL,
      'before'  => $before,
      'after'   => $after,
    );
    
    foreach ( $this->prices as $price )
    {
        if ( $price->Ranks[0]->rank == 0 ) {
          $price->Ranks[0]->rank = $price->id;
          $price->Ranks[0]->save();
        }
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
        ->from('PriceRank pr')
        ->update();

    // Id previous price rank > selected price rank, the price went down in the list (the rank has risen)
    if ($prices['before']->rank > $prices['current']->rank) 
    {
        $newRank = $prices['before']->rank;        
        $q->set('rank', 'rank - 1')
          ->where('rank BETWEEN ? AND ?', array($prices['current']->rank, $prices['before']->rank));
        $update = true;
    }
    // If next price rank < selected price rank, the price went up in the list (the rank has lowered)
    if ($prices['after']->rank < $prices['current']->rank) 
    {
        $newRank = $prices['after']->rank;
        $q->set('rank', 'rank + 1')
          ->where('rank BETWEEN ? AND ?', array($prices['after']->rank, $prices['current']->rank));
          $update = true;
    }

    if ( $dom && $dom != '.' )
      $q->andWhere('pr.domain ILIKE ? OR pr.domain = ?', array('%.'.$dom, $dom));

    if ( $update )
      $q->execute();
    
    $prices['current']->Ranks[0]->rank = $newRank;
    $prices['current']->Ranks[0]->save();
    
    $this->price  = $prices['current'];
    $this->reload = false;
    return 'Success';
  }
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],strtolower($request->getParameter('q')));
    $transliterate = sfConfig::get('software_internals_transliterate');

    $this->json = array();
    
    if ( !$search )
      return 'Json';
    
    if (!( $max = $request->getParameter('max',sfConfig::get('app_manifestations_max_ajax')) ))
      $max = 10;
    
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->andWhere('pt.lang = ?', $this->getUser()->getCulture())
      ->andWhere(str_replace(array('from', 'to'), array($transliterate['from'], $transliterate['to']), "Translate(pt.name, 'from', 'to') ILIKE ? OR Translate(pt.description, 'from', 'to') ILIKE ?"), array("%$search%", "%$search%"))
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
