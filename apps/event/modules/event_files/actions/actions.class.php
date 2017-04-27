<?php

require_once dirname(__FILE__).'/../lib/event_filesGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/event_filesGeneratorHelper.class.php';

/**
 * event_files actions.
 *
 * @package    symfony
 * @subpackage event_files
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class event_filesActions extends autoEvent_filesActions
{
  
  public function executeDel(sfWebRequest $request)
  {
    $this->json = array();
    $this->json['error'] = 'success'; 
    $id = intval($request->getParameter('id'));
    $mid = intval($request->getParameter('mid'));
    
    Doctrine_Query::create()
      ->delete()
      ->from('EventPicture ep')
      ->where('ep.picture_id = ?', $id)
      ->andWhere('ep.event_id = ?', $mid)
      ->execute();
  }
  
  public function executeAdd(sfWebRequest $request)
  {
    $this->form = $this->getForm();
    $values = $request->getPostParameters();
    $id = $request->getPostParameter('id');
    $this->json = array();
    $this->json['error'] = 'error';
    
    foreach ( $request->getFiles() as $upload => $content )
    {
      if ( $content['error'] == 0 ) 
      {
        $file = new Picture();
        $file->name = $content['name'];
        $file->content = base64_encode(file_get_contents($content['tmp_name']));
        $file->content_encoding = $content['type'];
        $file->type = 'event';
        $file->save();
        
        $mfile = new EventPicture();
        $mfile->event_id = $id;
        $mfile->picture_id = $file->id;
        $mfile->save();
        
        $this->json['id'] = $file->id;
        $this->json['url'] = $file->getUrl();
        $this->json['name'] = $file->name;
        $this->json['error'] = 'success';
      }
      else 
      {
        $this->json['error'] = $content['error'];
      }
    }  
  }
  
  
  protected function getForm()
  {
      sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
      return new sfForm;
  }
  
}
