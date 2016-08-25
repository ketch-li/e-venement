<?php

/**
 * Base project form.
 * 
 * @package    e-venement
 * @subpackage form
 * @author     Your name here 
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class StatsCriteriasForm extends BaseForm
{
  public function configure()
  {
    $this->widgetSchema['dates'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'template'  => 'du %from_date%<br/> au %to_date%',
    ));
    $this->validatorSchema['dates'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate(array('required' => false)),
      'to_date'   => new sfValidatorDate(array('required' => false)),
      'required' => false,
    ));
    
    $this->widgetSchema->setNameFormat('criterias[%s]');
    $this->disableCSRFProtection();
  }
  
  public function addManifestationCriteria()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','CrossAppLink'));
    
    $this->widgetSchema['manifestations_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'url' => cross_app_url_for('event','manifestation/ajax'),
      'model' => 'Manifestation',
      'config'=> '{ max: 50 }',
      'label' => 'Manifestations',
    ));
    $this->validatorSchema['manifestations_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'required' => false,
      'multiple' => true,
    ));
    return $this;
  }
  public function addEventCriterias()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','CrossAppLink'));
    
    $this->widgetSchema['events_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'url' => cross_app_url_for('event','event/ajax'),
      'model' => 'Event',
      'label' => 'Events',
    ));
    $this->validatorSchema['events_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Event',
      'required' => false,
      'multiple' => true,
    ));
    
    $this->widgetSchema['workspaces_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Workspace',
      'query' => Doctrine::getTable('Workspace')->createQuery('ws')
        ->andWhereIn('ws.id',array_keys(sfContext::getInstance()->getUser()->getWorkspacesCredentials())),
      'order_by' => array('name',''),
      'multiple' => true,
      'label' => 'Workspaces',
    ));
    $this->validatorSchema['workspaces_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Workspace',
      'multiple' => true,
      'required' => false,
    ));
    
    $this->widgetSchema['meta_events_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MetaEvent',
      'query' => Doctrine::getTable('MetaEvent')->createQuery('me')
        ->andWhereIn('me.id',array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials())),
      'order_by' => array('name',''),
      'multiple' => true,
      'label' => 'Meta events',
    ));
    $this->validatorSchema['meta_events_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'MetaEvent',
      'multiple' => true,
      'required' => false,
    ));
    return $this;
  }
  
  public function addLocationsCriteria()
  {
    $this->widgetSchema['locations_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Location',
      'query' => Doctrine::getTable('Location')->createQuery('l')
        ->andWhere('l.place = ?', true),
      'order_by' => array('name',''),
      'multiple' => true,
      'label' => 'Locations',
    ));
    $this->validatorSchema['locations_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Location',
      'multiple' => true,
      'required' => false,
    ));
    return $this;
  }
  
  public function addUsersCriteria()
  {
    $this->widgetSchema['users'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'sfGuardUser',
      'order_by'  => array('first_name, last_name',''),
      'multiple'  => true,
      'label' => 'Users',
    ));
    $this->validatorSchema['users'] = new sfValidatorDoctrineChoice(array(
      'model' => 'sfGuardUser',
      'multiple' => true,
      'required' => false,
    ));
    return $this;
  }
  
  public function addAccountingCriterias()
  {
    $this->widgetSchema['accounting_vat'] = new sfWidgetFormInput();
    $this->validatorSchema['accounting_vat'] = new sfValidatorInteger(array(
      'min' => 0,
      'max' => 100,
    ));
    $this->widgetSchema['accounting_unit_price'] = new sfWidgetFormInput();
    $this->validatorSchema['accounting_unit_price'] = new sfValidatorInteger(array(
      'min' => 0,
    ));
    return $this;
  }
  
  public function addWithContactCriteria()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    $choices = array(
      ''    => __('yes or no',null,'sf_admin'),
      'yes' => __('yes',null,'sf_admin'),
      'no'  => __('no',null,'sf_admin'),
    );
    
    $this->widgetSchema   ['with_contact'] = new sfWidgetFormChoice(array(
      'choices' => $choices,
      'label' => 'Tickets with contact',
    ));
    $this->validatorSchema['with_contact'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
    ));
    return $this;
  }
  
  public function addIntervalCriteria()
  {
    $this->widgetSchema   ['interval'] = new sfWidgetFormInput(array(
      'default'   => 1,
    ));
    $this->validatorSchema['interval'] = new sfValidatorInteger(array(
      'required' => false,
    ));
    return $this;
  }
  public function addGroupsCriteria()
  {
    $this->widgetSchema   ['groups_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Group',
      'multiple' => true,
      'order_by' => array('sf_guard_user_id ASC, name',''),
      'label' => 'Groups',
    ));
    $this->validatorSchema['groups_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model' => 'Group',
      'multiple' => true,
    ));
    return $this;
  }
  public function addApproachCriteria()
  {
    $this->widgetSchema   ['approach'] = new sfWidgetFormChoice(array(
      'choices' => $arr = array(
        '' => 'By contacts',
        'by-tickets' => 'By tickets',
        'financial' => 'Financial',
      ),
      'label' => 'Type of approach',
    ));
    $this->validatorSchema['approach'] = new sfValidatorChoice(array(
      'required' => false,
      'choices' => array_keys($arr),
    ));
    return $this;
  }
  public function addOnlyWhatCriteria()
  {
    $this->widgetSchema   ['only_what'] = new sfWidgetFormChoice(array(
      'choices' => $arr = array(
        '' => 'Everybody',
        'individuals' => 'Individuals',
        'professionals' => 'Professionals',
      ),
      'label' => 'Type of contact',
    ));
    $this->validatorSchema['approach'] = new sfValidatorChoice(array(
      'required' => false,
      'choices' => array_keys($arr),
    ));
    return $this;
  }
  public function addStrictContactsCriteria()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    $this->widgetSchema   ['strict_contacts'] = new sfWidgetFormChoice(array(
      'choices' => array('0' => __('no',null,'sf_admin'), '1' => __('yes',null,'sf_admin')),
      'label' => 'Counting only contacts (not family members)',
    ));
    $this->validatorSchema['strict_contacts'] = new sfValidatorBoolean(array(
      'required' => false,
      'true_values' => array('1'),
    ));
    return $this;
  }
  
  public function removeDatesCriteria()
  {
    unset($this->widgetSchema['dates'], $this->validatorSchema['dates']);
    return $this;
  }
}
