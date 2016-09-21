<?php

/**
 * MetaEvent filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MetaEventFormFilter extends BaseMetaEventFormFilter
{
  public function configure()
  {
    $this->widgetSchema   ['name'] = new sfWidgetFormInputText;
    $this->validatorSchema['name'] = new sfValidatorString(array('required' => false));
  }
  
  public function addNameColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    return $q->andWhere('translation.name ILIKE ?', $values.'%');
  }
}
