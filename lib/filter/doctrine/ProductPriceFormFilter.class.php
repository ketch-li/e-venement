<?php

/**
 * ProductPrice filter form.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProductPriceFormFilter extends BaseProductPriceFormFilter
{
  /**
   * @see PriceFormFilter
   */
  public function configure()
  {

    $this->widgetSchema['name'] = new sfWidgetFormInput(array(
    ));
    $this->validatorSchema['name'] = new sfValidatorString(array(
      'required' => false,
    ));
    

    $this->widgetSchema['description'] = new sfWidgetFormInput(array(
    ));
    $this->validatorSchema['description'] = new sfValidatorString(array(
      'required' => false,
    ));
    
    parent::configure();
  }
  
  public function addNameColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value )      
      $q->andWhere('pt.name ILIKE ?', trim(strtolower($value)).'%');
    return $q;
  }
  public function addDescriptionColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value )
      $q->andWhere('pt.description ILIKE ?', trim(strtolower($value)).'%');
    return $q;
  }
}
