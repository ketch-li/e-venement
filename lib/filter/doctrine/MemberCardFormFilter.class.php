<?php

/**
 * MemberCard filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MemberCardFormFilter extends BaseMemberCardFormFilter
{
  public function configure()
  {
    $this->widgetSchema   ['has_email_address'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array('n-a' => 'yes or no', 'yes' => 'yes', 'no' => 'no'),
    ));
    $this->validatorSchema['has_email_address'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
    ));
  }
  
  public function setup()
  {
    $this->noTimestampableUnset = true;
    parent::setup();
  }

  public function getFields()
  {
    $fields = parent::getFields();
    $fields['has_email_address'] = 'HasEmailAddress';
    return $fields;
  }

  public function addHasEmailAddressColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value || $value == 'n-a' )
      return $q;

    switch ( $value ) {
    case 'yes':
      $q->andWhere('c.email IS NOT NULL');
      break;
    case 'no':
      $q->andWhere('c.email IS NULL');
      break;
    }

    return $q;
  }
}
