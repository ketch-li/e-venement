<?php

require_once dirname(__FILE__).'/../lib/meta_eventGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/meta_eventGeneratorHelper.class.php';

/**
 * meta_event actions.
 *
 * @package    e-venement
 * @subpackage meta_event
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class meta_eventActions extends autoMeta_eventActions
{
  public function executeDelPicture(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()->from('Picture p')
      ->where('p.id IN (SELECT me.picture_id FROM MetaEvent me WHERE me.id = ?)',$request->getParameter('id'))
      ->delete()
      ->execute();
    return sfView::NONE;
  }

  public function executeBatchDuplicate(sfWebRequest $request)
  {
    $class = 'MetaEvent';
    $rc = new ReflectionClass($class);
    $sfUser = sfContext::getInstance()->getUser();

    if ( $rc->implementsInterface('liDuplicable') )
    {
      $ids = $request->getParameter('ids');

      $metaEvents = Doctrine::getTable($class)->createQuery('me')->orderBy('me.updated_at DESC')
        ->andWhereIn('me.id', $ids)
        ->andWhereIn('me.id', array_keys($sfUser->getMetaEventsCredentials()))
        ->execute();

      if ( $metaEvents->count() == 0 )
      {
        $this->getUser()->setFlash('error', 'You must at least select one item.');
        $this->redirect('@meta_event');
      }
      
      $duplicated = array();

      foreach ( $metaEvents as $metaEvent )
        $duplicated[] = $metaEvent->duplicate();
      
      if (count($duplicated) >= count($ids))
      {
        $this->getUser()->setFlash('notice', 'The selected items have been duplicated successfully.');
      }
      else
      {
        $this->getUser()->setFlash('error', 'A problem occured when duplicating the selected items.');
      }
    }
    else
      throw new sfException(sprintf('Class %s must implement interface liDuplicable in order to be duplicated.', $class));

    $this->redirect('@meta_event');
  }

}
