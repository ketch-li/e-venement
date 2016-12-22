<?php

require_once dirname(__FILE__).'/../lib/textsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/textsGeneratorHelper.class.php';

/**
 * texts actions.
 *
 * @package    symfony
 * @subpackage texts
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class textsActions extends autoTextsActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new OptionPubTextsForm();
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $values = $request->getPostParameters();
    
    // terms & conditions
    foreach ( $request->getFiles() as $upload => $content )
    {
      if ( $content['error'] == 0 ) 
      {            
          $fname = 'pub:'.$upload;
          
          // cleaning the DB
          Doctrine::getTable('Picture')->createQuery('f')
            ->delete()
            ->where('f.name = ?', $fname)
            ->execute();
          
          $file = new Picture;
          $file->name = $fname;
          $file->content = base64_encode(file_get_contents($content['tmp_name']));
          $file->type = $content['type'];
          $file->save();
          $values[$upload] = $fname;
          $values[str_replace('_file((', '_url((', $upload)] = $file->getUrl(array('app' => 'pub'));
          
          $values[str_replace('_file((', '((', $upload)] = '<a href="'.$file->getUrl(array('app' => 'pub')).'" target="_blank">'.__('Terms & Conditions').'</a>';
          //$values[str_replace('_file((', '((', $upload)] = $file->getUrl(array('app' => 'pub'));
        }
    }
    
    $this->form = new OptionPubTextsForm();
    $this->form->bind($values, array());
    
    if ( !$this->form->isValid() )
    {
      $this->getUser()->setFlash('error',__('Your form cannot be validated.'));
      return $this->setTemplate('index');
    }
    
    $user_id = NULL;
    
    $cpt = $this->form->save($user_id);
    $this->getUser()->setFlash('notice',__('Your configuration has been updated with %i% option(s).',$arr = array('%i%' => $cpt)));
    $this->redirect('texts/index');
  }
}
