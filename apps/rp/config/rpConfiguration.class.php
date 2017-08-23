<?php

require_once dirname(__FILE__).'../../../../config/autoload.inc.php';

class rpConfiguration extends sfApplicationConfiguration
{
  protected $collectors = array();
  protected $task;
  protected $init_configuration = false;
  protected $bad_indexes_email = array(
    'orange',
    'sfr',
    'free',
    'yahoo',
    'gmail',
  );
  
  public function setup()
  {
    if (!( sfContext::hasInstance() && get_class(sfContext::getInstance()->getConfiguration()) != get_class($this) ))
      $this->enablePlugins(array('liClassLoaderPlugin', 'liPDFPlugin', 'sfDomPDFPlugin', 'liBarcodePlugin'));
    parent::setup();
  }
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
    
    $this->dispatcher->connect('admin.save_object', array($this, 'setSpecialFlash'));
    $this->dispatcher->connect('admin.save_object', array($this, 'addPhoneNumber'));
    $this->dispatcher->connect('user.change_authentication', array($this, 'logAuthentication'));
  }
  public function initialize()
  {
    if (!( sfContext::hasInstance() && get_class(sfContext::getInstance()->getConfiguration()) != get_class($this) ))
      $this->enableSecondWavePlugins(sfConfig::get('app_options_plugins', array()));
    ProjectConfiguration::initialize();
  }
  
  public static function changeTemplatesDir(sfAction $action)
  {
    if ( sfConfig::get('app_options_design',false) && sfConfig::get(sfConfig::get('app_options_design').'_active',false) )
    {
      $params = $action->getRoute()->getParameters();
      $action->setTemplate('_'.sfConfig::get('app_options_design','').'/'.($action->getTemplate() ? $action->getTemplate() : $params['action']));
    }
  }
  
  public function addPhoneNumber(sfEvent $event)
  {
    $params = $event->getParameters();
    
    // Phone
    if ( $params['object'] instanceof Contact || $params['object'] instanceof Organism )
    {
      if ( $params['object'] instanceof Contact )
      {
        $params = $event->getSubject()->getRequestParameter('contact');
        $pn = new ContactPhonenumber();
        $pn->contact_id = $event->getSubject()->contact->id;
      }
      else
      {
        $params = $event->getSubject()->getRequestParameter('organism');
        $pn = new OrganismPhonenumber();
        $pn->organism_id = $event->getSubject()->organism->id;
      }
      
      if ( $event->getSubject()->form->isValid() && isset($params['phone_number']) && $params['phone_number'] )
      {
        $pn->name = $params['phone_type'];
        $pn->number = $params['phone_number'];
        $pn->save();
      }
    }
  }
  public function setSpecialFlash(sfEvent $event)
  {
    $params = $event->getParameters();
    
    // Email
    if ( $params['object'] instanceof Email )
    if ( !$params['object']->isATest() )
      $event->getSubject()->getUser()->setFlash('success',"Your email have been sent correctly.");
  }
  public function logAuthentication(sfEvent $event)
  {
    $params   = $event->getParameters();
    $user     = sfContext::getInstance()->getUser();
    $request  = sfContext::getInstance()->getRequest();
    
    if ( !is_object($user) )
      return false;
    
    if (( sfConfig::get('project_login_alert_beginning_at', false) && sfConfig::get('project_login_alert_beginning_at') < time() || !sfConfig::get('project_login_alert_beginning_at', false) )
      &&( sfConfig::get('project_login_alert_ending_at', false) && sfConfig::get('project_login_alert_ending_at') > time() || !sfConfig::get('project_login_alert_ending_at', false) )
      && sfConfig::get('project_login_alert_message', false) )
      $user->setFlash('error', sfConfig::get('project_login_alert_message'));

    $auth = new Authentication();
    $auth->sf_guard_user_id = $user->getId();
    $auth->description      = $user;
    $auth->ip_address       = $request->getHttpHeader('addr','remote');
    $auth->user_agent       = $request->getHttpHeader('User-Agent');
    $auth->referer          = $request->getReferer();
    $auth->success          = $params['authenticated'];
    
    $auth->save();
  }
  
  public function initGarbageCollectors(sfCommandApplicationTask $task = NULL)
  {
    $this->task = $task;
    
    // Delete the pdf files generated to print membercards
    $this->addGarbageCollector('membercard-files-removal', function(){
      $section = 'clear-membercard-files';
      $this->stdout($section, 'Removing old membercard files...', 'COMMAND');
       
      $interval = sfConfig::get('app_options_membercard_file_removal', '1 week');
       
      $res = 
      Doctrine_Query::create()
        ->delete()
        ->from('Picture p')
        ->where('p.name LIKE ?', 'db:Membercard%')
        ->andWhere("p.created_at < now() - INTERVAL '$interval'")
        ->execute(); 
    
      $msg = $res . ' file' . ($res > 1 ? 's' : '') . ' removed';
    
      $this->stdout($section, $msg, 'INFO');
    });
    
    // Bad indexes removal
    $this->addGarbageCollector('indexes-removal', function(){
      $section = 'Indexes removal';
      $this->stdout($section, 'Removing bad indexes...', 'COMMAND');
      $nb = 0;
      
      $q = "DELETE FROM contact_index WHERE field = 'email' AND keyword IN ('".implode("','", $this->bad_indexes_email)."')";
      $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
      $stmt = $pdo->prepare($q);
      $stmt->execute();
      $this->stdout($section, "[OK] indexes removed", 'INFO');
    });
    
    // Alerts related to MemberCards expiration
    $this->addGarbageCollector('mc-alerts', function(){
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date', 'CrossAppLink'));
      $section = 'MC expiration';
      $nb = $err = 0;
      $this->stdout($section, 'Looking for member cards...', 'COMMAND');
      
      $options = OptionMCForm::getDBOptions();
      if ( !$options['enabled'] || !$options['email_from'] )
      {
        $this->stdout($section, 'This feature is currently disabled...', 'COMMAND');
        return;
      }
      
      $last_run = Doctrine::getTable('Option')->createQuery('o')
        ->andWhere('o.type = ?', 'mc_alerts')
        ->andWhere('o.name = ?', 'last_run')
        ->fetchOne();
      if ( $last_run && strtotime($last_run->value) + 24*60*60 > time() )
      {
        $this->stdout($section, 'The emails have been sent less than 24 hours ago', 'COMMAND');
        return;
      }
      if ( $last_run )
        $last_run->delete();
      
      $last_run = new Option;
      $last_run->type = 'mc_alerts';
      $last_run->name = 'last_run';
      $last_run->value = date('Y-m-d H:i:s');
      $last_run->save();
      
      $mcs = new Doctrine_Collection('MemberCard');
      
      $q = Doctrine::getTable('MemberCard')->createQuery('mc')
        ->leftJoin('mc.Contact c')
        ->andWhere('mc.expire_at <= ?', date('Y-m-d', strtotime($options['delay_before'].' days')))
        ->andWhere('mc.expire_at >  ?', date('Y-m-d', strtotime(($options['delay_before']-1).' days')))
        ->andWhere('(SELECT COUNT(mmc.id) FROM MemberCard mmc WHERE mmc.member_card_type_id = mc.member_card_type_id AND mc.contact_id = mmc.contact_id AND mmc.expire_at > mc.expire_at) = 0')
      ;
      // Domain segmentation
      if ( ($dom = sfConfig::get('project_internals_users_domain', false)) && $dom != '.' )
      $q->leftJoin("mc.Transaction tr")
        ->leftJoin("tr.User tvu ON tvu.id = (SELECT tv.sf_guard_user_id FROM TransactionVersion tv WHERE tv.id = tr.id AND tv.version = 1)")
        ->leftJoin('tvu.Domain uvd')
        ->andWhere('uvd.name ILIKE ? OR uvd.name = ?', array('%.'.$dom, $dom));
      
      $mcs->merge($q->execute());
      $this->stdout($section, 'Got '.($nb = $mcs->count()).' member cards that are going to expire.', 'COMMAND');
      
      $q = Doctrine::getTable('MemberCard')->createQuery('mc')
        ->leftJoin('mc.Contact c')
        ->andWhere('mc.expire_at >= ?', date('Y-m-d', strtotime($options['delay_after'].' days')))
        ->andWhere('mc.expire_at <  ?', date('Y-m-d', strtotime(($options['delay_after']+1).' days')))
        ->andWhere('(SELECT COUNT(mmc.id) FROM MemberCard mmc WHERE mmc.member_card_type_id = mc.member_card_type_id AND mc.contact_id = mmc.contact_id AND mmc.expire_at > mc.expire_at) = 0')
      ;
      // Domain segmentation
      if ( ($dom = sfConfig::get('project_internals_users_domain', false)) && $dom != '.' )
      $q->leftJoin("mc.Transaction tr")
        ->leftJoin("tr.User tvu ON tvu.id = (SELECT tv.sf_guard_user_id FROM TransactionVersion tv WHERE tv.id = tr.id AND tv.version = 1)")
        ->leftJoin('tvu.Domain uvd')
        ->andWhere('uvd.name ILIKE ? OR uvd.name = ?', array('%.'.$dom, $dom));      
        
      $mcs->merge($q->execute());
      $this->stdout($section, 'Got '.($mcs->count() - $nb).' member cards that have already expired.', 'COMMAND');
      
      $nb = 0;
      foreach ( $mcs as $mc )
      if ( $mc->Contact && $mc->Contact->email )
      {
        $email = new Email;
        $email->field_from = $options['email_from'];
        $email->field_subject = $options['email_subject'];
        $email->content = str_replace('##EXPIRATION##', format_date($mc->expire_at), $options['email_content']);
        $email->Contacts[] = $mc->Contact;
        $email->isATest(false);
        $email->save();
        $nb++;
      }
      else
        $err++;
      
      $this->stdout($section, "[OK] $nb emails sent".($err > 0 ? ", $err not sent (no email given for the related Contact)" : ''), 'INFO');
    });
  }
}
