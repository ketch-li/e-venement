<?php

trait actionEvent
{
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->getUser()->setDefaultCulture($request->getLanguages());
    parent::executeIndex($request);
    
    // focusing on one meta event
    if ( $request->getParameter('meta-event', false) )
    {
      if ( is_array($request->getParameter('meta-event')) )
        $this->pager->getQuery()
          ->andWhereIn('me.slug', $request->getParameter('meta-event'));
      else
      $this->pager->getQuery()
        ->andWhere('me.slug = ?', $request->getParameter('meta-event'));
      sfConfig::set('pub.meta_event.slug', $request->getParameter('meta-event'));
    }
    
    // if there is only one event...
    if ( $this->pager->getQuery()->count() == 1 && !$request->hasParameter('debug') )
    {
      foreach ( array('success', 'notice', 'error') as $type )
      if ( $this->getUser()->getFlash($type) )
        $this->getUser()->setFlash($type, $this->getUser()->getFlash($type));
      
      $this->getUser()->getAttributeHolder()->remove('manifestation.filters');
      $this->getUser()->setAttribute('manifestation.filters', array('event_id' => $this->pager->getCurrent()->id), 'admin_module');
      $this->setFilters(array());
      $this->redirect('manifestation/index?id='.$this->pager->getCurrent()->id);
    }
    else
      $this->setFilters(array());
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->event = $this->getRoute()->getObject();
    $this->getUser()->getAttributeHolder()->remove('manifestation.filters');
    $this->getUser()->setAttribute('manifestation.filters', array('event_id' => $this->event->id), 'admin_module');
    
    foreach ( array('success', 'notice', 'error') as $type )
    if ( $this->getUser()->getFlash($type) )
      $this->getUser()->setFlash($type, $this->getUser()->getFlash($type));
    
    $this->redirect('manifestation/index');
  }
  public function executeBatchDelete(sfWebRequest $request)
  {
    $this->redirect('event/index?meta-event='.sfConfig::get('pub.meta_event.slug',''));
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->executeBatchDelete($request);
  }
  public function executeNew(sfWebRequest $request)
  {
    $this->executeBatchDelete($request);
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->executeEdit($request);
  }
  protected function getFilters()
  {
    return $this->getUser()->getAttribute('event.filters', $this->configuration->getFilterDefaults(), 'pub_module');
  }

  protected function setFilters(array $filters)
  {
    return $this->getUser()->setAttribute('event.filters', $filters, 'pub_module');
  }
}
