<?php

/**
 * MetaEvent form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MetaEventForm extends BaseMetaEventForm
{
  public function configure()
  {
    $this->widgetSchema   ['users_list']
      ->setOption('order_by',array('username',''))
      ->setOption('expanded',true)
      ->setOption('query', $q = Doctrine::getTable('sfGuardUser')->createQuery('u'))
    ;
    if ( !$this->object->isNew() )
      $q->andWhere('me.id IS NOT NULL AND me.id = ? OR u.is_active = ?', array($this->object->id, true));
    else
      $q->andWhere('u.is_active = ?', true);
  }
}
