<?php

require_once dirname(__FILE__).'/../lib/payment_methodGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/payment_methodGeneratorHelper.class.php';

/**
 * payment_method actions.
 *
 * @package    e-venement
 * @subpackage payment_method
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class payment_methodActions extends autoPayment_methodActions
{
  public function executeDelPicture(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()->from('Picture p')
      ->where('p.id IN (SELECT e.picture_id FROM PaymentMethod e WHERE e.id = ?)',$request->getParameter('id'))
      ->delete()
      ->execute();
    return sfView::NONE;
  }
  
  public function executeChangeRank(sfWebRequest $request)
  {
    foreach ( array('id', 'smaller_than', 'bigger_than') as $param )
    if ( intval($request->getParameter($param)).'' !== ''.$request->getParameter($param) )
      $request->setParameter($param, 0);
    
    $max = Doctrine::getTable('PaymentMethod')->count();
    
    $q = Doctrine::getTable('PaymentMethod')->createQuery('pm');

    $this->paymentMethods = $q->execute();
    $this->forward404Unless($this->paymentMethods && $this->paymentMethods->count() > 1);
    
    $dom = sfConfig::get('project_internals_users_domain', false);
    
    $before = new PaymentMethodRank;
    $before->rank = 0;
    $after = new PaymentMethodRank;
    $after->rank = $max+1;
    $newRank = 0;
    $update = false;
    
    $methodRanks = array(
      'current' => NULL,
      'before'  => $before,
      'after'   => $after,
    );
    
    foreach ( $this->paymentMethods as $pm )
    {
      if ( $pm->Ranks[0]->rank == 0 ) {
        sfContext::getInstance()->getLogger()->warning($pm->name.' : '.$pm->Ranks[0]->rank);
        $pm->Ranks[0]->rank = $pm->id;
        $pm->Ranks[0]->save();
      }
      switch ( $pm->id ) {
      case $request->getParameter('smaller_than'):
        $methodRanks['after'] = $pm;
        break;
      case $request->getParameter('id'):
        $methodRanks['current'] = $pm;
        break;
      case $request->getParameter('bigger_than'):
        $methodRanks['before'] = $pm;
        break;
      }
    }
            
    $q = Doctrine_Query::create()
        ->from('PaymentMethodRank pmr')
        ->update();

    // If previous method rank > selected method rank, the method went down in the list (the rank has risen)
    if ($methodRanks['before']->rank > $methodRanks['current']->rank) 
    {
        $newRank = $methodRanks['before']->rank;        
        $q->set('rank', 'rank - 1')
          ->where('rank BETWEEN ? AND ?', array($methodRanks['current']->rank, $methodRanks['before']->rank));
        $update = true;
    }
    // If next method rank < selected method rank, the method went up in the list (the rank has lowered)
    if ($methodRanks['after']->rank < $methodRanks['current']->rank) 
    {
        $newRank = $methodRanks['after']->rank;
        $q->set('rank', 'rank + 1')
          ->where('rank BETWEEN ? AND ?', array($methodRanks['after']->rank, $methodRanks['current']->rank));      
        $update = true;     
    }

    if ( $dom && $dom != '.' )
      $q->andWhere('pmr.domain ILIKE ? OR pmr.domain = ?', array('%.'.$dom, $dom));

    if ( $update )
      $q->execute();
    
    $methodRanks['current']->Ranks[0]->rank = $newRank;
    $methodRanks['current']->Ranks[0]->save();
    
    $this->payment_method  = $methodRanks['current'];
    $this->reload = false;
    return 'Success';
  }
  
}
