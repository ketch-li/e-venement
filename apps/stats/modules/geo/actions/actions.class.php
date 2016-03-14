<?php

/**
 * geo actions.
 *
 * @package    e-venement
 * @subpackage geo
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class geoActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    if ( $request->hasParameter('criterias') )
    {
      $this->criterias = $request->getParameter('criterias');
      $this->setCriterias($this->criterias);
      $this->redirect($this->getContext()->getModuleName().'/index');
    }
    
    $this->form = new StatsCriteriasForm();
    $this->form
      ->addOnlyWhatCriteria()
      ->addApproachCriteria()
      ->addEventCriterias()
      ->addManifestationCriteria()
      ->addGroupsCriteria();
    if ( is_array($this->getCriterias()) )
      $this->form->bind($this->getCriterias());
  }
  
  protected function getCriterias()
  {
    return $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
  }
  protected function setCriterias($values)
  {
    $this->getUser()->setAttribute('stats.criterias',$values,'admin_module');
    return $this;
  }
  
  public function executeJson(sfWebRequest $request)
  {
    $criterias = $this->getCriterias();
    $this->data = $this->getData($request->getParameter('type','ego'), !(isset($criterias['approach']) && $criterias['approach'] === ''));
  }
  public function executeCsv(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Date','CrossAppLink','Number'));
    
    $criterias = $this->getCriterias();
    $this->data = $this->getData($request->getParameter('type','ego'), !(isset($criterias['approach']) && $criterias['approach'] === ''), 'csv');
    
    $total = array('nb' => 0, 'value' => 0, 'tickets' => 0);
    foreach ( $total as $approach => $val )
    foreach ( $this->data[$approach] as $data )
      $total[$approach] += $data;
    
    $this->lines = array();
    foreach ( $this->data['nb'] as $name => $data )
    {
      $this->lines[$name] = array(
        'name' => __($name),
        'qty' => $data,
        'percent' => format_number(round($data*100/($total['nb'] ? $total['nb'] : 0),2)),
      );
    }
    foreach ( $this->data['tickets'] as $name => $data )
    {
      $this->lines[$name]['tickets'] = $data;
      $this->lines[$name]['tickets%'] = format_number(round($data*100/($total['tickets'] ? $total['tickets'] : 1), 2));
    }
    foreach ( $this->data['value'] as $name => $data )
    {
      $this->lines[$name]['value'] = format_currency($data, $this->getContext()->getConfiguration()->getCurrency());
      $this->lines[$name]['value%'] = format_number(round($data*100/($total['value'] ? $total['value'] : 1), 2));
    }
    
    $this->lines['total'] = array(
      'name'          => __('Total'),
      'qty'           => $total['nb'],
      'percent'       => 100,
      'tickets'       => $total['tickets'],
      'tickets%'      => 100,
      'value'         => format_currency($total['value'],$this->getContext()->getConfiguration()->getCurrency()),
      'value%'        => 100,
    );
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms'        => in_array('microsoft',$params['option']),
      'fields'    => array('name','qty','percent','tickets', 'tickets%', 'value','value%'),
      'tunnel'    => false,
      'noheader'  => false,
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    sfConfig::set('sf_escaping_strategy', false);
    $confcsv = sfConfig::get('software_internals_csv');
    if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( $request->hasParameter('debug') )
    {
      $this->setLayout('layout');
      $this->getResponse()->sendHttpHeaders();
    }
    else
      sfConfig::set('sf_web_debug', false);
  }
  
  public function executeData(sfWebRequest $request)
  {
    $criterias = $this->getCriterias();
    $data = $this->getData($request->getParameter('type','ego'), !(isset($criterias['approach']) && $criterias['approach'] === ''));
    
    $this->data = $data['nb'];
    if ( isset($criterias['approach']) )
    switch ( $criterias['approach'] ) {
    case 'financial':
      $this->data = $data['value'];
      break;
    case 'by-tickets':
      $this->data = $data['tickets'];
      break;
    }
    
    if ( !$request->hasParameter('debug') )
    {
      $this->setLayout('raw');
      sfConfig::set('sf_debug',false);
      $this->getResponse()->setContentType('application/json');
    }
  }
  
  protected function buildQuery()
  {
    $q = $this->addFiltersToQuery(Doctrine_Query::create()
        ->from('Transaction t')
        ->leftJoin('t.Contact c')
        ->leftJoin('t.Professional pro')
      )
      ->leftJoin('pro.Organism o')
      ->leftJoin('t.Tickets tck')
      ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL')
      ->andWhere('tck.cancelling IS NULL AND tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE cancelling IS NOT NULL)')
      ->andWhere('tck.duplicating IS NULL')
      ->leftJoin('tck.Gauge g')
      ->andWhereIn('g.workspace_id', array_keys($this->getUser()->getWorkspacesCredentials()))
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Event e')
      ->andWhereIn('e.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
      ->leftJoin('tck.Price p')
      ->leftJoin('p.Users u')
      ->andWhere('u.id = ?', $this->getUser()->getId())
    ;
    return $q;
  }
  protected function addFiltersToQuery(Doctrine_Query $q)
  {
    $criterias = $this->getCriterias();
    
    if ( isset($criterias['groups_list']) && is_array($criterias['groups_list']) )
    {
      $q->leftJoin('c.Groups gc')
        ->leftJoin('pro.Groups gp')
        ->andWhere('(TRUE')
        ->andWhereIn('gc.id', $criterias['groups_list'])
        ->orWhereIn('gp.id', $criterias['groups_list'])
        ->andWhere('TRUE)')
      ;
    }
    
    if ( isset($criterias['only_what']) )
    switch ( $criterias['only_what'] ) {
    case 'individuals':
      $q->andWhere('t.professional_id IS NULL');
      break;
    case 'professionals':
      $q->andWhere('t.professional_id IS NOT NULL');
      break;
    }
    if ( isset($criterias['meta_events_list']) && is_array($criterias['meta_events_list']) )
      $q->andWhereIn('e.meta_event_id', $criterias['meta_events_list']);
    if ( isset($criterias['event_categories_list']) && is_array($criterias['event_categories_list']) )
      $q->andWhereIn('e.event_category_id', $criterias['event_categories_list']);
    if ( isset($criterias['workspaces_list']) && is_array($criterias['workspaces_list']) )
      $q->andWhereIn('g.workspace_id', $criterias['workspaces_list']);
    if ( isset($criterias['sf_guard_users_list']) && is_array($criterias['sf_guard_users_list']) )
      $q->andWhereIn('tck.sf_guard_user_id', $criterias['sf_guard_users_list']);
    if ( isset($criterias['events_list']) && is_array($criterias['events_list']) )
      $q->andWhereIn('m.event_id', $criterias['events_list']);
    if ( isset($criterias['manifestations_list']) && is_array($criterias['manifestations_list']) )
      $q->andWhereIn('m.id', $criterias['manifestations_list']);
    
    if ( isset($criterias['dates']) && is_array($criterias['dates']) )
    {
      foreach ( array('from' => '>=', 'to' => '<') as $margin => $operand )
      if ( isset($criterias['dates'][$margin]) && is_array($criterias['dates'][$margin]) )
      if ( isset($criterias['dates'][$margin]['day']) && isset($criterias['dates'][$margin]['month']) && isset($criterias['dates'][$margin]['year']) )
      if ( $criterias['dates'][$margin]['day'] && $criterias['dates'][$margin]['month'] && $criterias['dates'][$margin]['year'])
      {
        $q->andWhere(
          'tck.printed_at '.$operand.' ? OR tck.printed_at IS NULL AND tck.integrated_at '.$operand.' ?',
          array(
            $criterias['dates'][$margin]['year'].'-'.$criterias['dates'][$margin]['month'].'-'.$criterias['dates'][$margin]['day'],
            $criterias['dates'][$margin]['year'].'-'.$criterias['dates'][$margin]['month'].'-'.$criterias['dates'][$margin]['day']
          )
        );
      }
    }
    
    return $q;
  }
  
  protected function getData($type = 'ego', $count_tickets = false, $format = 'data')
  {
    $this->type = $type;
    $res = array('nb' => array(), 'tickets' => array(), 'value' => array());
    $others = $total = array('nb' => 0, 'tickets' => 0, 'value' => 0);
    switch ( $type ) {
    
    case 'metropolis-in':
      $client = sfConfig::get('app_about_client', array());
      $metro = $this->buildQuery()
        ->andWhere('(TRUE')
        ->andWhereIn('o.postalcode', $client['postalcode'])
        ->orWhereIn('c.postalcode', $client['postalcode'])
        ->orWhereIn('t.postalcode', $client['postalcode'])
        ->andWhere('TRUE)')
      ;
    case 'postalcodes':
      $q = isset($metro) ? $metro : $this->buildQuery();
      $q
        ->select('t.id, c.id AS contact_id')
        ->addSelect('(CASE WHEN pro.id IS NOT NULL THEN o.postalcode ELSE CASE WHEN c.id IS NOT NULL THEN c.postalcode ELSE t.postalcode END END) AS postalcode')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id, c.postalcode, pro.id, o.postalcode, t.postalcode')
      ;
      $contacts = array();
      foreach ( $arr = $q->fetchArray() as $pc )
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        // this is here as a workaround of the arrival of postalcodes within transactions
        if ( $approach == 'nb' )
        {
          $id = $pc['contact_id'] ? $pc['contact_id'] : 't'.$pc['id'];
          if ( in_array($id, $contacts) )
            continue;
          $contacts[] = $id;
        }
        
        if ( !isset($res[$approach][$pc['postalcode']]) )
          $res[$approach][$pc['postalcode']] = 0;
        $res[$approach][$pc['postalcode']] += is_int($field) ? $field : $pc[$field];
      }
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        arsort($res[$approach]);
      
      $cpt = 0;
      foreach ( $res[$count_tickets ? 'tickets' : 'nb'] as $code => $qty )
      {
        if ( str_pad(intval($code).'',5,'0',STR_PAD_LEFT) !== ''.$code )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          if ( isset($res[$approach][$code]) )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
          continue;
        }
        if ( $cpt >= sfConfig::get('app_geo_limits_'.$type, 8) )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
        }
        $cpt++;
      }
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        $res[$approach]['others'] = $others[$approach];
        arsort($res[$approach]);
      }
    break;
    
    case 'districts':
      $q = $this->buildQuery()
        ->select('t.id, c.id AS contact_id')
        ->addSelect('(SELECT db.name FROM GeoFrStreetBase sb LEFT JOIN sb.GeoFrDistrictBase db WHERE c.address = sb.address AND c.postalcode = sb.zip AND c.city = sb.city) AS iris')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id, c.postalcode, pro.id, o.postalcode, t.postalcode')
      ;
      $contacts = array();
      foreach ( $arr = $q->fetchArray() as $pc )
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        if ( !isset($res[$approach][$pc['iris']]) )
          $res[$approach][$pc['iris']] = 0;
        $res[$approach][$pc['iris']] += is_int($field) ? $field : $pc[$field];
      }
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        arsort($res[$approach]);
      
      $cpt = 0;
      foreach ( $res[$count_tickets ? 'tickets' : 'nb'] as $code => $qty )
      {
        if ( str_pad(intval($code).'',5,'0',STR_PAD_LEFT) !== ''.$code )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          if ( isset($res[$approach][$code]) )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
          continue;
        }
        if ( $format != 'csv' && $cpt >= sfConfig::get('app_geo_limits_'.$type, 12) )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
        }
        $cpt++;
      }
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        $res[$approach]['others'] = $others[$approach];
        arsort($res[$approach]);
      }
    break;
    
    case 'departments':
      $contacts = array();
      foreach ( $this->buildQuery()
        ->select('t.id, c.id AS contact_id')
        ->addSelect('substr(CASE WHEN pro.id IS NOT NULL THEN o.postalcode ELSE CASE WHEN c.id IS NOT NULL THEN c.postalcode ELSE t.postalcode END END,1,2) AS dpt')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id, c.postalcode, pro.id, o.postalcode, t.postalcode')
        ->fetchArray() as $pc )
      {
        foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        {
          // this is here as a workaround of the arrival of postalcodes within transactions
          if ( $approach == 'nb' )
          {
            $id = $pc['contact_id'] ? $pc['contact_id'] : 't'.$pc['id'];
            if ( in_array($id, $contacts) )
              continue;
            $contacts[] = $id;
          }
          
          if ( !isset($res[$approach][$pc['dpt']]) )
            $res[$approach][$pc['dpt']] = 0;
          $res[$approach][$pc['dpt']] += is_int($field) ? $field : $pc[$field];
          $total[$approach] += is_int($field) ? $field : $pc[$field];
        }
      }
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        arsort($res[$approach]);
      
      $cpt = 0;
      foreach ( $res[$count_tickets ? 'tickets' : 'nb'] as $code => $qty )
      {
        if ( str_pad(intval($code).'',2,'0',STR_PAD_LEFT) !== ''.$code )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
          continue;
        }
        if ( $cpt >= sfConfig::get('app_geo_limits_'.$type, 4) )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
        }
        $cpt++;
      }
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        $res[$approach]['others'] = $others[$approach];
        arsort($res[$approach]);
      }
      
      $contacts = array();
      foreach ( Doctrine::getTable('GeoFrDepartment')->createQuery('gd')
        ->andWhereIn('gd.num', array_keys($res['nb']))
        ->execute() as $dpt )
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        $res[$approach][$dpt->name] = $res[$approach][$dpt->num];
        unset($res[$approach][$dpt->num]);
      }
    break;
    
    case 'regions':
      $dpts = array();
      foreach ( Doctrine::getTable('GeoFrDepartment')->createQuery('gd')
        ->leftJoin('gd.Region gr')
        ->select('gd.num, gr.id AS region_id, gr.name AS region')
        ->fetchArray() as $dpt )
        $dpts[$dpt['num']] = $dpt['region'];
      $dpts[''] = '';
      
      $contacts = array();
      foreach ( $this->buildQuery()
        ->select('t.id, c.id AS contact_id')
        ->addSelect('substr(CASE WHEN pro.id IS NOT NULL THEN o.postalcode ELSE CASE WHEN c.id IS NOT NULL THEN c.postalcode ELSE t.postalcode END END,1,2) AS dpt')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id, c.postalcode, pro.id, o.postalcode, t.postalcode')
        ->fetchArray() as $pc )
      {
        foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        {
          // this is here as a workaround of the arrival of postalcodes within transactions
          if ( $approach == 'nb' )
          {
            $id = $pc['contact_id'] ? $pc['contact_id'] : 't'.$pc['id'];
            if ( in_array($id, $contacts) )
              continue;
            $contacts[] = $id;
          }
          
          if ( !isset($dpts[trim($pc['dpt'])]) )
            $pc['dpt'] = '';
          if ( !isset($res[$approach][$dpts[trim($pc['dpt'])]]) )
            $res[$approach][$dpts[trim($pc['dpt'])]] = 0;
          $res[$approach][$dpts[trim($pc['dpt'])]] += is_int($field) ? $field : $pc[$field];
          $total[$approach] += is_int($field) ? $field : $pc[$field];
        }
      }
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        arsort($res[$approach]);
      
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      if ( isset($res[$approach]['']) )
      {
        $others[$approach] += $res[$approach][''];
        unset($res[$approach]['']);
      }
      
      $cpt = 0;
      foreach ( $res[$count_tickets ? 'tickets' : 'nb'] as $code => $qty )
      {
        if ( $code === '' )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
          continue;
        }
        if ( $cpt >= sfConfig::get('app_geo_limits_'.$type, 4) )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
        }
        $cpt++;
      }
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        $res[$approach]['others'] = $others[$approach];
        arsort($res[$approach]);
      }
    break;
    
    case 'countries':
      $tmp = sfConfig::get('app_about_client', array());
      $default_country = isset($tmp['country']) ? $tmp['country'] : '';
      
      $contacts = array();
      foreach ( $this->buildQuery()
        ->select('t.id, c.id AS contact_id')
        ->addSelect('(CASE WHEN pro.id IS NOT NULL THEN o.country ELSE c.country END) AS country')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id, c.country, pro.id, o.country')
        ->fetchArray() as $pc )
      {
        if ( !trim($pc['country']) )
          $pc['country'] = $default_country;
        $pc['country'] = trim(ucwords(strtolower($pc['country'])));
        foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        {
          // this is here as a workaround of the arrival of postalcodes within transactions
          if ( $approach == 'nb' )
          {
            $id = $pc['contact_id'] ? $pc['contact_id'] : 't'.$pc['id'];
            if ( in_array($id, $contacts) )
              continue;
            $contacts[] = $id;
          }
          
          if ( !isset($res[$approach][$pc['country']]) )
            $res[$approach][$pc['country']] = 0;
          $res[$approach][$pc['country']] += is_int($field) ? $field : $pc[$field];
          $total[$approach] += is_int($field) ? $field : $pc[$field];
        }
      }
      
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        arsort($res[$approach]);
      
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      if ( isset($res[$approach]['']) )
      {
        $others[$approach] += $res[$approach][''];
        unset($res[$approach]['']);
      }
      
      $cpt = 0;
      foreach ( $res[$count_tickets ? 'tickets' : 'nb'] as $code => $qty )
      {
        if ( $code === '' )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
          continue;
        }
        if ( $cpt >= sfConfig::get('app_geo_limits_'.$type, 4) )
        {
          foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          {
            $others[$approach] += $res[$approach][$code];
            unset($res[$approach][$code]);
          }
        }
        $cpt++;
      }
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        $res[$approach]['others'] = $others[$approach];
        arsort($res[$approach]);
      }
    break;
    
    default: // 'ego'
      if ( !isset($client) )
        $client = sfConfig::get('app_about_client',array());
      if ( !isset($client['postalcode']) )
        $client['postalcode'] = array(0);
      if ( !is_array($client['postalcode']) )
        $client['postalcode'] = array($client['postalcode']);
      $qs = array();
      foreach ( $client['postalcode'] as $pc )
        $qs[] = '?';
      
      // exact
      $q = $this->buildQuery()
        ->select('t.id, c.id AS contact_id')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id')
        ->andWhere('(pro.id IS NOT NULL AND o.postalcode = ? OR pro.id IS NULL AND c.id IS NOT NULL AND c.postalcode = ? OR c.id IS NULL AND t.postalcode = ?)', array($client['postalcode'][0], $client['postalcode'][0], $client['postalcode'][0]))
        ->andWhere('(pro.id IS NULL AND (c.country ILIKE ? OR c.country IS NULL OR c.country = ?) OR pro.id IS NOT NULL AND (o.country ILIKE ? OR o.country IS NULL OR o.country = ?))', array(isset($client['country']) ? $client['country'] : 'France', '', isset($client['country']) ? $client['country'] : 'France', '',));
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        $res[$approach]['exact'] = 0;
      $contacts = array();
      $arr = $q->fetchArray();
      foreach ( $arr as $c )
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        // this is here as a workaround of the arrival of postalcodes within transactions
        if ( $approach == 'nb' )
        {
          $id = $c['contact_id'] ? $c['contact_id'] : 't'.$c['id'];
          if ( in_array($id, $contacts) )
            continue;
          $contacts[] = $id;
        }
        
        $res[$approach]['exact'] += is_int($field) ? $field : $c[$field];
        $total[$approach] += is_int($field) ? $field : $c[$field];
      }
      
      // metropolis
      if ( count($client['postalcode']) > 1 )
      {
        $buf = $client['postalcode'][0];
        unset($qs[0], $client['postalcode'][0]);
        $q = $this->buildQuery()
          ->select('t.id, c.id AS contact_id')
          ->addSelect('count(DISTINCT tck.id) AS qty')
          ->addSelect('sum(tck.value) AS sum')
          ->groupBy('t.id, c.id')
          ->andWhere('(pro.id IS NOT NULL AND o.postalcode IN ('.implode(',',$qs).') OR pro.id IS NULL AND c.id IS NOT NULL AND c.postalcode IN ('.implode(',',$qs).') OR c.id IS NULL AND t.postalcode IN ('.implode(',',$qs).'))', $arr = array_merge($client['postalcode'], $client['postalcode'], $client['postalcode']))
          ->andWhere('(pro.id IS NULL AND (c.country ILIKE ? OR c.country IS NULL OR c.country = ?) OR pro.id IS NOT NULL AND (o.country ILIKE ? OR o.country IS NULL OR o.country = ?))', array(isset($client['country']) ? $client['country'] : 'France', '', isset($client['country']) ? $client['country'] : 'France', '',))
        ;
        foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
          $res[$approach]['metropolis'] = 0;
        $contacts = array();
        $arr = $q->fetchArray();
        foreach ( $arr as $c )
        foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        {
          // this is here as a workaround of the arrival of postalcodes within transactions
          if ( $approach == 'nb' )
          {
            $id = $c['contact_id'] ? $c['contact_id'] : 't'.$c['id'];
            if ( in_array($id, $contacts) )
              continue;
            $contacts[] = $id;
          }
          
          $res[$approach]['metropolis'] += is_int($field) ? $field : $c[$field];
          $total[$approach] += is_int($field) ? $field : $c[$field];
        }
        $client['postalcode'][0] = $buf;
        $qs[0] = '?';
      }
      
      // department
      $q = $this->buildQuery()
        ->select('t.id, c.id AS contact_id')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id')
        ->andWhere('substring(CASE WHEN pro.id IS NOT NULL THEN o.postalcode ELSE CASE WHEN c.id IS NOT NULL THEN c.postalcode ELSE t.postalcode END END, 1, 2) = substring(?, 1, 2)', $client['postalcode'][0])
        ->andWhere('(pro.id IS NULL AND (c.country ILIKE ? OR c.country IS NULL OR c.country = ?) OR pro.id IS NOT NULL AND (o.country ILIKE ? OR o.country IS NULL OR o.country = ?))', array(isset($client['country']) ? $client['country'] : 'France', '', isset($client['country']) ? $client['country'] : 'France', '',));
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        $res[$approach]['department'] = -$res[$approach]['exact'];
      $contacts = array();
      $arr = $q->fetchArray();
      foreach ( $arr as $c )
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        // this is here as a workaround of the arrival of postalcodes within transactions
        if ( $approach == 'nb' )
        {
          $id = $c['contact_id'] ? $c['contact_id'] : 't'.$c['id'];
          if ( in_array($id, $contacts) )
            continue;
          $contacts[] = $id;
        }
        
        $res[$approach]['department'] += is_int($field) ? $field : $c[$field];
        $total[$approach] += is_int($field) ? $field : $c[$field];
      }
      
      // region
      $q = $this->buildQuery()
        ->select('t.id, c.id AS contact_id')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id')
        ->andWhere('substring(CASE WHEN pro.id IS NOT NULL THEN o.postalcode ELSE CASE WHEN c.id IS NOT NULL THEN c.postalcode ELSE t.postalcode END END, 1, 2) = substring(?, 1, 2)', $client['postalcode'][0])
        ->andWhere('(pro.id IS NULL AND (c.country ILIKE ? OR c.country IS NULL OR c.country = ?) OR pro.id IS NOT NULL AND (o.country ILIKE ? OR o.country IS NULL OR o.country = ?))', array(isset($client['country']) ? $client['country'] : 'France', '', isset($client['country']) ? $client['country'] : 'France', '',));
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        $res[$approach]['region'] = -$res[$approach]['exact'] -$res[$approach]['department'];
      $contacts = array();
      $arr = $q->fetchArray();
      foreach ( $arr as $c )
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        // this is here as a workaround of the arrival of postalcodes within transactions
        if ( $approach == 'nb' )
        {
          $id = $c['contact_id'] ? $c['contact_id'] : 't'.$c['id'];
          if ( in_array($id, $contacts) )
            continue;
          $contacts[] = $id;
        }
        
        $res[$approach]['region'] += is_int($field) ? $field : $c[$field];
        $total[$approach] += is_int($field) ? $field : $c[$field];
      }
      
      // country
      $q = $this->buildQuery()
        ->select('t.id, c.id AS contact_id')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id')
        ->andWhere('(pro.id IS NULL AND (c.country ILIKE ? OR c.country IS NULL OR c.country = ?) OR pro.id IS NOT NULL AND (o.country ILIKE ? OR o.country IS NULL OR o.country = ?))', array(isset($client['country']) ? $client['country'] : 'France', '', isset($client['country']) ? $client['country'] : 'France', '',));
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        $res[$approach]['country'] = -$res[$approach]['exact'] -$res[$approach]['department'] -$res[$approach]['region'];
      $arr = $q->fetchArray();
      $contacts = array();
      foreach ( $arr as $c )
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        // this is here as a workaround of the arrival of postalcodes within transactions
        if ( $approach == 'nb' )
        {
          $id = $c['contact_id'] ? $c['contact_id'] : 't'.$c['id'];
          if ( in_array($id, $contacts) )
            continue;
          $contacts[] = $id;
        }
        
        $res[$approach]['country'] += is_int($field) ? $field : $c[$field];
        $total[$approach] += is_int($field) ? $field : $c[$field];
      }
      
      // others
      $q = $this->buildQuery()
        ->select('t.id, c.id AS contact_id')
        ->addSelect('count(DISTINCT tck.id) AS qty')
        ->addSelect('sum(tck.value) AS sum')
        ->groupBy('t.id, c.id');
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
        $res[$approach]['others'] = -$res[$approach]['exact'] -$res[$approach]['department'] -$res[$approach]['region'] -$res['nb']['country'];
      $arr = $q->fetchArray();
      $contacts = array();
      foreach ( $arr as $c )
      foreach ( array('nb' => 1, 'tickets' => 'qty', 'value' => 'sum') as $approach => $field )
      {
        // this is here as a workaround of the arrival of postalcodes within transactions
        if ( $approach == 'nb' )
        {
          $id = $c['contact_id'] ? $c['contact_id'] : 't'.$c['id'];
          if ( in_array($id, $contacts) )
            continue;
          $contacts[] = $id;
        }
        
        $res[$approach]['others'] += is_int($field) ? $field : $c[$field];
        $total[$approach] += is_int($field) ? $field : $c[$field];
      }
      
      break;
    }
    
    return $res;
  }
}
