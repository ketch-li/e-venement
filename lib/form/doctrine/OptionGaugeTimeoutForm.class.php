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
        'timeout' => 'Timeout',
      ),
    );

    $this->widgetSchema['timeout'] = new sfWidgetFormInput(array(
        'type' => 'text',
        'label' => 'Timeout (in minutes)',
        ),
      array(
        'title' => 'Timeout',
        'help' => '(in minutes)',
    ));
    $this->validatorSchema['timeout'] = new sfValidatorInteger();
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
      ->andWhere('ogt.name = ?', 'timeout');
  }
}
