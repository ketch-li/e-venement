<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * Contact filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContactFormFilter extends BaseContactFormFilter
{
  protected $noTimestampableUnset = true;
  protected $showProfessionalData = true;
  protected $tickets_having_query = NULL; // Doctrine_Query
  protected $grpintersection = false;

  /**
   * @see AddressableFormFilter
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
    $this->tickets_having_query = Doctrine_Query::create()->from('Contact hqc')
      ->groupBy('hqc.id')
      ->select('hqc.id');
    
    $this->widgetSchema   ['groups_intersection'] = new sfWidgetFormInputCheckbox(array(
      'value_attribute_value' => 1,
    ));
    $this->validatorSchema['groups_intersection'] = new sfValidatorBoolean(array(
      'true_values' => array('1'),
    ));
    $this->widgetSchema['groups_list']->setOption(
      'order_by', array('u.id IS NULL DESC, u.username, name','')
    )->setOption(
      'query', Doctrine::getTable('Group')->createQuery('g')->select('g.*'));
    
    $this->widgetSchema['emails_list']->setOption('query',Doctrine::getTable('Email')
      ->createQuery()
      ->andWhere('sent')
      ->orderBy('updated_at DESC')
      ->limit(30)
    );
    
    $this->widgetSchema['culture'] = new sfWidgetFormChoice(array(
      'choices' => array('' => '') + sfConfig::get('project_internals_cultures', array()),
    ));
    
    // has postal address ?
    $this->widgetSchema   ['has_address'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['has_address'] = $this->validatorSchema['npai'];
    
    // has postal address ?
    $this->widgetSchema   ['has_category'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['has_category'] = $this->validatorSchema['npai'];
    
    // has email address ?
    $this->widgetSchema   ['has_email'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['has_email'] = $this->validatorSchema['npai'];
    
    // no newsletter ?
    $this->widgetSchema   ['email_newsletter'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['email_newsletter'] = $this->validatorSchema['npai'];
    
    // organism
    $this->widgetSchema   ['organism_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Organism',
      'url'   => url_for('organism/ajax'),
    ));
    $this->validatorSchema['organism_id'] = new sfValidatorInteger(array('required' => false));
    
    // organism category
    $this->widgetSchema   ['organism_category_id'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'OrganismCategory',
      'order_by'  => array('name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['organism_category_id'] = new sfValidatorDoctrineChoice(array(
      'model'    => 'OrganismCategory',
      'required' => false,
      'multiple' => true,
    ));
    
    // professional type
    $this->widgetSchema   ['professional_type_id'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'ProfessionalType',
      'multiple'  => true,
      'order_by'  => array('name',''),
    ));
    $this->validatorSchema['professional_type_id'] = new sfValidatorDoctrineChoice(array(
      'model'    => 'ProfessionalType',
      'required' => false,
      'multiple' => true,
    ));
    $this->widgetSchema   ['has_professional_type_id'] = new sfWidgetFormChoice(array(
      'choices' => $arr = array('' => 'yes or no', 0 => 'no', 1 => 'yes'),
    ));
    $this->validatorSchema['has_professional_type_id'] = new sfValidatorChoice(array(
      'choices' => array_keys($arr),
      'required' => false,
    ));
    // organism's prefered contact
    $this->widgetSchema   ['organism_professional_id'] = new sfWidgetFormChoice(array(
      'choices' => $arr = array(
        '' => '',
        1  => 'yes',
      ),
    ));
    $this->validatorSchema['organism_professional_id'] = new sfValidatorChoice(array(
      'choices' => array_keys($arr),
      'required' => false,
    ));
    
    $this->widgetSchema   ['not_groups_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Group',
      'url'   => cross_app_url_for('rp', 'group/ajax'),
      'config' => '{ max: 300 }',
    ));
    $this->validatorSchema['not_groups_list'] = $this->validatorSchema['groups_list'];
    
    $years = sfContext::getInstance()->getConfiguration()->yob;
    $script = <<<EOF
    <script type="text/javascript"><!-- $(document).ready(function(){
      $('[name="contact_filters[YOB][from][year]"], [name="contact_filters[YOB][to][year]"]').change(function(){
        $(this).siblings().val($(this).val() ? 1 : '');
      });
    }); --></script>
EOF;
    $this->widgetSchema   ['YOB'] = new sfWidgetFormFilterDate(array(
      'from_date'=> new sfWidgetFormDate(array(
        'format' => '%year% %month% %day%',
        'years'  => $years,
      )),
      'to_date'   => new sfWidgetFormDate(array(
        'format' => '%year% %month% %day%',
        'years'  => $years,
      )),
      'with_empty'=> false,
      'template'  => '<span class="from_year">'.__('From %from_date%').'</span> <span class="to_year">'.__('to %to_date%').'</span>'.$script,
    ));
    $this->validatorSchema['YOB'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate(array('required' => false,)),
      'to_date'   => new sfValidatorDate(array('required' => false,)),
      'required'  => false,
    ));
    
    // events
    $this->widgetSchema   ['manifestations_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event', 'manifestation/ajax'),
      'config' => '{ max: 25 }',
    ));
    $this->validatorSchema['manifestations_list'] = new sfValidatorDoctrineChoice(array(
      'model'    => 'Manifestation',
      'multiple' => true,
      'required' => false,
    ));
    $this->widgetSchema   ['events_list'] = new sfWidgetFormDoctrineChoice(array(
      'model'    => 'Event',
      'query'    => Doctrine::getTable('Event')->retrieveList()->select('e.*, translation.*'),
      'order_by' => array('translation.name','asc'),
      'multiple' => true,
    ));
    $this->validatorSchema['events_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'query'    => $this->widgetSchema['events_list']->getOption('query'),
      'model'    => 'Event',
      'multiple' => true,
    ));
    $this->widgetSchema   ['event_categories_list'] = new sfWidgetFormDoctrineChoice(array(
      'model'    => 'EventCategory',
      'order_by' => array('name','asc'),
      'multiple' => true,
    ));
    $this->validatorSchema['event_categories_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model'    => 'EventCategory',
      'multiple' => true,
    ));
    $this->widgetSchema   ['meta_events_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MetaEvent',
      'query' => $q = Doctrine::getTable('MetaEvent')->createQuery('me')
        ->andWhereIn('me.id',array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials())),
      'order_by' => array('name','asc'),
      'multiple' => true,
    ));
    $this->validatorSchema['meta_events_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model'    => 'MetaEvent',
      'query' => $q,
      'multiple' => true,
    ));
    $this->widgetSchema   ['prices_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Price',
      'query' => $q = Doctrine::getTable('Price')->createQuery('p')
        ->leftJoin('p.Users u')
        ->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId()),
      'order_by' => array('name, description',''),
      'multiple' => true,
    ));
    $this->validatorSchema['prices_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model'    => 'Price',
      'query' => $q,
      'multiple' => true,
    ));
    $this->widgetSchema   ['workspaces_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Workspace',
      'query' => $q = Doctrine::getTable('Workspace')->createQuery('ws')
        ->andWhereIn('ws.id', array_keys(sfContext::getInstance()->getUser()->getWorkspacesCredentials())),
      'order_by' => array('name',''),
      'multiple' => true,
    ));
    $this->validatorSchema['workspaces_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model'    => 'Workspace',
      'query'    => $q,
      'multiple' => true,
    ));
    
    $this->widgetSchema   ['event_archives'] = new sfWidgetFormChoice($opt = array(
      'choices' => $choices = $this->getEventArchivesChoices(),
      'multiple' => true,
    ));
    $this->validatorSchema['event_archives'] = new sfValidatorChoice(array_merge($opt,array(
      'required' => false,
    )));
    
    $this->widgetSchema   ['tickets_amount_min'] = new sfWidgetFormInput();
    $this->validatorSchema['tickets_amount_min'] = new sfValidatorInteger(array(
      'required' => false,
    ));
    $this->widgetSchema   ['tickets_amount_max'] = new sfWidgetFormInput();
    $this->validatorSchema['tickets_amount_max'] = new sfValidatorInteger(array(
      'required' => false,
    ));
    
    // seats rank
    $this->widgetSchema   ['tickets_best_rank'] = new sfWidgetFormInput;
    $this->validatorSchema['tickets_best_rank'] = new sfValidatorInteger(array(
      'required' => false,
    ));
    $this->widgetSchema   ['tickets_rank_operand'] = new sfWidgetFormChoice(array(
      'choices' => $arr = array(
        '<=' => 'Less or equal',
        '=' => 'Equal',
        '>=' => 'Equal or more',
      ),
    ));
    $this->validatorSchema['tickets_rank_operand'] = new sfValidatorChoice(array(
      'choices' => array_keys($arr),
      'required' => false,
    ));
    $this->widgetSchema   ['tickets_avg_rank'] = new sfWidgetFormInput;
    $this->validatorSchema['tickets_avg_rank'] = new sfValidatorInteger(array(
      'required' => false,
    ));
    
    //cards
    $this->widgetSchema   ['member_cards'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MemberCardType',
      'order_by' => array('name','asc'),
      'multiple' => true,
    ));
    $this->validatorSchema['member_cards'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'multiple' => true,
      'model' => 'MemberCardType',
    ));
    $this->widgetSchema   ['member_cards_detail'] = new sfWidgetFormInputText;
    $this->validatorSchema['member_cards_detail'] = new sfValidatorString(array(
      'required' => false,
    ));
    $this->widgetSchema   ['member_cards_valid_at'] = new liWidgetFormJQueryDateText(array(
      'culture' => sfContext::getInstance()->getUser()->getCulture(),
    ));
    $this->validatorSchema['member_cards_valid_at'] = new sfValidatorDate(array(
      'required' => false,
    ));
    $this->widgetSchema   ['member_cards_not_valid_at'] = new liWidgetFormJQueryDateText(array(
      'culture' => sfContext::getInstance()->getUser()->getCulture(),
    ));
    $this->validatorSchema['member_cards_not_valid_at'] = new sfValidatorDate(array(
      'required' => false,
    ));
    $this->widgetSchema   ['member_cards_only_last'] = new sfWidgetFormChoice(array(
      'choices' => array('0' => 'no', '1' => 'yes'),
    ));
    $this->validatorSchema['member_cards_only_last'] = new sfValidatorBoolean(array(
      'true_values' => array('1'),
    ));
    
    // flow control
    $this->widgetSchema   ['control_manifestation_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Manifestation',
      'query' => $q = Doctrine::getTable('Manifestation')->createQuery('m')->select('m.*, e.*')->leftJoin('e.Checkpoints cp')->andWhere('cp.id IS NOT NULL'),
      'multiple'  => true,
    ));
    $this->validatorSchema['control_manifestation_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'query' => $q,
      'required' => false,
      'multiple'  => true,
    ));
    $this->widgetSchema   ['control_checkpoint_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Checkpoint',
      'multiple'  => true,
    ));
    $this->validatorSchema['control_checkpoint_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Checkpoint',
      'required' => false,
      'multiple'  => true,
    ));
    $this->widgetSchema   ['control_created_at'] = new sfWidgetFormFilterDate(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'with_empty'=> false,
    ));
    $this->validatorSchema['control_created_at'] = new sfValidatorDateRange(array(
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
    
    $this->widgetSchema   ['region_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'GeoFrRegion',
      'order_by' => array('name',''),
      'add_empty' => true,
    ));
    $this->validatorSchema['region_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'GeoFrRegion',
      'required' => false,
    ));
    
    $this->widgetSchema   ['survey_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Survey',
      'url'   => cross_app_url_for('srv', 'survey/ajax'),
    ));
    $this->validatorSchema['survey_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Survey',
      'required' => false,
    ));
    $this->widgetSchema   ['survey_query_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'SurveyQuery',
      'url'   => cross_app_url_for('srv', 'query/ajax'),
    ));
    $this->validatorSchema['survey_query_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'SurveyQuery',
      'required' => false,
    ));
    $this->widgetSchema   ['survey_answer']   = new sfWidgetFormInput;
    $this->validatorSchema['survey_answer'] = new sfValidatorPass(array('required' => false,));
    
    parent::configure();
  }
  
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['postalcode']           = 'Postalcode';
    $fields['YOB']                  = 'YOB';
    $fields['not_contacts_list']    = 'NotContactsList';
    $fields['not_professionals_list'] = 'NotProfessionalsList';
    $fields['organism_id']          = 'OrganismId';
    $fields['organism_category_id'] = 'OrganismCategoryId';
    $fields['organism_professional_id']   = 'OrganismProfessionalId';
    $fields['professional_type_id']       = 'ProfessionalTypeId';
    $fields['has_professional_type_id']   = 'HasProfessionalTypeId';
    $fields['has_email']            = 'HasEmail';
    $fields['email_newsletter']     = 'EmailNewsletter';
    $fields['has_address']          = 'HasAddress';
    $fields['has_category']         = 'HasCategory';
    $fields['groups_list']          = 'GroupsList';
    $fields['not_groups_list']      = 'NotGroupsList';
    $fields['emails_list']          = 'EmailsList';
    $fields['manifestations_list']  = 'ManifestationsList';
    $fields['events_list']          = 'EventsList';
    $fields['event_categories_list']= 'EventCategoriesList';
    $fields['meta_events_list']     = 'MetaEventsList';
    $fields['workspaces_list']      = 'WorkspacesList';
    $fields['event_archives']       = 'EventArchives';
    $fields['prices_list']          = 'PricesList';
    $fields['tickets_best_rank']    = 'TicketsBestRank';
    $fields['tickets_avg_rank']     = 'TicketsAvgRank';
    $fields['member_cards']         = 'MemberCards';
    $fields['member_cards_valid_at']      = 'MemberCardsValidAt';
    $fields['member_cards_not_valid_at']  = 'MemberCardsNotValidAt';
    $fields['member_cards_only_last']     = 'MemberCardsOnlyLast';
    $fields['control_manifestation_id']   = 'ControlManifestationId';
    $fields['control_checkpoint_id']      = 'ControlCheckpointId';
    $fields['control_created_at']   = 'ControlCreatedAt';
    $fields['region']               = 'RegionId';
    $fields['survey_id']            = 'SurveyId';
    $fields['survey_query_id']      = 'SurveyQueryId';
    $fields['survey_answer']        = 'SurveyAnswer';
    
    // must be the last ones, because of a having() part which needs to be added lately
    $fields['tickets_amount_min']   = 'TicketsAmountMin';
    $fields['tickets_amount_max']   = 'TicketsAmountMax';
    
    return $fields;
  }
  
  public function addCultureColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value )
      return $q;
    
    $a = $q->getRootAlias();
    $q->andWhere("$a.culture = ?", $value);
    
    return $q;
  }
  public function addNotContactsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( $value )
    if ( count($value) > 0 )
      $q->andWhereNotIn("$a.id",$value);
    
    return $q;
  }
  public function addNotProfessionalsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    // remove completly a contact from a list if it's got only one "professional" and this one is selected for removal
    if ( $value )
    if ( count($value) > 0 )
      $q->andWhere('(TRUE')
        ->andWhereNotIn('p.id',$value)
        ->orWhere('p.id IS NULL')
        ->andWhere('TRUE)');
    
    return $q;
  }
  public function addRegionIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( intval($value) > 0 )
      $q->andWhere("SUBSTRING($a.postalcode,1,2) IN (SELECT REGEXP_REPLACE(dpt.num, '[a-zA-Z]', '0') FROM GeoFrDepartment dpt LEFT JOIN dpt.Region reg WHERE reg.id = ?)",$value)
        ->andWhere("LOWER($a.country) = ? OR TRIM($a.country) = ? OR $a.country IS NULL",array('france',''));
    
    return $q;
  }
  public function addEmailsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
      $q->leftJoin("$a.Emails ce")
        ->leftJoin("p.Emails pe")
        ->andWhere('(TRUE')
        ->andWhere('ce.sent = TRUE')
        ->andWhereIn('ce.id',$value)
        ->orWhereIn('pe.id',$value)
        ->andWhere('pe.sent = TRUE')
        ->andWhere('TRUE)');
    
    return $q;
  }
  
  // links to the ticketting system module
  public function addManifestationsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !is_array($value) )
      return $q;
    
    $a = $q->getRootAlias();
    
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
      $query->leftJoin('transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$query->contains("LEFT JOIN $a.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)") )
      $query->leftJoin($a.'.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)');
      
      $query->andWhere('(TRUE')
            ->andWhereIn('tck.manifestation_id',$value)
            ->orWhereIn('ctck.manifestation_id', $value)
            ->andWhere('TRUE)');
    }
    
    return $q;
  }
  public function addEventsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !is_array($value) )
      return $q;
    
    $a = $q->getRootAlias();
    
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
      $query->leftJoin('transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN tck.Manifestation m") )
      $query->leftJoin('tck.Manifestation m');
      
      if ( !$query->contains("LEFT JOIN $a.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)") )
      $query->leftJoin($a.'.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN ctck.Manifestation cm") )
      $query->leftJoin('ctck.Manifestation cm');
      
      $query->andWhere('(TRUE')
            ->andWhereIn('m.event_id',$value)
            ->orWhereIn('cm.event_id', $value)
            ->andWhere('TRUE)');
    }
    
    return $q;
  }
  public function addEventCategoriesListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
      $query->leftJoin('transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN tck.Manifestation m") )
      $query->leftJoin('tck.Manifestation m');
      if ( !$query->contains("LEFT JOIN m.Event event") )
      $query->leftJoin('m.Event event');
      
      if ( !$query->contains("LEFT JOIN $a.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)") )
      $query->leftJoin($a.'.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN ctck.Manifestation cm") )
      $query->leftJoin('ctck.Manifestation cm');
      if ( !$query->contains("LEFT JOIN cm.Event cevent") )
      $query->leftJoin('m.Event cevent');
     
      $query->andWhere('(TRUE')
            ->andWhereIn('event.event_category_id',$value)
            ->orWhereIn('cevent.event_category_id',$value)
            ->andWhere('TRUE)');
    }
    
    return $q;
  }
  public function addMetaEventsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
      $query->leftJoin('transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN tck.Manifestation m") )
      $query->leftJoin('tck.Manifestation m');
      if ( !$query->contains("LEFT JOIN m.Event event") )
      $query->leftJoin('m.Event event');
      if ( !$query->contains("LEFT JOIN event.MetaEvent mev") )
      $query->leftJoin('event.MetaEvent mev');
      
      if ( !$query->contains("LEFT JOIN $a.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)") )
      $query->leftJoin($a.'.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN ctck.Manifestation cm") )
      $query->leftJoin('ctck.Manifestation cm');
      if ( !$query->contains("LEFT JOIN cm.Event cevent") )
      $query->leftJoin('cm.Event cevent');
      if ( !$query->contains("LEFT JOIN cevent.MetaEvent cmev") )
      $query->leftJoin('cevent.MetaEvent cmev');
      
      $query->andWhere('(TRUE')
            ->andWhereIn('mev.id',$value)
            ->orWhereIn('cmev.id',$value)
            ->andWhere('TRUE)');
    }
    
    return $q;
  }
  public function addWorkspacesListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
      $query->leftJoin('transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN tck.Gauge g") )
      $query->leftJoin('tck.Gauge g');
      
      if ( !$query->contains("LEFT JOIN $a.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)") )
      $query->leftJoin($a.'.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN ctck.Gauge cg") )
      $query->leftJoin('ctck.Gauge cg');
      
      $query->andWhere('(TRUE')
            ->andWhereIn('g.workspace_id',$value)
            ->orWhereIn('cg.workspace_id',$value)
            ->andWhere('TRUE)');
    }
    
    return $q;
  }
  
  public function addTicketsAvgRankColumnQuery(Doctrine_Query $q, $field, $value)
  { return $this->addTicketsCommonRankColumnQuery($q, 'AVG', $value, 'tar'); }
  public function addTicketsBestRankColumnQuery(Doctrine_Query $q, $field, $value)
  { return $this->addTicketsCommonRankColumnQuery($q, 'MIN', $value, 'tbr'); }
  protected function addTicketsCommonRankColumnQuery(Doctrine_Query $q, $sql_fct, $value, $tbl_prefix)
  {
    $a = $q->getRootAlias();
    
    if ( $value )
    {
      $operand = $this->values['tickets_rank_operand'];
      $operand = in_array($operand, array('<=', '=', '>=')) ? $operand : '<=';
      
      $q1 = Doctrine::getTable('Ticket')->createQueryPreparedForRanks($tbl_prefix)
        ->having("{$sql_fct}({$tbl_prefix}_s.rank) $operand $value")
        ->select("{$tbl_prefix}_t.contact_id")
        ->groupBy("{$tbl_prefix}_t.contact_id")
      ;
      $tbl_prefix = $a.$tbl_prefix;
      $q2 = Doctrine::getTable('Ticket')->createQueryPreparedForRanks($tbl_prefix)
        ->having("{$sql_fct}({$tbl_prefix}_s.rank) $operand $value")
        ->select("{$tbl_prefix}.contact_id")
        ->groupBy("{$tbl_prefix}.contact_id")
      ;
      $q->andWhere("$a.id IN ($q1) OR $a.id IN ($q2)");
    }
    
    return $q;
  }

  public function addPricesListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac WITH (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
      $query->leftJoin('transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN tck.Price price") )
      $query->leftJoin('tck.Price price');
      
      if ( !$query->contains("LEFT JOIN $a.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)") )
      $query->leftJoin($a.'.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)');
      if ( !$query->contains("LEFT JOIN ctck.Price cprice") )
      $query->leftJoin('ctck.Price cprice');
      
      $query->andWhere('(TRUE')
            ->andWhereIn('price.id',$value)
            ->orWhereIn('cprice.id',$value)
            ->andWhere('TRUE)');
    }
    
    return $q;
  }
  
  public function addEventArchivesColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    {
      if ( !$q->contains("LEFT JOIN $a.EventArchives ea") )
      $q->leftJoin("$a.EventArchives ea");
      
      $q->andWhereIn('ea.name',$value);
    }
    
    return $q;
  }

  // having queries
  public function addTicketsAmountMinColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    $prefix = 'hq';
    
    if ( $value )
    {
      if ( $q->contains("LEFT JOIN $a.Groups gc") || $q->contains("LEFT JOIN p.Groups gp") )
      {
        if ( sfContext::hasInstance() )
        {
          sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
          sfContext::getInstance()->getUser()->setFlash('notice', __('For global efficiency needs, your search was truncated of its criteria related to the amount spent in ticketting. To avoid this, remove your criteria on groups.'));
        }
        return $q;
      }
      
      foreach ( array('' => $q, $prefix => $this->tickets_having_query) as $p => $query )
      {
        $r = $query->getRootAlias();
        
        if ( !$query->contains("LEFT JOIN $r.Professionals ".$p."p") )
        $query->leftJoin("$r.Professionals ".$p."p");
        
        if ( !$query->contains("LEFT JOIN $r.Transactions ".$p."transac ON ".$p."$r.id = ".$p."transac.contact_id AND (".$p."p.id = ".$p."transac.professional_id OR ".$p."transac.professional_id IS NULL)") )
        $query->leftJoin("$r.Transactions ".$p."transac ON $r.id = ".$p."transac.contact_id AND (".$p."p.id = ".$p."transac.professional_id OR ".$p."transac.professional_id IS NULL)");
        
        if ( !$query->contains("LEFT JOIN ".$p."transac.Tickets ".$p."tck ON ".$p."transac.id = ".$p."tck.transaction_id AND (tck.printed_at IS NOT NULL OR ".$p."tck.integrated_at IS NOT NULL) AND ".$p."tck.id NOT IN (SELECT ".$p."ttck.cancelling FROM ticket ".$p."ttck WHERE ".$p."ttck.cancelling IS NOT NULL)") )
        $query->leftJoin($p."transac.Tickets ".$p."tck ON ".$p."transac.id = ".$p."tck.transaction_id AND (".$p."tck.printed_at IS NOT NULL OR ".$p."tck.integrated_at IS NOT NULL) AND ".$p."tck.id NOT IN (SELECT ".$p."ttck.cancelling FROM ticket ".$p."ttck WHERE ".$p."ttck.cancelling IS NOT NULL)");
      }
      
      $this->tickets_having_query->having("sum(".$p."tck.value) >= ?", $value);
      foreach ( $this->tickets_having_query->fetchArray() as $c )
        $ids[] = $c['id'];
      
      //$q->andWhere("$a.id IN (".$this->tickets_having_query.")", $value);
      $q->andWhereIn("$a.id", $ids);
    }
    
    return $q;
  }
  public function addTicketsAmountMaxColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( $value )
    {
      if ( $q->contains("LEFT JOIN $a.Groups gc") || $q->contains("LEFT JOIN p.Groups gp") )
      {
        if ( sfContext::hasInstance() )
        {
          sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
          sfContext::getInstance()->getUser()->setFlash('notice', __('For global efficiency needs, your search was truncated of its criteria related to the amount spent in ticketting. To avoid this, remove your criteria on groups.'));
        }
        return $q;
      }
      
      foreach ( array('' => $q, $prefix => $this->tickets_having_query) as $p => $query )
      {
        $r = $query->getRootAlias();
        
        if ( !$query->contains("LEFT JOIN $r.Professionals ".$p."p") )
        $query->leftJoin("$r.Professionals ".$p."p");
        
        if ( !$query->contains("LEFT JOIN $r.Transactions ".$p."transac ON ".$p."$r.id = ".$p."transac.contact_id AND (".$p."p.id = ".$p."transac.professional_id OR ".$p."transac.professional_id IS NULL)") )
        $query->leftJoin("$r.Transactions ".$p."transac ON $r.id = ".$p."transac.contact_id AND (".$p."p.id = ".$p."transac.professional_id OR ".$p."transac.professional_id IS NULL)");
        
        if ( !$query->contains("LEFT JOIN ".$p."transac.Tickets ".$p."tck ON ".$p."transac.id = ".$p."tck.transaction_id AND (tck.printed_at IS NOT NULL OR ".$p."tck.integrated_at IS NOT NULL) AND ".$p."tck.id NOT IN (SELECT ".$p."ttck.cancelling FROM ticket ".$p."ttck WHERE ".$p."ttck.cancelling IS NOT NULL)") )
        $query->leftJoin($p."transac.Tickets ".$p."tck ON ".$p."transac.id = ".$p."tck.transaction_id AND (".$p."tck.printed_at IS NOT NULL OR ".$p."tck.integrated_at IS NOT NULL) AND ".$p."tck.id NOT IN (SELECT ".$p."ttck.cancelling FROM ticket ".$p."ttck WHERE ".$p."ttck.cancelling IS NOT NULL)");
      }
      
      $this->tickets_having_query->having("sum(".$p."tck.value) < ?", $value);
      foreach ( $this->tickets_having_query->fetchArray() as $c )
        $ids[] = $c['id'];
      
      //$q->andWhere("$a.id IN (".$this->tickets_having_query.")", $value);
      $q->andWhereIn("$a.id", $ids);
    }
    
    return $q;
  }

  public function addGroupsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) && count($value) )
    {
      if ( !$this->values['groups_intersection'] )
      {
        if ( !$q->contains("LEFT JOIN $a.Groups gc") )
          $q->leftJoin("$a.Groups gc");
        
        if ( !$q->contains("LEFT JOIN p.Groups gp") )
          $q->leftJoin("p.Groups gp");
        
        $q->andWhere('(TRUE')
          ->andWhereIn("gc.id",$value)
          ->orWhereIn("gp.id",$value)
          ->andWhere('TRUE)');
      }
      else
      // if we are looking for the intersection, not the union
      foreach ( $value as $gid )
      {
        $q->andWhere('(TRUE')
          ->andWhere('c.id IN (SELECT s'.$gid.'gc.id FROM Group s'.$gid.'gtc LEFT JOIN s'.$gid.'gtc.Contacts s'.$gid.'gc WHERE s'.$gid.'gtc.id = ?)',$gid)
          ->orWhere('p.id IN (SELECT s'.$gid.'gp.id FROM Group s'.$gid.'gtp LEFT JOIN s'.$gid.'gtp.Professionals s'.$gid.'gp WHERE s'.$gid.'gtp.id = ?)',$gid)
          ->andWhere('TRUE)');
      }
    }
    
    return $q;
  }
  public function addNotGroupsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) && count($value) )
    {
      $q1 = new Doctrine_Query();
      $q1->select('gctmp.contact_id')
        ->from('GroupContact gctmp')
        ->andWhereIn('gctmp.group_id',$value);
      $q2 = new Doctrine_Query();
      $q2->select('gptmp.professional_id')
        ->from('GroupProfessional gptmp')
        ->andWhereIn('gptmp.group_id',$value);
      
      $q->andWhere("$a.id NOT IN ($q1)",$value) // hack for inserting $value
        ->andWhere("p.id IS NULL OR p.id NOT IN ($q2)",$value); // hack for inserting $value
    }
    
    return $q;
  }
  public function addHasCategoryColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.organism_category_id IS NOT NULL AND (o.organism_category_id IS NOT NULL OR o.id IS NULL)");
    else
      return $q->addWhere("$a.organism_category_id IS     NULL AND (o.organism_category_id IS     NULL)");
  }
  public function addHasAddressColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.postalcode IS NOT NULL AND $a.postalcode != '' AND $a.city IS NOT NULL AND $a.postalcode != ''");
    else
      return $q->addWhere("$a.postalcode IS     NULL OR $a.postalcode = '' OR $a.city IS     NULL OR $a.city = ''");
  }
  public function addHasEmailColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.email IS NOT NULL AND $a.email != '' OR p.contact_email IS NOT NULL AND p.contact_email != ''");
    else
      return $q->addWhere("($a.email IS     NULL OR $a.email = '') AND (p.contact_email IS NULL OR p.contact_email = '')");
  }
  public function addEmailNewsletterColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.email_no_newsletter = FALSE OR p.contact_email_no_newsletter IS NOT NULL AND p.contact_email_no_newsletter = TRUE");
    else
      return $q->addWhere("$a.email_no_newsletter = TRUE AND NOT (p.contact_email_no_newsletter IS NOT NULL AND p.contact_email_no_newsletter = TRUE)");
  }
  public function addEmailNpaiColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.email_npai = TRUE OR p.contact_email_npai IS NOT NULL AND p.contact_email_npai = TRUE");
    else
      return $q->addWhere("$a.email_npai = FALSE AND NOT (p.contact_email_npai IS NOT NULL AND p.contact_email_npai = TRUE)");
  }
  public function addNpaiColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    return $q->andWhere("$a.npai = ? AND (o.npai = ? OR o.id IS NULL)", array(
      $value ? true : false,
      $value ? true : false,
    ));
  }
  public function addEmailColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    if (is_array($values) && isset($values['is_empty']) && $values['is_empty'])
      $q->addWhere(sprintf('((%s.email IS NULL OR %1$s.email = ?) AND (p.contact_email IS NULL OR p.contact_email = ?))', $q->getRootAlias()), array('',''));
    else if (is_array($values) && isset($values['text']) && '' != $values['text'])
      $q->addWhere(sprintf('%s.email ILIKE ? OR p.contact_email ILIKE ?', $q->getRootAlias(), 'email'), array('%'.$values['text'].'%', '%'.$values['text'].'%'));
    return $q;
  }
  public function addProfessionalTypeIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value )
    {
      $this->setProfessionalData(true);
      $q->andWhereIn('pt.id',$value);
    }
    return $q;
  }
  public function addHasProfessionalTypeIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value === '0' )
    {
      $this->setProfessionalData(true);
      $q->andWhere('pt.id IS NULL')
        ->andWhere('p.id IS NOT NULL');
    }
    if ( $value === '1' )
    {
      $this->setProfessionalData(true);
      $q->andWhere('pt.id IS NOT NULL');
    }
    return $q;
  }
  public function addOrganismProfessionalIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value )
    {
      $this->setProfessionalData(true);
      $q->andWhere("o.professional_id = p.id");
    }
    return $q;
  }
  public function addOrganismIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $this->setProfessionalData(true);
    $a = $q->getRootAlias();
    if ( $value )
    {
      $this->setProfessionalData(true);
      $q->addWhere("o.id = ?",$value);
    }
    return $q;
  }
  public function addOrganismCategoryIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value )
    {
      $this->setProfessionalData(true);
      $q->andWhere('(TRUE')
        ->andWhereIn('o.organism_category_id',$value)
        ->orWhereIn("$a.organism_category_id",$value)
        ->andWhere('TRUE)');
    }
    return $q;
  }
  public function addYOBColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value['from'] )
      $q->addWhere('y.year >= ?',date('Y',strtotime($value['from'])));
    if ( $value['to'] )
      $q->addWhere('y.year <= ?',date('Y',strtotime($value['to'])));
    
    return $q;
  }
  public function addPostalcodeColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( $value['text'] )
      $q->addWhere("$c.postalcode LIKE ? OR (o.id IS NOT NULL AND o.postalcode LIKE ?)",array($value['text'].'%',$value['text'].'%'));
    
    return $q;
  }
  
  // member cards
  public function addMemberCardsColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( count($value) > 0 )
    {
      if ( !$q->contains("LEFT JOIN $c.MemberCards mc") )
        $q->leftJoin("$c.MemberCards mc");
      $q->andWhereIn("mc.member_card_type_id",$value)
        ->andWhere('mc.active = ?',true);
   }
    
    return $q;
  }
  // member cards
  public function addMemberCardsDetailColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( count($value) > 0 )
    {
      if ( !$q->contains("LEFT JOIN $c.MemberCards mc") )
        $q->leftJoin("$c.MemberCards mc");
      $q->andWhere("mc.detail = ?",$value);
    }
    return $q;
  }
  public function addMemberCardsValidAtColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( $value )
    {
      if ( !$q->contains("LEFT JOIN $c.MemberCards mc") )
        $q->leftJoin("$c.MemberCards mc");
      $q->andWhere("mc.expire_at > ?",date('Y-m-d',strtotime($value)))
        ->andWhere('mc.active = ?',true);
    }
    
    return $q;
  }
  public function addMemberCardsNotValidAtColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( $value )
    {
      if ( !$q->contains("LEFT JOIN $c.MemberCards mc") )
        $q->leftJoin("$c.MemberCards mc");
      $q->andWhere("mc.expire_at <= ?",date('Y-m-d',strtotime($value)))
        ->andWhere('mc.active = ?',true);
    }
    
    return $q;
  }
  public function addMemberCardsOnlyLastColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( $value )
    {
      if ( !$q->contains("LEFT JOIN $c.MemberCards mc") )
        $q->leftJoin("$c.MemberCards mc");
      $q->andWhere("mc.id = (SELECT max(mc2.id) FROM MemberCard mc2 WHERE mc2.contact_id = $c.id AND mc2.active = TRUE)")
        ->andWhere('mc.active = ?',true);
    }
    
    return $q;
  }

  // checkpoints / flow management
  public function addControlManifestationIdColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    
    if ( $values )
    {
      if ( !$q->contains("LEFT JOIN $a.Transactions transac") )
      $q->leftJoin("$a.Transactions transac");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)')") )
      $query->leftJoin('transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      if ( !$q->contains('LEFT JOIN tck.Controls ctrl') )
      $q->leftJoin('tck.Controls ctrl');
      
      if ( !$query->contains("LEFT JOIN $a.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)") )
      $query->leftJoin($a.'.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)');
      if ( !$q->contains('LEFT JOIN ctck.Controls cctrl') )
      $q->leftJoin('ctck.Controls cctrl');
      
      $q->andWhere('(TRUE')
        ->andWhere('ctrl.id IS NOT NULL')
        ->andWhereIn('tck.manifestation_id',$values)
        ->orWhereIn('cctrl.id IS NOT NULL')
        ->andWhereIn('ctck.manifestation_id',$values)
        ->andWhere('TRUE)');
    }
    
    return $q;
  }
  public function addControlCheckpointIdColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    if ( $values )
    {
      if ( !$q->contains("LEFT JOIN $a.Transactions transac") )
      $q->leftJoin("$a.Transactions transac");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)')") )
      $query->leftJoin('transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$query->contains("LEFT JOIN $a.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)") )
      $query->leftJoin($a.'.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)');
      
      if ( !$q->contains('LEFT JOIN tck.Controls ctrl') )
      $q->leftJoin('tck.Controls ctrl');
      
      if ( !$q->contains('LEFT JOIN ctrl.Checkpoint check') )
      $q->leftJoin('ctrl.Checkpoint check');
      
      $q->andWhereIn('check.id',$values);
    }
    
    return $q;
  }
  public function addControlCreatedAtColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    $fieldName = 'created_at';
      
    if (isset($values['is_empty']) && $values['is_empty'])
    {
      $q->addWhere(sprintf('%s.%s IS NULL', 'ctrl', $fieldName));
    }
    else
    {
      if ( !$q->contains("LEFT JOIN $a.Transactions transac") )
      $q->leftJoin("$a.Transactions transac");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)')") )
      $query->leftJoin('transac.Tickets tck WITH (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$query->contains("LEFT JOIN $a.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)") )
      $query->leftJoin($a.'.DirectTickets ctck WITH (ctck.printed_at IS NOT NULL OR ctck.integrated_at IS NOT NULL) AND ctck.id NOT IN (SELECT cttck.cancelling FROM ticket cttck WHERE cttck.cancelling IS NOT NULL)');
      
      if ( !$q->contains('LEFT JOIN tck.Controls ctrl') )
      $q->leftJoin('tck.Controls ctrl');
      
      if (null !== $values['from'] && null !== $values['to'])
      {
        $q->andWhere(sprintf('%s.%s >= ?', 'ctrl', $fieldName), $values['from'])
          ->andWhere(sprintf('%s.%s <= ?', 'ctrl', $fieldName), $values['to']);
      }
      else if (null !== $values['from'])
      {
        $q->andWhere(sprintf('%s.%s >= ?', 'ctrl', $fieldName), $values['from']);
      }
      else if (null !== $values['to'])
      {
        $q->andWhere(sprintf('%s.%s <= ?', 'ctrl', $fieldName), $values['to']);
      }
    }

    return $q;
  }
  
  // filtering on Contact AND Professional's description
  public function addDescriptionColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if (!( $value && is_array($value)
      && (trim($value['text']) || isset($value['is_empty']) && $value['is_empty']) ))
      return $q;
    
    if ( isset($value['is_empty']) && $value['is_empty'] )
      return $q->andWhere("$a.description = ?", '');
    
    foreach ( explode(' ', str_replace('  ', ' ', trim($value['text']))) as $str )
    if ( $str )
    {
      // transforms a AND WHERE provided by self::addTextQuery() in a OR WHERE clause...
      $q->andWhere('(FALSE');
      $this->addTextQuery($q->orWhere('(TRUE'), $field, array('text' => $str))->andWhere('TRUE)');
      $this->addTextQuery($q->orWhere('(TRUE'), $field, array('text' => $str), 'p')->andWhere('TRUE)');
      $q->andWhere('TRUE)');
    }
    
    return $q;
  }
  
  // Surveys
  public function addSurveyIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( $value )
      $q->andWhere("$a.id IN (SELECT s_sag.contact_id FROM SurveyAnswersGroup s_sag WHERE s_sag.survey_id = ? AND s_sag.contact_id IS NOT NULL) OR p.id IN (SELECT s_sag2.professional_id FROM SurveyAnswersGroup s_sag2 WHERE s_sag2.survey_id = ? AND s_sag2.professional_id IS NOT NULL)", array($value, $value));
    
    return $q;
  }
  public function addSurveyQueryIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( $value )
      $q->andWhere("$a.id IN (SELECT sq_sag.contact_id FROM SurveyAnswersGroup sq_sag LEFT JOIN sq_sag.Answers sq_a WHERE sq_a.survey_query_id = ? AND s_sag.contact_id IS NOT NULL) OR p.id IN (SELECT sq_sag2.professional_id FROM SurveyAnswersGroup sq_sag2 LEFT JOIN sq_sag2.Answers sq_a2 WHERE sq_a2.survey_query_id = ? AND s_sag2.professional_id IS NOT NULL)", array($value, $value));
    
    return $q;
  }
  public function addSurveyAnswerColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( $value )
      $q->andWhere("$a.id IN (SELECT sa_sag.contact_id FROM SurveyAnswersGroup sa_sag LEFT JOIN sa_sag.Answers sa_a WHERE sa_a.value ILIKE ? AND s_sag.contact_id IS NOT NULL) OR p.id IN (SELECT sa_sag2.professional_id FROM SurveyAnswersGroup sa_sag2 LEFT JOIN sa_sag2.Answers sa_a2 WHERE sa_a2.value ILIKE ? AND s_sag2.professional_id IS NOT NULL)", array("%$value%", "%$value%"));
    
    return $q;
  }

  public function setProfessionalData($bool)
  {
    return $this->showProfessionalData = $bool;
  }
  public function showProfessionalData()
  {
    return $this->showProfessionalData;
  }
  public function buildQuery(array $values)
  {
    $this->values = $values;
    $this->setProfessionalData(false);
    
    // to limit execution time
    $q = parent::buildQuery($values);
    $a = $q->getRootAlias();
    $q->select("$a.*, p.*, o.*, pn.*, y.*, pt.*, oph.*, gc.*, gp.*, go.*");
    
    return $q;
  }
  
  protected function getEventArchivesChoices()
  {
    $names = Doctrine::getTable('ContactEventArchives')->createQuery('a')
      ->select('DISTINCT a.name')
      ->orderBy('name')
      ->fetchArray();
    
    $choices = array();
    foreach ( $names as $name )
      $choices[$name['name']] = $name['name'];
    
    return $choices;
  }
}
