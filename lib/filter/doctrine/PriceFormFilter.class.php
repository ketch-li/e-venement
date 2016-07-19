<?php

/**
 * Price filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PriceFormFilter extends BasePriceFormFilter
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink', 'I18N', 'Asset'));
    
    $this->widgetSchema['manifestations_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event', 'manifestation/ajax'),
    ));
    
    $this->widgetSchema['users_list']->setOption('query', $q = Doctrine_Query::create()->from('SfGuardUser u'));
    $this->validatorSchema['users_list']->setOption('query', $q);
    
    $this->widgetSchema   ['not_workspaces_list'] = $this->widgetSchema   ['workspaces_list'];
    $this->validatorSchema['not_workspaces_list'] = $this->validatorSchema['workspaces_list'];
  }
  
  public function addNotWorkspacesListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( !$q->contains("LEFT JOIN $a.Workspaces ws") )
      $q->leftJoin("$a.Workspaces ws");
    
    if ( !$value )
      return $q;
    return $q->andWhereNotIn('ws.id', $value);
  }
  
  public function addNameColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if (!( $value && is_array($value) && isset($value['text']) && $value['text'] ))
      return $q;
    $q->andWhere('pt.name ILIKE ?', $value['text'].'%');
    return $q;
  }
}
