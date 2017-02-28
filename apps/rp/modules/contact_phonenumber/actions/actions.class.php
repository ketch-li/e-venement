<?php

require_once dirname(__FILE__).'/../lib/contact_phonenumberGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/contact_phonenumberGeneratorHelper.class.php';

/**
 * contact_phonenumber actions.
 *
 * @package    e-venement
 * @subpackage contact_phonenumber
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class contact_phonenumberActions extends autoContact_phonenumberActions
{
  public function executeEdit(sfWebRequest $request) 
  {
    parent::executeEdit($request);
    
    $this->object = $this->form->getObject();
    $type = Doctrine::getTable('PhoneType')->findOneByName($this->object->name);
    
    $this->form->getWidget('mask')->setOption('default', $type->mask);  
  }    
}
