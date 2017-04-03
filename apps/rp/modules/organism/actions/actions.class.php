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

require_once dirname(__FILE__).'/../lib/organismGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/organismGeneratorHelper.class.php';

/**
 * organism actions.
 *
 * @package    e-venement
 * @subpackage organism
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class organismActions extends autoOrganismActions
{
  private $force_classic_template_dir = false;
  
  public function executeDuplicate(sfWebRequest $request)
  {
    $this->executeShow($request);
    
    $organism = new Organism;
    $organism->name = $this->organism->name;
    $organism->address = $this->organism->address;
    $organism->postalcode = $this->organism->postalcode;
    $organism->city = $this->organism->city;
    $organism->country = $this->organism->country;
    foreach ( $this->organism->Phonenumbers as $pn )
      $organism->Phonenumbers[] = $pn->copy();
    
    $organism->save();
    $this->getUser()->setFlash('notice', 'The item was created successfully.');
    $this->redirect('organism/edit?id='.$organism->id);
  }
  
  public function postExecute()
  {
    $this->addExtraRequirements();
    if ( !$this->useClassicTemplateDir() )
      $this->getContext()->getConfiguration()->changeTemplatesDir($this);
    return parent::postExecute();
  }
  protected function useClassicTemplateDir($bool = NULL)
  {
    if ( is_null($bool) )
      return $this->force_classic_template_dir;

    $this->force_classic_template_dir = $bool;
    return $this;
  }
  protected function addExtraRequirements()
  {
    if ( sfConfig::get('app_options_design',false) == 'tdp' && sfConfig::get(sfConfig::get('app_options_design').'_active',false) )
    {
      if ( !isset($this->hasFilters) )
        $this->hasFilters = $this->getUser()->getAttribute('organism.filters', $this->configuration->getFilterDefaults(), 'admin_module');
      if ( !isset($this->filters) )
        $this->filters = $this->configuration->getFilterForm($this->getFilters());
      if ( in_array($this->getActionName(), array('edit','new','show','create','update','delete')) )
        $this->setTemplate('edit');
    }
  }
  public function executeGroup(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/group.php');
  }
  public function executeGroupList(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/group-list.php');
  }
  public function executeBatchMerge(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/batch-merge.php');
  }
  public function executeBatchDelete(sfWebRequest $request)
  {
    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_objects', array(
      'objects' => Doctrine::getTable('Organism')->createQuery('o')
        ->andWhereIn('o.id',$request->getParameter('ids'))
        ->select('o.*')
        ->execute(),
    )));
    return parent::executeBatchDelete($request);
  }
  public function executeArchives(sfWebRequest $request)
  {
    $this->executeEdit($request);
    $this->setTemplate('edit');
    $this->organism->Professionals = $this->organism->ProfessionalArchives;
  }
  public function executeVersion(sfWebRequest $request)
  {
    $this->executeShow($request);
    
    if ( !($v = $request->getParameter('v',false)) )
      $v = $this->contact->version > 1 ? $this->contact->version - 1 : 1;
    
    if ( $v < 1 )
      $v = 1;
    
    $request->setParameter('v',$v);
    
    if (!( intval($v).'' == ''.$v && $this->organism->getSearchedVersion($v) ))
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error', __('You have requested the version #%%v%% that does not exist.', array('%%v%%' => $v)));
      $this->redirect('organism/show?id='.$this->organism->id);
    }
    
    $this->object = $this->organism;
  }
  
  public function executeEmailing(sfWebRequest $request)
  {
    $this->redirect('email/new');
  }
  public function executeGetSpecializedForm(sfWebRequest $request)
  {
    $this->executeEdit($request);
    $this->form->displayOnly($this->field = $request->getParameter('field'));
    $this
      ->useClassicTemplateDir(true)
      ->setLayout('empty');
  }
  public function executeBatchAddToGroup(sfWebRequest $request)
  {
    $request->checkCSRFProtection();
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $filters = $request->getParameter($this->getModuleName().'_filters');
    
    try {
      error_log(print_r($_POST,true));
      $validator = new sfValidatorDoctrineChoice(array('model' => 'Organism', 'multiple' => true, 'required' => false));
      $ids = $validator->clean($request->getParameter('ids'));
      $validator = new sfValidatorDoctrineChoice(array('model' => 'Group', 'multiple' => true));
      $groups = $validator->clean(isset($groups['groups_list'])
        ? $groups['groups_list']
        : $filters['groups_list']
      );
    }
    catch (sfValidatorError $e)
    {
      $this->getUser()->setFlash('error', 'A problem occured when adding the selected items as some items do not exist anymore.');
      return $this->redirect('@organism');
    }
    
    if ( count($ids) > 0 )
    foreach ( $ids as $organism_id )
    foreach ( $groups as $group_id )
    {
      $go = new GroupOrganism;
      $go->organism_id = $organism_id;
      $go->group_id = $group_id;
      
      try { $go->save(); }
      catch(Doctrine_Exception $e) {}
    }
    
    $this->getUser()->setFlash('notice',__('The chosen organisms have been added to the selected groups.'));
    $this->redirect('@organism');
  }
  public function executeBatchAddProToGroup(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $ids = $request->getParameter('ids');
    $groups = $request->getParameter('groups');
    
    $orgs = Doctrine::getTable('Organism')->createQuery('o')
      ->whereIn('o.id',$ids)
      ->execute();
    
    foreach ( $orgs as $organism )
    foreach ( $organism->Professionals as $pro )
    foreach ( $groups as $group_id )
    {
      $gp = new GroupProfessional();
      $gp->professional_id = $pro->id;
      $gp->group_id = $group_id;
      
      try { $gp->save(); }
      catch(Doctrine_Exception $e) {}
    }
    
    $this->getUser()->setFlash('notice',__('The contacts in chosen organisms have been added to the selected groups.'));
    $this->redirect('group/show?id='.$gp->group_id);
  }

  public function executeEmailList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('organism','index');
    
    $this->email_id = $request->getParameter('id');
    $q = Doctrine::getTable('Organism')->createQueryByEmailId($this->email_id);
    
    $this->pager = $this->configuration->getPager('Organism');
    $this->pager->setMaxPerPage(15);
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
  }
  public function executeSearchIndexing(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $table = Doctrine_Core::getTable('Organism');
    $table->getTemplate('Doctrine_Template_Searchable')->getPlugin()
      ->setOption('analyzer', new MySearchAnalyzer());
    $table->batchUpdateIndex($nb = 1500);
    
    $this->getUser()->setFlash('notice',__('%nb% record(s) have been indexed',array('%nb%' => $nb)));
    $this->executeIndex($request);
    $this->setTemplate('index');
  }
  
  public function executeFilters(sfWebRequest $request)
  {
    $this->executeIndex($request);
    
    // optimizations
    $ws = $this->filters->getWidgetSchema();
    $ws['groups_list'] = $ws['not_groups_list'];
  }
  public function executeSideBar(sfWebRequest $request)
  {
    $this->filters = false;  // a trick that avoids until 5 sec of processing #1/2
    $cacher = liCacher::create('organism/sideBar?sf_guard_user_id='.$this->getUser()->getId())
      ->setDomain('rp-index');
    if ( !$cacher->requiresRefresh($request) )
    if ( ($this->cache = $cacher->useCache()) !== false )
      return 'Success';
    
    unset($this->filters);  // trick #2/2
    $this->executeIndex($request);
    $this->cache = $this->getPartial('global/tdp/side_widget', array(
      'filters' => $this->filters,
    ));
    
    $cacher
      ->setData($this->cache)
      ->writeData();
  }
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $this->pager->getQuery()->orderby('name');
    }
    if ( $contact_id = intval($request->getParameter('contact_id')) )
      $this->pager->getQuery()->andWhere('c.id = ?', $contact_id);
  }

  public function executeShow(sfWebRequest $request)
  {
    $this->organism = Doctrine::getTable('Organism')->findWithTickets($request->getParameter('id'));
    $this->forward404Unless($this->organism instanceof Organism);
    $this->form = $this->configuration->getForm($this->organism);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->executeShow($request);
    
    if ( (sfConfig::get('app_options_design',false) != 'tdp' || !sfConfig::get(sfConfig::get('app_options_design').'_active',false))
      && !$this->getUser()->hasCredential('pr-organism-edit') )
      $this->setTemplate('show');
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $this->filters = true; // hack Beaulieu du 30/09/2013 à valider avant commit
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $q = Doctrine::getTable('Organism')
      ->createQuery()
      ->orderBy('name')
      ->limit($request->getParameter('limit'));
    if ( $request->getParameter('email') == 'true' )
    $q->andWhere("email IS NOT NULL AND email != ?",'');
    $q = Doctrine_Core::getTable('Organism')
      ->search($search.'*',$q);
    $request = $q->execute()->getData();

    $organisms = array();
    foreach ( $request as $organism )
      $organisms[$organism->id] = (string) $organism;
    
    return $this->renderText(json_encode($organisms));
  }

  protected function addViewRenderer()
  {
    $response = $this->getResponse()->addStyleSheet('view');
    $response = $this->getResponse()->addJavaScript('more-simple');
  }

  public function executeMap(sfWebRequest $request)
  {
    $q = $this->buildQuery();
    $this->gMap = new GMap();
    if ( !$this->gMap->getGMapClient()->getAPIKey() )
    {
      $this->getUser()->setFlash('error',__("The geolocalization module is not enabled, you can't access this function."));
      $this->redirect('index');
    }
    $this->gMap = Addressable::getGmapFromQuery($q,$request);
  }
  
  public function executeSearch(sfWebRequest $request)
  {
    self::executeIndex($request);
    $this->quickest = true;
    
    $search = $this->sanitizeSearch($request->getParameter('s'));
    $transliterate = sfConfig::get('software_internals_transliterate',array());
    
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $table = Doctrine_Core::getTable('Organism');
    $this->pager->setQuery($table->search($search.'*',$this->pager->getQuery()));

    $this->addExtraRequirements();
    
    $this->pager->init();
    $this->setTemplate('index');
  }
  
  public function executeBatchRemoveFromFilters(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/batch-remove-from-filters.php');
  }
  public function executeCsv(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/csv.php');
  }
  public function executeLabels(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/labels.php');
  }
  
  public function executeVcf(sfWebRequest $request)
  {
    $this->executeShow($request);
    $this->useClassicTemplateDir(true);
  }
  
  public function executeFilter(sfWebRequest $request)
  {
    // remove cached data
    Doctrine::getTable('Cache')->createQuery('c')
      ->andWhere('c.domain = ?', 'rp-index')
      ->andWhere('c.identifier LIKE ?', '%/sideBar?sf_guard_user_id='.$this->getUser()->getId())
      ->delete()
      ->execute();

    if ( sfConfig::get('app_options_design',false) == 'tdp' && sfConfig::get(sfConfig::get('app_options_design').'_active',false) )
    {
      $this->setFilters($this->configuration->getFilterDefaults());
      $this->getUser()->setAttribute('contact.filters',$this->configuration->getFilterDefaults(),'admin_module');
    }
    return parent::executeFilter($request);
  }
  public function getFilters()
  {
    if ( sfConfig::get('app_options_design',false) != 'tdp' || !sfConfig::get(sfConfig::get('app_options_design').'_active',false) )
      return parent::getFilters();
    
    $filters = parent::getFilters();
    $other_filters = $this->getUser()->getAttribute('contact.filters',null,'admin_module');
    if ( !(isset($filters['organism_category_id']) && is_array($filters['organism_category_id'])) )
      $filters['organism_category_id'] = array();
    if ( !(isset($filters['groups_list']) && is_array($filters['groups_list'])) )
      $filters['groups_list'] = array();
    if ( !(isset($other_filters['organism_category_id']) && is_array($other_filters['organism_category_id'])) )
      $other_filters['organism_category_id'] = array();
    if ( !(isset($other_filters['groups_list']) && is_array($other_filters['groups_list'])) )
      $other_filters['groups_list'] = array();
    $filters['organism_category_id'] = array_merge($filters['organism_category_id'], $other_filters['organism_category_id']);
    $filters['groups_list'] = array_merge($filters['groups_list'], $other_filters['groups_list']);
    $this->setFilters($filters);
    return $filters;
  }
  public static function sanitizeSearch($search)
  {
    $nb = mb_strlen($search);
    $charset = sfConfig::get('software_internals_charset');
    $transliterate = sfConfig::get('software_internals_transliterate',array());
    
    $search = str_replace(preg_split('//u', $transliterate['from'], -1), preg_split('//u', $transliterate['to'], -1), $search);
    $search = str_replace(MySearchAnalyzer::$cutchars,' ',$search);
    $search = preg_replace(array('!^[\d\w] !', '! [\d\w] !'), ' ', $search);
    $search = mb_strtolower(iconv($charset['db'],$charset['ascii'], mb_substr($search,$nb-1,$nb) == '*' ? mb_substr($search,0,$nb-1) : $search));
    return $search;
  }
}
