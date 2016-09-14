<?php

/**
 * social actions.
 *
 * @package    e-venement
 * @subpackage social
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class socialActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    if ( $request->hasParameter('criterias') )
    {
      $this->criterias = $request->getParameter('criterias');
      $this->setCriterias($this->criterias);
      $this->redirect($this->getContext()->getModuleName().'/index');
    }
    
    $this->form = new StatsCriteriasForm();
    $this->form
      ->addGroupsCriteria()
      ->removeDatesCriteria()
      ->addStrictContactsCriteria()
    ;
    if ( is_array($this->getCriterias()) )
      $this->form->bind($this->getCriterias());
  }
  
  protected function getCriterias()
  {
    return $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
  }
  protected function setCriterias($values)
  {
    $this->getUser()->setAttribute('stats.criterias',$values,'admin_module');
    return $this;
  }

  public function executeJson(sfWebRequest $request)
  {
    $this->lines = $this->getData($request->getParameter('id'), 'array');
    
    if ( !$request->hasParameter('debug') )
    {
      $this->setLayout('raw');
      sfConfig::set('sf_debug',false);
      $this->getResponse()->setContentType('application/json');
    }
  }
  
  protected function getData($id, $type = NULL)
  {
    switch ( $id ) {
    case 'fs':
      $table = 'FamilialSituation';
      break;
    case 'fq':
      $table = 'FamilialQuotient';
      break;
    case 'tor':
      $table = 'TypeOfResources';
      break;
    default:
      throw new liEvenementException("You forgot to specify what kind of data you are expecting or you requested something not implemented.");
    }
    
    $q = Doctrine_Query::create()->from($table.' t')
      ->select('t.id, t.name')
      ->leftJoin('t.Contacts c')
      ->leftJoin('c.Groups g')
      ->groupBy('t.id, t.name')
      ->orderBy('t.name');
    
    $criterias = $this->getCriterias();
    if ( isset($criterias['groups_list']) && $criterias['groups_list'] )
      $q->andWhereIn('g.id',$criterias['groups_list']);
    
    if ( isset($criterias['strict_contacts']) && $criterias['strict_contacts'] )
      $q->addSelect('count(DISTINCT (c.id)) AS nb');
    else
      $q->leftJoin('c.YOBs y')
        ->addSelect('count(DISTINCT (c.id, y.id)) AS nb');
    
    return $type == 'array' ? $q->fetchArray() : $q->execute();
  }
}
