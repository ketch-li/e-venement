<?php

/**
 * Gauge form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TicketsIntegrationForm extends BaseFormDoctrine
{
  protected $manifestation;
  
  public function getModelName()
  {
    return 'Transaction';
  }
  
  public function __construct(Manifestation $manifestation)
  {
    $this->manifestation = $manifestation;
    parent::__construct();
  }
  
  public function configure()
  {
    $this->widgetSchema->setNameFormat('integrate[%s]');
    
    $filetypes = array(
      'fb' => 'FranceBillet',
      'tkn' => 'Ticketnet',
    );
    $this->widgetSchema   ['filetype'] = new sfWidgetFormChoice(array(
      'choices' => $filetypes,
      'expanded' => true,
      'default' => 'fb',
    ));
    $this->validatorSchema['filetype'] = new sfValidatorChoice(array(
      'choices'   => array_keys($filetypes),
      'required'  => true,
    ));
    
    $this->widgetSchema   ['gauges_list'] = new sfWidgetFormDoctrineChoice(array(
      'expanded'  => true,
      'model'     => 'Gauge',
      'query'     => Doctrine::getTable('Gauge')->createQuery('g')->andWhere('g.manifestation_id = ?',$this->manifestation->id),
      'order_by'  => array('ws.name','ASC'),
    ));
    $this->validatorSchema['gauges_list'] = new sfValidatorDoctrineChoice(array(
      'model'     => 'Gauge',
      'query'     => Doctrine::getTable('Gauge')->createQuery('g')->andWhere('g.manifestation_id = ?',$this->manifestation->id),
      'required'  => true,
    ));
    
    $this->widgetSchema   ['file'] = new sfWidgetFormInputFile();
    $this->validatorSchema['file'] = new sfValidatorFile(array(
      'required'  => true,
    ));
    
    $this->widgetSchema   ['transaction_ref_id'] = new sfWidgetFormInput(array(
      'label' => 'Reference transaction',
    ));
    $this->validatorSchema['transaction_ref_id'] = new sfValidatorDoctrineChoice(array(
      'required'  => false,
      'model' => 'Transaction',
    ));
  }
}
