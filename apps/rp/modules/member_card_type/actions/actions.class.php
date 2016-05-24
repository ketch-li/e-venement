<?php

require_once dirname(__FILE__).'/../lib/member_card_typeGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/member_card_typeGeneratorHelper.class.php';

/**
 * member_card_type actions.
 *
 * @package    symfony
 * @subpackage member_card_type
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class member_card_typeActions extends autoMember_card_typeActions
{
  public function executeAddPromoCode(sfWebRequest $request)
  {
    $this->redirect('promo_code/new?mct_id='.$request->getParameter('id'));
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->redirect('member_card_type/edit?id='.$request->getParameter('id'));
  }
  
  public function executeClean(sfWebRequest $request)
  {
    $q = Doctrine::getTable('MemberCardType')->createQuery('mct')
      ->leftJoin('mct.MemberCards mc WITH mc.expire_at > ?', date('Y-m-d'))
      ->leftJoin('mc.Tickets tck WITH tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL')
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('mct.MemberCardPriceModels mcpm')
      ->andWhere('mct.id = ?', $request->getParameter('id'));
    $this->forward404Unless($this->member_card_type = $q->fetchOne());
    
    $mcps = Doctrine::getTable('MemberCardPrice')->createQuery('mcp')
      ->andWhere('mcp.member_card_id IN (SELECT mc.id FROM MemberCard mc WHERE mc.member_card_type_id = ? AND mc.expire_at > ?)', array($this->member_card_type->id, date('Y-m-d')))
      ->delete()
      ->execute();
    ;
    
    foreach ( $this->member_card_type->MemberCards as $mc )
    {
      $tickets = new Doctrine_Collection('Ticket');
      $tickets->merge($mc->Tickets);
      foreach ( $this->member_card_type->MemberCardPriceModels as $mcpm )
      for ( $i = $mcpm->quantity ; $i > 0 ; $i-- )
      {
        $stop = false;
        foreach ( $tickets as $key => $ticket )
        if ( $mcpm->price_id == $ticket->price_id
          && $mcpm->event_id == $ticket->Manifestation->event_id )
        {
          $stop = true;
          unset($tickets[$key]);
          break;
        }
        if ( !$stop )
        {
          $mcp = new MemberCardPrice;
          $mcp->price_id = $mcpm->price_id;
          $mcp->event_id = $mcpm->event_id;
          $mc->MemberCardPrices[] = $mcp;
        }
      }
    }
    $this->member_card_type->MemberCards->save();
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->getUser()->setFlash('notice', __('%%nb%% member card(s) have been cleaned up', array('%%nb%%' => $this->member_card_type->MemberCards->count())));
    $this->redirect('member_card_type/edit?id='.$this->member_card_type->id);
  }
}
