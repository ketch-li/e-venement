<?php

/**
 * Email filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EmailFormFilter extends BaseEmailFormFilter
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
    // organism
    $this->widgetSchema['organisms_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => url_for('organism/ajax'),
      'order_by' => array('name',''),
    ));
    $this->widgetSchema['contacts_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Contact',
      'url'   => url_for('contact/ajax'),
      'order_by' => array('name,firstname',''),
    ));
    $this->widgetSchema['professionals_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Professional',
      'url'   => url_for('professional/ajax'),
      'method'=> 'getFullName',
      'order_by' => array('c.name,c.firstname,o.name,t.name,p.name',''),
    ));
    
    $this->widgetSchema['sf_guard_user_id']->setOption('order_by',array('first_name, last_name, username',''));
    
    $this->widgetSchema   ['email_address'] = new sfWidgetFormInput(array(
      'type' => 'email',
    ));
    $this->validatorSchema['email_address'] = new sfValidatorEmail(array(
      'required' => false,
    ));
    
    $this->widgetSchema   ['with_attachments'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array('' => '', 'yes' => __('yes',null,'sf_admin'), 'no' => __('no',null,'sf_admin')),
    ));
    $this->validatorSchema['with_attachments'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
    ));
  }
  
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['email_address'] = 'EmailAddress';
    return $fields;
  }
  
  public function addWithAttachmentsColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value )
      return $q;
    
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Attachments att")
      ->andWhere($value == 'no' ? 'att.id IS NULL' : 'att.id IS NOT NULL');
    
    return $q;
  }
  public function addEmailAddressColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value )
      return $q;
    
    $a = $q->getRootAlias();
    $q->andWhere("LOWER($a.field_to) = LOWER(?) OR LOWER($a.field_cc) = LOWER(?) OR LOWER($a.field_bcc) = LOWER(?)", array($value, $value, $value));
    
    return $q;
  }
}
