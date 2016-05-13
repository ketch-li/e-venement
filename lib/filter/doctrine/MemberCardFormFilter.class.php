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

    $this->widgetSchema   ['created_at'] = new sfWidgetFormFilterDate(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'with_empty'=> false,
    ));
    $this->validatorSchema['created_at'] = new sfValidatorDateRange(array(
      'from_date'     => new sfValidatorDate(array(
        'required'    => false,
        'date_output' => 'Y-m-d',
        'with_time'   => false,
      )),
      'to_date'       => new sfValidatorDate(array(
        'required'    => false,
        'date_output' => 'Y-m-d',
        'with_time'   => false,
      )),
      'required' => false,
    ));
  }

  public function getFields()
  {
    $fields = parent::getFields();
    $fields['has_email_address'] = 'HasEmailAddress';
    $fields['created_at']   = 'CreatedAt';
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

  public function addCreatedAtColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    $fieldName = 'created_at';

    if (null !== $values['from'] && null !== $values['to'])
    {
      $q->andWhere(sprintf('%s.%s >= ?', $a, $fieldName), $values['from'])
        ->andWhere(sprintf('%s.%s <= ?', $a, $fieldName), $values['to']);
    }
    else if (null !== $values['from'])
    {
      $q->andWhere(sprintf('%s.%s >= ?', $a, $fieldName), $values['from']);
    }
    else if (null !== $values['to'])
    {
      $q->andWhere(sprintf('%s.%s <= ?', $a, $fieldName), $values['to']);
    }

    return $q;
  }
}
