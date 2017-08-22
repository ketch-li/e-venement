<?php

/**
 * Control form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ControlForm extends BaseControlForm
{
  protected $force_field = NULL;
  
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    $this->disableCSRFProtectionOnUserAgent();
    
    unset($this->widgetSchema['sf_guard_user_id']);
    unset($this->widgetSchema['version']);
    
    $this->validatorSchema['sf_guard_user_id']->setOption('required', false);
    $this->validatorSchema['version']->setOption('required', false);
    
    $this->widgetSchema['checkpoint_id']
        ->setOption('add_empty',true)
        //->setOption('multiple', true)
    ;
    
    $this->widgetSchema['ticket_id'] = new sfWidgetFormInput();
    $this->widgetSchema['comment'] = new sfWidgetFormTextArea();
    
    $validators = array();
    foreach ( $this->getFieldsConfig() as $field )
    {
      $validators[] = new sfValidatorDoctrineChoice(array(
        'model' => 'Ticket',
        'column' => $field,
        'query' => Doctrine::getTable('Ticket')->createQuery('t')->select('t.*')
          ->andWhere('t.printed_at IS NOT NULL OR t.integrated_at IS NOT NULL'),
      ));
    }
    $this->validatorSchema['ticket_id'] = new sfValidatorOr($validators);
  }
  
  public function doBind(array $values)
  {
    if ( !in_array('othercode', $this->getFieldsConfig()) // because othercode can be also an integer
      && intval($values['ticket_id']).'' === ''.$values['ticket_id'] )
    {
      $validators = $this->validatorSchema['ticket_id']->getValidators();
      $validators[0]->setOption('column', 'id');
    }
    
    if ( $this->forceField() )
    {
      $this->validatorSchema['ticket_id'] = new sfValidatorDoctrineChoice(array(
        'model' => 'Ticket',
        'column' => $this->forceField(),
        'query' => Doctrine::getTable('Ticket')->createQuery('t')->select('t.*')
          ->andWhere('t.printed_at IS NOT NULL OR t.integrated_at IS NOT NULL'),
      ));
    }
    
    return parent::doBind($values);
  }
  
  public function isValid()
  {
    if ( !parent::isValid() )
      return false;
    
    if (!( $checkpoint = Doctrine::getTable('Checkpoint')->find($this->values['checkpoint_id']) ))
      return false;
    $this->object->Checkpoint = $checkpoint;
    return $checkpoint->mightControl($this->values['ticket_id']);
  }
  
  public function forceField($field = NULL)
  {
    if ( $field )
    {
      $this->force_field = $field;
      if ( $field == 'id' )
        $this->object->setTicketIdForced();
    }
    return $this->force_field;
  }
  public static function getFieldsConfig()
  {
    $field = sfConfig::get('app_tickets_id', 'id');
    return is_array($field) ? $field : array($field);
  }
}
