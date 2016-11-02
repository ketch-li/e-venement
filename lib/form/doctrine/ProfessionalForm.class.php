<?php

/**
 * Professional form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProfessionalForm extends BaseProfessionalForm
{
  public function configure()
  {
    $this->widgetSchema   ['professional_type_id'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'ProfessionalType',
      'order_by'  => array('name',''),
      'add_empty' => true,
    ));
    $this->validatorSchema['professional_type_id'] = new sfValidatorDoctrineChoice(array(
      'model'     => 'ProfessionalType',
      'required'  => false,
    ));
    
    $this->widgetSchema['groups_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Group',
      'url'   => cross_app_url_for('rp', 'group/ajax'),
      'config' => '{ max: 300 }',
    ));
    
    $this->widgetSchema['organism_id']->setOption('config', '{ max: 20 }');
    
    if ( !$this->object->isNew() && sfConfig::get('app_options_design',false) && sfConfig::get(sfConfig::get('app_options_design').'_active') )
    {
      $orgForm = new OrganismForm($this->getObject()->Organism);
      $orgForm->useFields(array('description'));
      $this->embedForm('organism',$orgForm);
      $this->widgetSchema->setNameFormat('professional_'.$this->object->id.'[%s]');
    }
    
    parent::configure();
  }
  
  public function save($con = NULL)
  {
    if ( $this->object->isNew() )
    {
      // removing the potentially existing organism embed form
      unset($this->widgetSchema['organism'],$this->validatorSchema['organism']);
    }
    
    return parent::save();
  }

  public function saveGroupsList($con = null)
  {
    $this->correctGroupsListWithCredentials();
    return parent::saveGroupsList($con);
  }
  public function saveEmailsList($con = null)
  {
    // BUG: 2013-04-12
    return;
  }
}
