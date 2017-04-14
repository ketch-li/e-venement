<?php
/**
 * texts actions.
 *
 * @package    symfony
 * @subpackage texts
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class textsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new OptionKioskTextsForm();
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $values = $request->getPostParameters();

    $this->form = new OptionKioskTextsForm();
    $this->form->bind($values, array());
    // var_dump($this->form->getCSRFToken());
    // var_dump($values);
    if ( !$this->form->isValid() )
    {
      //var_dump($this->form->getGlobalErrors());
      $this->getUser()->setFlash('error',__('Your form cannot be validated.'));
      return $this->setTemplate('index');
    }
    
    $user_id = NULL;
    
    $cpt = $this->form->save($user_id);
    $this->getUser()->setFlash('notice',__('Your configuration has been updated with %i% option(s).',$arr = array('%i%' => $cpt)));
    
    $this->redirect('texts/index');
  }
}
