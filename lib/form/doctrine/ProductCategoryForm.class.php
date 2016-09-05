<?php

/**
 * ProductCategory form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProductCategoryForm extends BaseProductCategoryForm
{
  public function configure()
  {
    $this->widgetSchema['vat_id']->setOption('order_by', array('value',''));
    $this->widgetSchema['product_category_id']->setOption('order_by', array('pct.name', ''));
    
    $this->widgetSchema['color_id']
      ->setOption('order_by',array('name',''))
      ->setOption('method', 'getName');

    // accepts only one level of ancestry
    $this->widgetSchema   ['product_category_id']->setOption('query', $q = Doctrine::getTable('ProductCategory')->createQuery('pc')
      ->andWhere('pc.product_category_id IS NULL'));
    $this->validatorSchema['product_category_id']->setOption('query', $q);
    if ( sfContext::hasInstance() )
      $q->andWhere('pct.lang = ?', sfContext::getInstance()->getUser()->getCulture());
    
    if ( $this->object->isNew() )
      return;
    
    // Now everything is done for updates, not for creations
    $this->widgetSchema['product_category_id']->getOption('query')
      ->andWhere('pc.id != ?', $this->object->id);
  }
}
