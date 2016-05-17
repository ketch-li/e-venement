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

require_once dirname(__FILE__).'/../lib/groupGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/groupGeneratorHelper.class.php';

/**
 * group actions.
 *
 * @package    e-venement
 * @subpackage group
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class groupActions extends autoGroupActions
{
  public function executeBatchMerge(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');
    if ( count($ids) < 2 )
      $this->redirect('group/show?id='.$ids[0]);
    
    $q = Doctrine::getTable('Group')->createQuery('g')
      ->andWhereIn('g.id',$ids)
      ->select('g.*')
    ;
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $group = new Group;
    $group->name = __('Search group').' - '.date('Y-m-d H:i:s');
    $group->save();
    
    foreach ( $q->execute() as $grp )
    foreach ( array(
      'ContactGroups' => array('contact_id', 'GroupContact'),
      'ProfessionalGroups' => array('professional_id', 'GroupProfessional'),
      'OrganismGroups' => array('organism_id', 'GroupOrganism'),
    ) as $collection => $relation )
    {
      foreach ( $grp->$collection as $object )
      if ( !isset($group->{$collection}[$object->{$relation[0]}]) )
      {
        $group->{$collection}[$object->{$relation[0]}] = $object;
        $rel = new $relation[1];
        $rel->group_id = $group->id;
        $rel->{$relation[0]} = $object->{$relation[0]};
        $rel->save();
      }
    }
    
    $this->redirect('group/show?id='.$group->id);
  }
  
  public function executeAddMemberCards(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $this->executeEdit($request);
    $q = Doctrine::getTable('MemberCardType')->createQuery('mct')
      ->leftJoin('mct.Users u')
      ->andWhere('u.id = ?', $this->getUser()->getId())
      ->orderBy('mct.name');
    if ( $mctids = $request->getParameter('member_card_types',array()) )
      $q->andWhereIn('mct.id', $mctids);
    $this->member_card_types = $q->execute();
    
    $form = new sfForm;
    $this->csrf_token = $form->getCSRFToken();
    
    if ( $mctids && $this->member_card_types instanceof Doctrine_Collection && $this->member_card_types->count() > 0
      && $this->csrf_token == $request->getParameter('_csrf_token') )
    {
      $cpt = 0;
      $expire_at = sfConfig::has('project_cards_expiration_delay')
        ? date('Y-m-d H:i:s',strtotime(sfConfig::get('project_cards_expiration_delay')))
        : (strtotime(date('Y').'-'.sfConfig::get('project_cards_expiration_date')) > strtotime('now')
          ? date('Y').'-'.sfConfig::get('project_cards_expiration_date')
          : (date('Y')+1).'-'.sfConfig::get('project_cards_expiration_date')
        )
      ;
      
      foreach ( $this->member_card_types as $mct )
      {
        $cids = array();
        foreach ( $this->group->Contacts as $contact )
        {
          $cids[] = $contact->id;
          $mc = new MemberCard;
          $mc->expire_at = $expire_at;
          $mc->MemberCardType = $mct;
          $mc->Contact = $contact;
          $mc->save();
          $cpt++;
        }
        
        $pros = new Doctrine_Collection('Professional');
        $pros->merge($this->group->Professionals);
        foreach ( $this->group->Organisms as $org ) // taking into account also the prefered professionals of organisms
        if ( $org->professional_id )
          $pros[] = $org->CloseContact;
        elseif ( $org->Professionals->count() == 1 )
          $pros[] = $org->Professionals[0];
        // pro by pro, including issued from organisms members
        foreach ( $pros as $pro )
        if ( !in_array($pro->contact_id, $cids) )
        {
          $cids[] = $pro->contact_id;
          $mc = new MemberCard;
          $mc->expire_at = $expire_at;
          $mc->MemberCardType = $mct;
          $mc->contact_id = $pro->contact_id;
          $mc->save();
          $cpt++;
        }
      }
      
      $this->getUser()->setFlash('notice', format_number_choice('[0,1]%%cpt%% member card has been created|(1,+Inf]%%cpt%% member cards have been created', array('%%cpt%%' => $cpt), $cpt));
      $this->redirect('group/show?id='.$this->group->id);
    }
    elseif ( $request->hasParameter('submit') )
      $this->getUser()->setFlash('error', __('Please select a type of member card to assign'));
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $transliterate = sfConfig::get('software_internals_transliterate');
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q',''));
    
    $q = Doctrine::getTable('Group')
      ->createQuery('g')
      ->select('g.*, u.*')
      ->orderby('name', '')
      ->limit($request->getParameter('limit'));
    if ( trim($search) )
      $q->andWhere(sprintf("translate(g.name, '%s', '%s') ILIKE ?", $transliterate['from'], $transliterate['to']), '%'.$search.'%');
    
    $this->groups = array();
    foreach ( $q->execute() as $group )
      $this->groups[$group->id] = (string)$group;
    
    if (!( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') ))
      return 'Json';
    else
      $this->setLayout('nude');
  }
  
  public function executeMember(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->executeEdit($request);
    
    /*
    if ( $this->form->getCSRFToken() !== $request->getParameter('_csrf_token') )
      throw new liEvenementException('CSRF Attack detected: '.$this->form->getCSRFToken().' - '.$request->getParameter('_csrf_token'));
    */
    
    $r = array();
    
    //try
    {
      // is the asked model is supported
      $relations = array('contact' => 'ContactGroups', 'organism' => 'OrganismGroups', 'professional' => 'ProfessionalGroups');
      $validator = new sfValidatorChoice(array(
        'choices' => array_keys($relations),
      ), array('required' => 'Required.', 'invalid' => sprintf('Unsupported model %s.',$request->getParameter('type','unknown'))) );
      $type = $validator->clean($request->getParameter('type'));
      
      // is the asked action is supported
      $validator = new sfValidatorChoice(array(
        'choices' => array('remove', 'add'),
      ),array('required' => 'Required.', 'invalid' => sprintf('Unsupported modifier %s.',$request->getParameter('modifier','unknown'))) );
      $modifier = $validator->clean($request->getParameter('modifier'));
      
      // tweaking the error messages
      $invalid = array(
        'remove' => __('Invalid or impossible to remove from this groupe because not a part of.'),
        'add' => __('Invalid or impossible to add to this group because already a part of.'),
      );
      
      $q = Doctrine_Query::create()->from(ucfirst($type).' o')
        ->leftJoin(sprintf('o.%s og WITH og.group_id = ?', $relation = $relations[$type]), $this->form->getObject()->id)
      ;
      
      // validating the current targetted object
      $validator = new sfValidatorDoctrineChoice(array(
        'model' => ucfirst($type),
        'required' => true,
        'query' => $q->copy()->select('o.id')
          ->having(sprintf('count(og.group_id) %s',$modifier == 'add' ? '= 0' : '= 1'))
          ->groupBy('o.id') // big but beautiful SQL hack...
      ), array('required' => 'Required.', 'invalid' => $invalid[$modifier]));
      $object_id = $validator->clean($request->getParameter('object_id')); // throws an exception if it doesn't validate
      
      // adding / removing the object from the group
      $object = $q->andWhere('o.id = ?',$object_id)->fetchOne();
      if ( $modifier == 'add' )
      {
        $object->Groups[] = $this->form->getObject();
        $object->save();
      }
      else
      {
        $rel = $object->$relation;
        $del = new GroupDeleted; // save the deletion for stats
        $del->created_at = $rel[0]->created_at;
        $del->group_id   = $rel[0]->group_id;
        //$del->information = $rel[0]->information;
        $rel[0]->delete();
        $del->save();
      }
      
      // messages
      $r['success'] = __(ucfirst($type).' '.($modifier == 'add' ? 'added' : 'removed'));
      $r['object_id'] = $object->id;
    }
    //catch ( sfValidatorError $e )
    {
      //$r['error'] = __($e->getMessage(), null, 'sf_admin');
    }
    
    $this->content = $r;
    if (!( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') ))
      return 'Json';
  }
  
  public function executeEmailing(sfWebRequest $request)
  {
    $q = new Doctrine_Query;
    $group = $q->from('Group g')
      ->leftJoin('g.Contacts c')
      ->leftJoin('g.Professionals p')
      ->leftJoin('g.Organisms o')
      ->andWhere('g.id = ?',$request->getParameter('id'))
      ->fetchOne();

    $email = new Email;
    foreach ( array('Contacts','Professionals','Organisms') as $type )
    foreach ( $group->$type as $obj )
    {
      $coll =& $email->$type;
      $coll[] = $obj;
    }
    $email->field_from = $this->getUser()->getGuardUser()->email_address;
    $email->field_subject = '-*-*-*-*-*-*-*-*-*-*-';
    $email->content = '<p>-*-*-*-*-*-*-*-*-*-*-</p>';
    $email->save();
    
    $this->redirect('email/edit?id='.$email->id);
  }
  
  public function executeDelPicture(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()->from('Picture p')
      ->where('p.id IN (SELECT g.picture_id FROM Group g WHERE g.id = ?)',$request->getParameter('id'))
      ->delete()
      ->execute();
    return $this->redirect('group/edit?id='.$request->getParameter('id'));
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->group = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->group);
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->form = $this->configuration->getForm();
    $this->group = $this->form->getObject();
    $this->form->bind($request->getParameter($form->getName()));
    if ( $this->form->getValue('sf_guard_user_id') !== $this->getUser()->getId()
      && !$this->getUser()->hasCredential('pr-group-common') && !$this->getUser()->hasCredential('pr-group-edit') )
      $this->forward404('You cannot modify a common group, sorry.');
    
    $this->processForm($request, $this->form);
    $this->setTemplate('new');
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->group = Doctrine::getTable('Group')->createQuery('g')
      ->addSelect('g.*, u.*')
      ->andWhere('g.id = ?', $request->getParameter('id'))
      ->fetchOne();
    $this->form = $this->configuration->getForm($this->group);
    
    if ( !$this->getUser()->hasCredential(array('pr-group-common', 'admin-users', 'admin-power'), false)
      && in_array($this->getUser()->id, $this->form->getObject()->Users->toKeyValueArray('id', 'id')) )
      $this->form->removeUsersList();
    
    /**
      * if the user cannot modify anything
      * if the user cannot modify common groups and this group is common
      * if the group is not his own
      *
      **/
    if ( !$this->getUser()->hasCredential('pr-group-perso') && !$this->getUser()->hasCredential('pr-group-common')
      || is_null($this->group->sf_guard_user_id) && !$this->getUser()->hasCredential('pr-group-common') && !$this->getUser()->hasCredential('pr-group-edit')
      || $this->group->sf_guard_user_id !== $this->getUser()->getId() && !is_null($this->group->sf_guard_user_id) )
    $this->setTemplate('show');
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->group = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->group);
    
    $this->form->bind($request->getParameter($this->form->getName()));
    if ( $this->form->getValue('sf_guard_user_id') !== $this->getUser()->getId()
      && !$this->getUser()->hasCredential('pr-group-common') && !$this->getUser()->hasCredential('pr-group-edit') )
      $this->forward404('You cannot modify a common group, sorry.');
    
    /**
      * if the user cannot modify anything
      * if the user cannot modify common groups and this group is common
      * if the group is not his own
      *
      **/
    if ( !$this->getUser()->hasCredential('pr-group-perso') && !$this->getUser()->hasCredential('pr-group-common')
      || is_null($this->group->sf_guard_user_id) && !$this->getUser()->hasCredential('pr-group-common') && !$this->getUser()->hasCredential('pr-group-edit')
      || !is_null($this->group->sf_guard_user_id) && $this->group->sf_guard_user_id !== $this->getUser()->getId() )
      $this->forward404('You cannot modify a common group, sorry.');
    
    $this->processForm($request, $this->form);
    $this->setTemplate('edit');
    
  }

  public function executeBatchDelete(sfWebRequest $request)
  {
    // restricting deletable groups to accessible groups
    $q = Doctrine::getTable('Group')->createQuery('g')
      ->andWhereIn('g.id', $request->getParameter('ids'))
    ;
    $ids = array();
    foreach ( $q->execute() as $group )
      $ids[] = $group->id;
    $request->setParameter('ids', $ids);
    
    parent::executeBatchDelete($request);
  }
  public function executeDelete(sfWebRequest $request)
  {
    $this->group = $this->getRoute()->getObject();
    
    /**
      * if the user cannot modify anything
      * if the user cannot modify common groups and this group is common
      * if the group is not his own
      *
      **/
    if ( !$this->getUser()->hasCredential('pr-group-perso') && !$this->getUser()->hasCredential('pr-group-common')
      || is_null($this->group->sf_guard_user_id) && !$this->getUser()->hasCredential('pr-group-common') && !$this->getUser()->hasCredential('pr-group-del')
      || !is_null($this->group->sf_guard_user_id) && $this->group->sf_guard_user_id !== $this->getUser()->getId() )
    $this->forward404('You cannot delete a common group, sorry.');
    
    parent::executeDelete($request);
    
  }

  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $this->pager->getQuery()->orderby('username IS NULL DESC, username, name');
    }
  }

  public function executeCsv(sfWebRequest $request)
  {
    $criterias = array(
      'groups_list'           => array($request->getParameter('id')),
      'organism_id'           => NULL,
      'organism_category_id'  => NULL,
      'professional_type_id'  => NULL,
    );
    $this->getUser()->setAttribute('contact.filters', $criterias, 'admin_module');
    $this->getUser()->setAttribute('organism.filters', $criterias, 'admin_module');
    
    $this->redirect('contact/index');
  }
  
  protected function createQueryByRoute()
  {
    $q = Doctrine_Query::create()
      ->from('Group g')
      ->leftJoin("g.User u")
      ->leftJoin("g.Contacts c")
      ->leftJoin("g.Professionals p")
      ->leftJoin("p.ProfessionalType pt")
      ->leftJoin("p.Contact pc")
      ->leftJoin("p.Organism o")
      ->orderBy('c.name, c.firstname, pc.name, pc.firstname, o.name, pt.name, p.name');
    if ( sfContext::getInstance()->getRequest()->getParameter('id') )
    $q->where('id = ?',sfContext::getInstance()->getRequest()->getParameter('id'));
    
    return $q;
  }
  protected function getObjectByRoute()
  {
    $groups = $this->createQueryByRoute()->limit(1)->execute();
    return $groups[0];
  }
}

