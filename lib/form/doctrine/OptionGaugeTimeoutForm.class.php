<?php

/**
 * OptionGaugeTimeout form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OptionGaugeTimeoutForm extends BaseOptionGaugeTimeoutForm
{
  /**
   * @see OptionForm
   */
  public function configure()
  {
    parent::configure();
    $this->model = 'OptionGaugeTimeout';

    self::enableCSRFProtection();

    foreach ( array('type','name','value','sf_guard_user_id','created_at','updated_at',) as $id )
    {
      unset($this->widgetSchema   [$id]);
      unset($this->validatorSchema[$id]);
    }

    $this->widgets = array(
      '' => array(
        'timeout' => array(
          'label'   => 'Timeout',
          'type'    => 'integer',
          'helper'  => '(in minutes)',
          'default' => '',
        ),
      ),
    );
    $this->convertConfiguration($this->widgets);
  }

  protected function convertConfiguration($widgets)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    foreach ( $widgets as $fieldset )
    foreach ( $fieldset as $name => $value )
    {
      $validator_class = 'sfValidator'.strtoupper(substr($value['type'],0,1)).strtolower(substr($value['type'],1));

      $this->widgetSchema[$name]    = new sfWidgetFormInputText(array(
          'label'                 => $value['label'],
          'default'               => $value['default'],
          'type'                  => 'number',
        ),
        array(
          'title'                 => __('previous:').' '.$value['default'].' '.$value['helper'],
      ));
      $this->validatorSchema[$name] = new $validator_class(array(
        'required' => false,
      ));
    }
  }

  public static function getDBOptions()
  {
    $r = array('timeout' => '');

    foreach ( self::buildOptionsQuery()->fetchArray() as $opt )
      $r[$opt['name']] = $opt['value'];
    return $r;
  }

  protected static function buildOptionsQuery()
  {
    return $q = Doctrine::getTable('OptionGaugeTimeout')->createQuery('ogt')
      ->andWhere('ogt.type = ?', 'gauge_timeout')
      ->andWhere('ogt.name = ?', 'timeout')
      ->andWhere('ogt.sf_guard_user_id IS NULL');
  }
}
