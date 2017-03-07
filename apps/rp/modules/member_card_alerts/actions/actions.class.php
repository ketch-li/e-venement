<?php

/**
 * member_card_alerts actions.
 *
 * @package    e-venement
 * @subpackage option_labels
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class member_card_alertsActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new OptionMCForm(OptionMCForm::getDBOptions());
  }
  
  public function executeUpdate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->form = new OptionMCForm();
    $this->form->bind($request->getPostParameters());
    
    if ( !$this->form->isValid() )
    {
      $this->getUser()->setFlash('error',__('Your form cannot be validated.'));
      return $this->setTemplate('index');
    }
    
    $domain = sfConfig::get('project_internals_users_domain', false);

    $cpt = $this->form->save(null, null, $domain);
    $this->getUser()->setFlash('notice',__('Your configuration has been updated with %i% option(s).',$arr = array('%i%' => $cpt)));
    $this->redirect('member_card_alerts/index');
  }
}
