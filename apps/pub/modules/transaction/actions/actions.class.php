<?php

/**
 * transaction actions.
 *
 * @package    symfony
 * @subpackage transaction
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class transactionActions extends sfActions
{
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }

  public function executeProducts(sfWebRequest $request)
  {
    $request->setParameter('target', 'products');
    $this->executeTickets($request);
  }
  public function executeTickets(sfWebRequest $request)
  {
    $this->forward404If(sfConfig::get('app_tickets_pdf_attachments', false) !== true);
    
    $transaction = Doctrine::getTable('Transaction')->find(intval($request->getParameter('id')));
    $this->setTemplate('tickets');
    
    // targets
    $targets = array(
      'tickets' => 'renderSimplifiedTickets',
      'products' => 'renderSimplifiedProducts',
    );
    if ( !isset($targets[$request->getParameter('type', 'tickets')]) )
      throw new liOnlineSaleException('Unknown target "'.$request->getParameter('type', 'tickets').'", please choose one: '.implode(', ', array_keys($targets)));
    $fct = $targets[$target = $request->getParameter('target', 'tickets')];
    if ( !method_exists($transaction, $fct) )
      throw new liOnlineSaleException('Bad configuration: '.get_class($transaction).' does not have any '.$fct.' method.');
    
    // debugging ?
    if ( sfConfig::get('sf_web_debug', false) && !$request->hasParameter('debug') )
      sfConfig::set('sf_web_debug', false);
    
    // ownership
    if ( $transaction->contact_id !== $this->getUser()->getContact()->id )
      throw new liOnlineSaleException('The delivery of tickets which belongs to someone else is not allowed');
    
    // content, and treatment
    $this->tickets_html = $transaction->$fct(
      array('barcode' => $request->getParameter('format') === 'html' ? 'html' : 'png')
    );
    switch ( $format = $request->getParameter('format', 'pdf') ) {
    case 'pdf':
      // content type
      $this->getResponse()->setContentType('application/pdf');
      if ( !sfConfig::get('sf_web_debug', false) )
        $this->getResponse()->setHttpHeader('Content-Disposition', 'attachment; filename="transaction-'.$transaction->id.'-'.$target.'.pdf"');

      $this->content = $this->getPartial('global/get_tickets_pdf', array('tickets_html' => $this->tickets_html));
      $this->setLayout(false);
      return 'PDF';
    case 'html':
      $this->setLayout(false);
      return 'Success';
    default:
      $this->dispatcher->notify($event = new sfEvent($this, 'pub.transaction_generate_other_format', array(
        'transaction' => $transaction,
        'target'      => $target,
        'format'      => $format,
        'headers'     => NULL,
        'content'     => NULL,
      )));
      foreach ( $event['headers'] as $key => $value )
        $this->getResponse()->setHttpHeader($key, $value);
      return $this->renderText($event['content']);
    }
  }
  
  public function executeSendEmail(sfWebRequest $request)
  {
    require_once(dirname(__FILE__).'/../../cart/actions/actions.class.php');
    if ( intval($request->getParameter('id')).'' !== $request->getParameter('id').'' )
      throw new liOnlineSaleException('Trying to access something without prerequisites.');
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $tokened = md5($request->getParameter('id').'|*|*|'.sfConfig::get('project_eticketting_salt', 'e-venement')) == $request->getParameter('token','');
    
    $this->transaction = Doctrine::getTable('Transaction')->find(intval($request->getParameter('id')));
    if (!( sfConfig::get('sf_web_debug', false)
      || $tokened
      || ( $this->transaction->Order->count() > 0 && $this->transaction->contact_id == $this->getUser()->getTransaction()->contact_id )
    ))
    {
      $this->getUser()->setFlash('error', __($err = 'The state of your order does not allow this action. Please contact us if necessary.'));
      error_log('An error occurred trying to send an email: '.$err);
      $this->redirect('transaction/show?id='.$this->transaction->id);
    }
    
    cartActions::sendConfirmationEmails($this->transaction, $this, $tokened);
    $this->getUser()->setFlash('success', __('Action successful'));
    
    if ( $tokened )
      $this->redirect('transaction/closeWindow');
    else
      $this->redirect('transaction/show?id='.$this->transaction->id);
  }
  public function executeCloseWindow(sfWebRequest $request)
  {
  }
  
  public function executeContacts(sfWebRequest $request)
  {
    return require(__DIR__.'/contacts.php');
  }

  public function executeAjax(sfWebRequest $request) {
      $this->paid = array();
    
      if ( Doctrine::getTable('Payment')->createQuery('p')
        ->andWhere('p.transaction_id = ?', $request->getParameter('id'))
        ->andWhere('p.payment_method_id = ?', sfConfig::get('app_tickets_payment_method_id',''))
        ->count() > 0 ) {
          $this->paid['payment'] = true;
      } else {
        $this->paid['payment'] = false;
      }
      
      return 'Json';
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->hardenIntegrity();
    
    try {
    
    $config = sfConfig::get('app_tickets_vel', array());
    $this->auto_add_id = isset($config['auto_add_one_ticket_for_manifid']) ? $config['auto_add_one_ticket_for_manifid'] : 0;
    
    $this->errors = array();
    
    if ( intval($request->getParameter('id')).'' !== $request->getParameter('id').'' )
      throw new liOnlineSaleException('Trying to access something without prerequisites.');
    
    $this->transaction = Doctrine::getTable('Transaction')->fetchOneById(intval($request->getParameter('id')));
    if ( $this->transaction->id != $this->getUser()->getTransactionId() && $this->transaction->contact_id !== $this->getUser()->getContact()->id )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error', __('You cannot access an order which belongs to someone else'));
      $this->redirect('@homepage');
    }
    
    $this->dispatcher->notify(new sfEvent($this, 'pub.transaction_show', array(
      'transaction' => $this->transaction,
      'user' => $this->getUser(),
    )));
    
    $this->current_transaction = $this->transaction->id === $this->getUser()->getTransaction()->id;
    if ( !$this->current_transaction && $this->transaction->closed )
    {
      $this->transaction->closed = false;
      $this->transaction->save();
    }
    
    // Tickets
    $q = Doctrine_Query::create()->from('Event e')
      ->leftJoin('e.Translation et WITH et.lang = ?',$this->getUser()->getCulture())
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Gauges g')
      ->leftJoin('g.Workspace w')
      ->leftJoin('g.Tickets tck')
      ->leftJoin('tck.Price p')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Order o')
      ->andWhere('t.id = ?',$this->transaction->id)
      ->andWhere('t.type = ?','normal')
      ->andWhere('o.id IS NOT NULL OR tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.transaction_id = ?', $this->transaction->id)
      ->andWhere('tck.cancelling IS NULL')
      ->andWhere('tck.id NOT IN (SELECT tck3.duplicating FROM Ticket tck3 WHERE tck3.duplicating IS NOT NULL)')
      ->andWhere('tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE tck2.cancelling IS NOT NULL)')
      ->andWhere('tck.id IS NOT NULL')
      ->andWhere('tck.price_id IS NOT NULL')
      ->leftJoin('t.HoldTransaction ht')
      ->andWhere('ht.id IS NULL')
      ->orderBy('m.happens_at, et.name, w.name, g.id, p.id, tck.id')
    ;
    if ( $this->getUser()->hasContact() )
      $q->andWhere('t.contact_id = ?',$this->getUser()->getContact()->id);
    else
      $q->andWhere('t.id = ?',$this->getUser()->getTransaction()->id);
    $this->events = $q->execute();
    
    // MemberCards
    $q = Doctrine::getTable('MemberCard')->createQuery('mc')
      ->leftJoin('mc.MemberCardType mct')
      ->andWhere('mc.transaction_id = ?', $this->transaction->id)
      ->orderBy('mc.expire_at, mct.name')
    ;
    if ( $this->getUser()->hasContact() )
      $q->andWhere('mc.contact_id = ? OR mc.transaction_id = ?', array($this->getUser()->getContact()->id, $this->getUser()->getTransaction()->id));
    else
      $q->andWhere('mc.transaction_id = ?', $this->getUser()->getTransaction()->id);
    $this->member_cards = $q->execute();
    
    // Products
    $q = Doctrine::getTable('BoughtProduct')->createQuery('bp')
      ->leftJoin('bp.Transaction t')
      ->andWhere('t.id = ?', $this->transaction->id)
      ->leftJoin('bp.Declination d')
      ->leftJoin('d.Product p')
      ->leftJoin('p.Category c')
      ->leftJoin('c.Translation ct WITH ct.lang = ?', $this->getUser()->getCulture())
      ->orderBy('ct.name, bp.name, bp.declination, bp.value, bp.id')
    ;
    if ( $this->getUser()->hasContact() )
      $q->andWhere('t.contact_id = ?',$this->getUser()->getContact()->id);
    else
      $q->andWhere('t.id = ?',$this->getUser()->getTransaction()->id);
    $this->products = $q->execute();
    
    $this->end = $request->hasParameter('end');
    
    $this->form = new sfForm;
    $widgets = $this->form->getWidgetSchema();
    $widgets->setNameFormat('transaction[%s]');
    $widgets['description'] = new sfWidgetFormTextArea;
    $widgets['description']->setDefault($this->getUser()->getTransaction()->description);
    
    }
    catch ( liOnlineSaleException $e )
    {
      $this->redirect('login/index');
    }
  }
  
  public function executeAddComment(sfWebRequest $request)
  {
    $transaction = Doctrine::getTable('Transaction')->findOneById($request->getParameter('id',0));
    $this->forward404Unless($transaction);
    
    $form = new sfForm;
    $validators = $form->getValidatorSchema();
    $validators['description'] = new sfValidatorString(array('required' => false));
    $values = $request->getParameter('transaction');
    $form->bind($values);
    
    if ( $form->isValid() )
    {
      $transaction->description = $values['description'];
      $transaction->save();
    }
    
    return sfView::NONE;
  }
  
  public function executeInvoice(sfWebRequest $request)
  {
    // faking the sfRouteObject
    $this->transaction = Doctrine::getTable('Transaction')->find($request->getParameter('id', 0));
    if ( $this->transaction->contact_id != $this->getUser()->getContact()->id )
      $this->forward404('No public transaction found for #'.$request->getParameter('id', 0));
    
    // forcing some needed vars
    $printed = true;
    $manifestation_id = 0;
    $no_actions = true;
    
    // forging the request
    if ( !$request->hasParameter('html') )
      $request->setParameter('pdf', 'pdf');
    $request->setParameter('nocancel', 'true');
    
    // ugly hack... but working and easy
    require(dirname(__FILE__).'/accounting.php');
    return require(dirname(__FILE__).'/invoice.php');
  }
}
