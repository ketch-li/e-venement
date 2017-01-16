<?php

/**
 * PluginAttachment
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    symfony
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginAttachment extends BaseAttachment
{
  public function preSave($event)
  {
    if ( substr($this->filename, 0, 3) == 'db:' )
      return parent::preSave($event);
    
    $real_filename = substr($this->filename, 0, 1) === '/'
      ? $this->filename
      : sfConfig::get('sf_upload_dir').'/'.$this->filename
    ;
    
    if ( !$this->size )
      $this->size = filesize($real_filename);
    if ( !$this->mime_type )
    {
      $finfo = finfo_open(FILEINFO_MIME);
      $this->mime_type = finfo_file($finfo, $real_filename);
      finfo_close($finfo);
    }
    
    return parent::preSave($event);
  }
}
