<?php

require_once dirname(__FILE__).'/../lib/organism_phonenumberGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/organism_phonenumberGeneratorHelper.class.php';

/**
 * organism_phonenumber actions.
 *
 * @package    e-venement
 * @subpackage organism_phonenumber
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class organism_phonenumberActions extends autoOrganism_phonenumberActions
{
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);

    $this->object = $this->form->getObject();
    $type = Doctrine::getTable('PhoneType')->findOneByName($this->object->name);
  
    $this->form->getWidget('mask')->setOption('default', $type->mask);
  }  
}
