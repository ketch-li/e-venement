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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/eventGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/eventGeneratorHelper.class.php';

/**
 * event actions.
 *
 * @package    e-venement
 * @subpackage event
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class eventActions extends autoEventActions
{
  public function executeAddContactToEvent(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    
    $this->form = new sfForm;
    $ws = $this->form->getWidgetSchema();
    $vs = $this->form->getValidatorSchema();
    $ws['professional_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Professional',
      'url'   => cross_app_url_for('rp', 'professional/ajax'),
      'default' => $request->getParameter('professional_id')
    ));
    $ws['event_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Event',
      'url'   => cross_app_url_for('event', 'event/ajax'),
      'default' => $request->getParameter('event_id')
    ));
    
    // if some data is given
    if ( !$request->hasParameter('nogo') && $request->hasParameter('professional_id') && $request->hasParameter('event_id') )
    {
      // prerequisite
      if ( !($entry = Doctrine::getTable('Entry')->createQuery('e')
        ->andWhere('e.event_id = ?', $request->getParameter('event_id'))
        ->fetchOne())
      || !($professional = Doctrine::getTable('Professional')->createQuery('p')
        ->andWhere('p.id = ?', $request->getParameter('professional_id'))
        ->fetchOne()) )
      {
        $this->getUser()->setFlash('error', __('The submitted event or contact is invalid.'));
        $this->redirect('event/addContactToEvent?professional_id='.$request->getParameter('professional_id').'&event_id='.$request->getParameter('event_id').'&nogo=1');
      }
      
      $ce = new ContactEntry;
      $ce->entry_id = $entry->id;
      $ce->professional_id = $professional->id;
      $ce->save();
      
      $this->redirect('event/addContactToEvent?event_id='.$request->getParameter('event_id'));
    }
  }
  public function executeAddManifestationToEvent(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    
    $this->form = new sfForm;
    $ws = $this->form->getWidgetSchema();
    $vs = $this->form->getValidatorSchema();
    $ws['manifestation_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event', 'manifestation/ajax'),
      'default' => $request->getParameter('manifestation_id')
    ));
    
    // if some data is given
    if ( !$request->hasParameter('nogo') && $request->hasParameter('manifestation_id') )
    {
      // prerequisite
      if ( !($entry = Doctrine::getTable('Entry')->createQuery('e')
        ->leftJoin('e.Event ev')
        ->leftJoin('ev.Manifestations m')
        ->andWhere('m.id = ?', $request->getParameter('manifestation_id'))
        ->andWhere('e.id NOT IN (SELECT me.entry_id FROM ManifestationEntry me WHERE me.manifestation_id = ?)', $request->getParameter('manifestation_id'))
        ->fetchOne())
      || !($manifestation = Doctrine::getTable('Manifestation')->createQuery('m')
        ->andWhere('m.id = ?', $request->getParameter('manifestation_id'))
        ->fetchOne()) )
      {
        $this->getUser()->setFlash('error', __('The submitted manifestation is invalid.'));
        $this->redirect('event/addManifestationToEvent?manifestation_id='.$request->getParameter('manifestation_id').'&nogo=1');
      }
      
      $ce = new ManifestationEntry;
      $ce->entry_id = $entry->id;
      $ce->manifestation_id = $manifestation->id;
      $ce->save();
      
      $this->redirect('event/addManifestationToEvent');
    }
  }
  public function executeFromDateToDate(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
    $this->form = new sfForm;
    $ws = $this->form->getWidgetSchema();
    $vs = $this->form->getValidatorSchema();
    $ws->setNameFormat('extract[%s]');
    $ws['dates'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'template'  => '<span class="from">'.__('From %from_date%').'</span> <span class="to">'.__('to %to_date%').'</span>',
    ));
    $vs['dates'] = new sfValidatorDateRange(array(
      'from_date'     => new sfValidatorDate(array('date_output' => 'Y-m-d','with_time' => false,)),
      'to_date'       => new sfValidatorDate(array('date_output' => 'Y-m-d', 'with_time'   => false,)),
    ));
    
    if ( !$request->hasParameter('extract') )
      return 'Success';
    $this->form->bind($request->getParameter('extract'));
    if ( !$this->form->isValid() )
      return 'Success';
    
    $request->setParameter('dates', $this->form->getValue('dates'));
    $this->forward('event', 'accepted');
  }
  public function executeExport(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/export.php');
  }
  public function executeRefused(sfWebRequest $request)
  {
    $request->setParameter('type','refused');
    $this->executeCsv($request);
  }
  public function executeImpossible(sfWebRequest $request)
  {
    $request->setParameter('type','impossible');
    $this->executeCsv($request);
  }
  public function executeAccepted(sfWebRequest $request)
  {
    $request->setParameter('type','accepted');
    return $this->executeCsv($request);
  }
  public function executeCsv(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/csv.php');
  }
  public function executeGauge(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/gauge.php');
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $q = $this->pager->getQuery();
      $a = $q->getRootAlias();
      $q->andWhereIn("$a.meta_event_id",array_keys($this->getUser()->getMetaEventsCredentials()))
        ->orderby('translation.name');
    }
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    
    $q = Doctrine_Query::create()->from('Entry e')
      ->leftJoin('e.ContactEntries ce')
      ->leftJoin('ce.Transaction t')
      ->leftJoin('t.Translinked t2')
      ->leftJoin('ce.Professional p')
      ->leftJoin('p.Contact c')
      ->leftJoin('p.Organism o')
      ->leftJoin('e.ManifestationEntries me')
      ->leftJoin('me.Manifestation m')
      ->andWhere('e.event_id = ?',$request->getParameter('id'))
      ->orderBy("ce.comment1 IS NULL OR TRIM(ce.comment1) = '', ce.comment1, c.name, c.firstname, m.happens_at ASC")
    ;
    
    // using date_range filter to focus on some manifestations only
    $filters = $this->getFilters();
    if ( isset($filters['dates_range']) )
    {
      if ( isset($filters['dates_range']['from']) )
        $q->andWhere('m.happens_at > ?', $filters['dates_range']['from']);
      if ( isset($filters['dates_range']['to']) )
        $q->andWhere('m.happens_at < ?', $filters['dates_range']['to']);
    }
    
    $this->entry = $q->fetchOne();
    
    if ( !$this->entry )
    {
      $this->entry = new Entry;
      $this->entry->event_id = $request->getParameter('id',NULL);
      $this->entry->save();
    }
  }

  protected function getSort()
  {
    if (!is_null($sort = $this->getUser()->getAttribute('grp.event.sort', null, 'admin_module')))
    {
      return $sort;
    }

    $this->setSort($this->configuration->getDefaultSort());

    return $this->getUser()->getAttribute('grp.event.sort', null, 'admin_module');
  }

  protected function setSort(array $sort)
  {
    if (!is_null($sort[0]) && is_null($sort[1]))
    {
      $sort[1] = 'asc';
    }

    $this->getUser()->setAttribute('grp.event.sort', $sort, 'admin_module');
  }

  protected function getFilters()
  {
    return $this->getUser()->getAttribute('grp.event.filters', $this->configuration->getFilterDefaults(), 'admin_module');
  }

  protected function setFilters(array $filters)
  {
    return $this->getUser()->setAttribute('grp.event.filters', $filters, 'admin_module');
  }
}
