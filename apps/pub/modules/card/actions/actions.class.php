<?php

/**
 * card actions.
 *
 * @package    symfony
 * @subpackage card
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class cardActions extends sfActions
{
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  public function executeAddPromoCode(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('redirect'));
    
    if ( !$request->getParameter('promo-code', false) )
    {
      $this->getUser()->setFlash('error', __('Special offer not found.'));
      $this->redirect($request->hasParameter('redirect'));
    }
    
    $promo = Doctrine::getTable('MemberCardTypePromoCode')->createQuery('pc')
      ->andWhere('pc.name ILIKE ?', $request->getParameter('promo-code', ''))
      ->andWhere('pc.ends_at > NOW() OR pc.ends_at IS NULL')
      ->andWhere('pc.begins_at < NOW() OR pc.begins_at IS NULL')
      //->leftJoin('pc.MemberCardType mct')
      ->fetchOne();
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    if ( !$promo )
      $this->getUser()->setFlash('error', __('Special offer not found.'));
    else
    {
      $go = true;
      foreach ( $this->getUser()->hasContact() ? $this->getUser()->getContact()->MemberCards : $this->getUser()->getTransaction()->MemberCards as $mc )
      if ( $mc->member_card_type_id == $promo->member_card_type_id && strtotime($mc->expire_at) > time() )
        $go = false;
      
      if ( $go )
      {
        $this->getContext()->getConfiguration()->addMemberCard($this->getUser()->getTransaction(), $promo->member_card_type_id, $request->getParameter('promo-code', NULL));
        $this->getUser()->setFlash('success', __('Enjoy your special offer!'));
      }
      else
        $this->getUser()->setFlash('notice', __('You can already enjoy this special offer.'));
    }
    
    $this->redirect($request->getParameter('redirect'));
  }
  public function executeIndex(sfWebRequest $request)
  {
    //$this->redirectIfNotAuthenticated();
    
    // use pub.php/card?member_card_types[]=ID1&member_card_types[]=ID2 to restrict the displaid member cards
    
    $q = Doctrine::getTable('MemberCardType')->createQuery('mct')
      ->leftJoin('mct.Users u')
      ->leftJoin('mct.Translation translation WITH translation.lang = ?', $this->getUser()->getCulture())
      ->andWhere('u.id = ?',$this->getUser()->getId())
      ->orderBy('translation.description');
    if ( $restrictions = $request->getParameter('member_card_types', array()) )
      $q->andWhereIn('mct.id', $restrictions);
    $this->member_card_types = $q->execute();
    
    $this->mct = array();
    if ( $this->getUser()->getTransaction()->MemberCards->count() > 0 )
    foreach ( $this->getUser()->getTransaction()->MemberCards as $mc )
    {
      if ( !isset($this->mct[$mc->member_card_type_id]) )
        $this->mct[$mc->member_card_type_id] = 0;
      $this->mct[$mc->member_card_type_id]++;
    }
  }
  
  public function executeDel(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $q = Doctrine::getTable('MemberCard')->createQuery('mc')
      ->andWhere('mc.transaction_id = ?', $this->getUser()->getTransactionId())
      ->andWhere('mc.active = ?', false)
      ->andWhere('mc.id = ?', $request->getParameter('id',0))
    ;
    $this->forward404Unless($mcs = $q->execute());
    $mcs->delete();
    $this->getUser()->setFlash('success', __('Your items were successfully removed from your cart.'));
    $this->redirect('store/index');
  }
  
  public function executeOrder(sfWebRequest $request)
  {
    $transaction = $this->getUser()->getTransaction();
    //$this->redirectIfNotAuthenticated();
    
    // empty'ing member cards from transaction
    if ( !$request->hasAttribute('append') )
    {
      foreach ( $transaction->MemberCards as $mc )
      foreach ( $mc->MemberCardPrice as $mcp )
      foreach ( $transaction->Tickets as $i => $ticket )
      if ( $ticket->price_id = $mcp->price_id
        && $ticket->Manifestation->event_id == $mcp->event_id )
      {
        unset($transaction->Tickets[$i]);
        $ticket->delete();
        break;
      }
      $transaction->MemberCards->delete();
    }
    
    $order = $request->getParameter('member_card_type');
    $cpt = 0;
    foreach ( $order as $id => $qty )
    if ( intval($qty) > 0 )
    {
      $cpt += intval($qty);
      if ( $cpt <= sfConfig::get('app_member_cards_max_per_transaction', 3) )
      for ( $i = 0 ; $i < intval($qty) ; $i++ )
        $this->getContext()->getConfiguration()->addMemberCard($transaction, $id);
    }
    
    $this->redirect('cart/show');
  }
  
  protected function isAuthenticated()
  {
    try { return $this->getUser()->getContact(); }
    catch ( liEvenementException $e )
    { return false; }
  }
  protected function redirectIfNotAuthenticated()
  {
    if ( self::isAuthenticated() )
      return true;

    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->getUser()->setFlash('error',__('To order a pass, you need to be authenticated or Create an account'));
    $this->forward('login','index');
    return false;
  }
}
