<?php

/**
 * Manifestation filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ManifestationFormFilter extends BaseManifestationFormFilter
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->widgetSchema['organizers_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    $this->widgetSchema['event_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Event',
      'url'   => url_for('event/ajax?with_meta_event=1'),
    ));
    
    $this->widgetSchema['happens_at']->setOption('template', '<span class="from">'.__('From %from_date%').'</span> <span class="to">'.__('to %to_date%').'</span>');
    
    $this->widgetSchema['location_id']->setOption('order_by', array('l.place DESC, l.rank, l.name',''))
      ->setOption('query', Doctrine::getTable('Location')->retrievePlaces());
    $this->widgetSchema['booking_list']->setOption('order_by', array('place ASC, name',''));
    
    $this->widgetSchema ['meta_event_id'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'MetaEvent',
      'order_by'  => array('name',''),
      'multiple'  => true,
      'query'     => $q = Doctrine::getTable('MetaEvent')->createQuery('me')->andWhereIn('me.id',array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials())),
    ));
    $this->validatorSchema['meta_event_id'] = new sfValidatorDoctrineChoice(array(
      'model'     => 'MetaEvent',
      'multiple'  => true,
      'query'     => $q,
      'required'  => false,
    ));
    
    $this->widgetSchema   ['workspace_id'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'Workspace',
      'order_by'  => array('name',''),
      'multiple'  => true,
      'query'     => $q = Doctrine::getTable('Workspace')->createQuery('ws')->andWhereIn('ws.id',array_keys(sfContext::getInstance()->getUser()->getWorkspacesCredentials())),
    ));
    $this->validatorSchema['workspace_id'] = new sfValidatorDoctrineChoice(array(
      'model'     => 'Workspace',
      'multiple'  => true,
      'query'     => $q,
      'required'  => false,
    ));
    
    $this->widgetSchema   ['event_categories_list'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'EventCategory',
      'order_by'  => array('name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['event_categories_list'] = new sfValidatorDoctrineChoice(array(
      'model'     => 'EventCategory',
      'multiple'  => true,
      'required'  => false,
    ));
    
    $this->widgetSchema   ['prices_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Price',
      'url'   => url_for('price/ajax'),
    ));
    $this->validatorSchema['prices_list'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Price',
      'multiple' => true,
      'required' => false,
    ));
    
    $this->widgetSchema['color_id']->setOption('method', 'getName');
    
    $this->widgetSchema   ['has_description'] =
    $this->widgetSchema   ['has_extra_infos'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array('' => 'yes or no', 1 => 'yes', 0 => 'no'),
    ));
    $this->validatorSchema['has_extra_infos'] =
    $this->validatorSchema['has_description'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
    ));
    
    $this->widgetSchema   ['participants_list']->setOption('query', Doctrine::getTable('Contact')->createQuery('c')
      ->leftJoin('c.InvolvedIn ii')
      ->andWhere('ii.id IS NOT NULL')
    );
    $this->validatorSchema['participants_list']->setOption('query', $this->widgetSchema['participants_list']->getOption('query'));
    
    $this->widgetSchema   ['day_of_the_week'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array(
        '' => '',
        1 => __('Monday', null, 'generic'),
        2 => __('Tuesday', null, 'generic'),
        3 => __('Wednesday', null, 'generic'),
        4 => __('Thrusday', null, 'generic'),
        5 => __('Friday', null, 'generic'),
        6 => __('Saturday', null, 'generic'),
        0 => __('Sunday', null, 'generic')
      ),
      'multiple' => true,
    ));
    $this->validatorSchema['day_of_the_week'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
      'multiple' => true,
    ));
    
    $this->widgetSchema   ['gauge_state'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array(
        '' => '',
        'online' => __('Online'),
        '!online' => __('Offline'),
        'onsite' => __('Onsite'),
        '!onsite' => __('Offsite'),
      ),
      'multiple' => true,
    ));
    $this->validatorSchema['gauge_state'] = new sfValidatorChoice(array(
      'choices' => array_keys($choices),
      'required' => false,
      'multiple' => true,
    ));
  }

  public function getFields()
  {
    $arr = parent::getFields();
    unset($arr['location_id']);
    return array_merge($arr,array(
      'event_categories_list' => 'EventCategoriesList',
      'meta_event_id'   => 'MetaEventId',
      'workspace_id'    => 'WorkspaceId',
      'has_extra_infos' => 'HasExtraInfos',
      'has_description' => 'HasDescription',
      'location_id'     => 'LocationId',
      'day_of_the_week' => 'DayOfTheWeek',
    ));
  }
  
  public function addPricesListColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    if ( !is_array($values) )
      $values = array($values);
    
    return $q
      ->leftJoin('g.PriceGauges pg')
      ->andWhere('(TRUE')
      ->andWhereIn('pm.price_id', $values)
      ->orWhereIn('pg.price_id', $values)
      ->andWhere('TRUE)')
    ;
  }
  public function addDayOfTheWeekColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !is_array($values) )
    {
      if ( $values === '' )
        return $q;
      $values = array($values);
    }
    
    if (( $key = array_search('', $values) ) !== false )
      unset($values[$key]);
    if ( !$values )
      return $q;
    
    $a = $q->getRootAlias();
    $q->andWhereIn("EXTRACT(DOW FROM $a.happens_at)", $values);
    return $q;
  }
  
  public function addGaugeStateColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !is_array($values) )
    {
      if ( $values === '' )
        return $q;
      $values = array($values);
    }
    
    if (( $key = array_search('', $values) ) !== false )
      unset($values[$key]);
    if ( !$values )
      return $q;
    
    $a = $q->getRootAlias();
    if ( in_array('online', $values) )
      $q->andWhere('g.online = ?', true);
    if ( in_array('!online', $values) )
      $q->andWhere('g.online = ?', false);
    if ( in_array('onsite', $values) )
      $q->andWhere('g.onsite = ?', true);
    if ( in_array('!onsite', $values) )
      $q->andWhere('g.onsite = ?', false);
    return $q;
  }
  
  // improving how happens_at values are used to be consistent w/ other filters of the software
  public function addHappensAtColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if (!( isset($value) && is_array($value) && (isset($value['from']) && $value['from'] || isset($value['to']) && $value['to']) ))
      return $q;
    
    if ( isset($value['from']) && $value['from'] )
      $q->andWhere("$a.happens_at >= ?", date('Y-m-d', strtotime($value['from'])));
    if ( isset($value['to']) && $value['to'] )
      $q->andWhere("$a.happens_at < ?", date('Y-m-d', strtotime($value['to'])));
    
    return $q;
  }
  public function addLocationIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value || $field != 'location_id' )
      return $q;
    
    $a = $q->getRootAlias();
    
    if ( !$q->contains("LEFT JOIN $a.LocationBooking LocationBooking") )
      $q->leftJoin("$a.LocationBooking LocationBooking");
    $q->andWhere('(TRUE')
      ->andWhere("$a.$field = ?",$value)
      ->orWhere("LocationBooking.$field = ?",$value)
      ->andWhere('TRUE)');
    
    return $q;
  }
  
  public function addMetaEventIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value || $field != 'meta_event_id' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( is_array($value) )
      $q->andWhereIn("e.$field", $value);
    else
      $q->andWhere("e.$field = ?", $value);
    
    return $q;
  }
  
  public function addEventCategoriesListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value )
      return $q;
    
    if ( !$q->contains('e.EventCategory ec') )
      $q->leftJoin('e.EventCategory ec');
    
    $q->andWhereIn('ec.id', $value);
    
    return $q;
  }
  
  public function addWorkspaceIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value || $field != 'workspace_id' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( is_array($value) )
      $q->andWhereIn("g.$field", $value);
    else
      $q->andWhere("g.$field = ?", $value);
    
    return $q;
  }
  
  public function addHasDescriptionColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value === '0' )
      $q->andWhere("$a.description IS NULL OR trim($a.description) = ?",'');
    elseif ( $value === '1' )
      $q->andWhere("$a.description IS NOT NULL AND trim($a.description) != ?",'');
    
    return $q;
  }
  public function addHasExtraInfosColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value === '0' )
      $q->andWhere("$a.id NOT IN (SELECT DISTINCT mei.manifestation_id FROM ManifestationExtraInformation mei)");
    elseif ( $value === '1' )
      $q->andWhere("$a.id IN (SELECT DISTINCT mei.manifestation_id FROM ManifestationExtraInformation mei)");
    
    return $q;
  }
}
