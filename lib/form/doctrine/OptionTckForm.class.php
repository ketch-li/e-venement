<?php

/**
 * OptionTck form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OptionTckForm extends BaseOptionTckForm
{
  /**
   * @see OptionForm
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    parent::configure();
    
    $this->model = 'OptionTck';
    
    foreach ( array('type','name','value','sf_guard_user_id','created_at','updated_at',) as $id )
    {
      unset($this->widgetSchema   [$id]);
      unset($this->validatorSchema[$id]);
    }    

    $this->widgetSchema['tck-print-ticket-cp'] = new sfWidgetFormInputCheckbox(array(
        'label' => __('Force postal code')), 
        array('value' => 1)
    );      
    $this->validatorSchema['tck-print-ticket-cp'] = new sfValidatorBoolean(array(
      'true_values' => array('1'),
      'required' => false
    ));    
    
    $q = Doctrine::getTable('OptionTck')->createQuery('o')
      ->andWhere('o.sf_guard_user_id IS NULL')
      ->andWhere('o.name = ?', 'tck-print-ticket-cp');

    $option = $q->fetchOne();
    $auth = false;

    if ($option) {
        $auth = $option->value;
    }

    $this->setDefault('tck-print-ticket-cp', $auth);    
  }
}
