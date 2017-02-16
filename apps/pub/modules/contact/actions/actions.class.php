<?php

/**
 * contact actions.
 *
 * @package    symfony
 * @subpackage contact
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class contactActions extends sfActions
{
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  
  public function executeNewPicture(sfWebRequest $request)
  {
    $this->forward404Unless(intval($request->getParameter('id',0)) > 0);
    $this->forward404Unless($this->contact = Doctrine::getTable('Contact')->find($request->getParameter('id')));
    if ( $request->getParameter('image', false) )
    {
      if ( !$this->contact->Picture->isNew() )
        $this->contact->Picture->delete();
      $this->contact->Picture = new Picture;

      $this->contact->Picture->content = $request->getParameter('image');
      $this->contact->Picture->name = 'contact-'.$this->contact->id.'-'.date('YmdHis').'.img';
      $this->contact->Picture->type = $request->getParameter('type');
      $this->contact->save();
    }
    return sfView::NONE;
  }
  
  public function executeNew(sfWebRequest $request)
  {
    $this->executeEdit($request);
    $this->setTemplate('edit');
  }
  
  public function executeUpdate(sfWebRequest $request)
  {
    // creating form
    try {
      $this->form = new ContactPublicForm($this->getUser()->getContact());
      $vs = $this->form->getValidatorSchema();
      foreach ( array('password', 'password_again') as $pass )
      if ( isset($vs[$pass]) )
        $vs[$pass]->setOption('required', false);
    }
    catch ( liEvenementException $e )
    { $this->form = new ContactPublicForm; }
    
    // validating and saving form
    $this->form->bind($request->getParameter('contact'));
    if ( $this->form->isValid() )
    {
      $this->form->save();
      
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('notice',__('Contact updated'));
      
      try { $this->getUser()->getContact(); }
      catch ( liEvenementException $e )
      { $this->getUser()->setContact($this->form->getObject()); }

      if ( sfConfig::get('app_contact_organism', array())['enable'] && $this->getUser()->getContact()->Professionals->Count() > 0 ) {
        $this->getUser()->getTransaction()->Professional = $this->getUser()->getContact()->Professionals[0];
        $this->getUser()->getTransaction()->save();
      }
      
      if ( sfConfig::get('app_contact_modify_coordinates_first', false) )
        $this->redirect(sfConfig::get('app_options_home', 'event'));
      elseif ( $request->hasParameter('openidconnect') )
      {
        $provider = new liOnlineExternalAuthOpenIDConnect;
        $this->redirect($provider->getConfig('issuer'));
      }
      else
        $this->redirect('contact/index');
    }
    
    $this->setTemplate('edit');
    return 'Success';
  }
  public function executeEdit(sfWebRequest $request)
  {
    // redirect to the identification provider
/*
    if ( in_array('liOnlineExternalAuthOpenIDConnectPlugin', $this->getContext()->getConfiguration()->getPlugins()) )
    {
      $provider = new liOnlineExternalAuthOpenIDConnect;
      $this->redirect($provider->getConfig('issuer'));
    }
*/
    
    try {
      $this->form = new ContactPublicForm($this->getUser()->getContact());
      
      $pns = array();
      foreach ( $this->getUser()->getContact()->Phonenumbers as $pn )
        $pns[$pn->updated_at.' '.$pn->id] = $pn;
      ksort($pns);
      
      $pn = array_pop($pns);
      $this->form->setDefault('phone_type',$pn->name);
      $this->form->setDefault('phone_number',$pn->number);
      
      $this->contact = $this->getUser()->getContact();
    }
    catch ( liEvenementException $e )
    { $this->form = new ContactPublicForm; }
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    try { $this->form = new ContactPublicForm($this->getUser()->getContact()); }
    catch ( liEvenementException $e )
    { $this->redirect('contact/new'); }
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m', true)
      ->leftJoin('m.Tickets tck')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Order order')
      ->andWhere('order.id IS NOT NULL OR tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL')
      ->andWhere('t.contact_id = ?',$this->getUser()->getContact()->id)
      ->andWhere('tck.price_id IS NOT NULL')
      ->leftJoin('t.HoldTransaction ht')
      ->andWhere('ht.id IS NULL')
      ->orderBy('m.happens_at DESC')
    ;
    $this->manifestations = $q->execute();
    
    $this->products = Doctrine::getTable('BoughtProduct')->createQuery('bp')
      ->leftJoin('bp.Transaction t')
      ->leftJoin('t.Order order')
      ->andWhere('order.id IS NOT NULL OR bp.integrated_at IS NOT NULL')
      ->andWhere('t.contact_id = ?',$this->getUser()->getContact()->id)
      ->orderBy("bp.description_for_buyers IS NOT NULL AND bp.description_for_buyers != '' DESC, bp.integrated_at DESC")
      ->execute();
    
    $this->member_cards = Doctrine::getTable('MemberCard')->createQuery('mc')
      ->leftJoin('mc.Transaction t')
      ->andWhere('t.id = ? OR mc.active = ?', array($this->getUser()->getTransactionId(), true))
      ->andWhere('mc.contact_id = ?',$this->getUser()->getContact()->id)
      ->leftJoin('mc.MemberCardType mct')
      ->orderBy("mc.expire_at DESC, mc.created_at, mct.name")
      ->execute();      
    
    $this->active_member_cards = array();
    $this->used_member_cards = array();
    
    foreach ($this->member_cards as $mc)
        if ( strtotime($mc->expire_at) > strtotime('now') && $mc->active )
            $this->active_member_cards[] = $mc;
        else
            $this->used_member_cards[] = $mc;
    
    $this->contact = $this->getUser()->getContact();
  }  
  
  public function executeDel(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
  
    $this->getUser()->getContact()->Professionals[0]->delete();
    $this->getUser()->getTransaction()->professional_id = null;
    $this->getUser()->getTransaction()->save();
    
    $this->getUser()->setFlash('success', __('Link to organism successfully deleted.'));
    $this->redirect('contact/edit');
  }
  
  public function executeAjax(sfWebRequest $request) 
  {
    $this->mc = Doctrine::getTable('MemberCard')->find(intval($request->getParameter('membercard_id')));
    return 'Success';    
  }
}
