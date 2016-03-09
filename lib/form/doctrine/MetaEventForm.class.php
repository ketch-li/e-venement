<?php

/**
 * MetaEvent form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MetaEventForm extends BaseMetaEventForm
{
  public function configure()
  {
    $this->widgetSchema   ['users_list']
      ->setOption('order_by',array('username',''))
      ->setOption('expanded',true)
      ->setOption('query', $q = Doctrine::getTable('sfGuardUser')->createQuery('u'))
    ;
    if ( !$this->object->isNew() )
      $q->andWhere('me.id IS NOT NULL AND me.id = ? OR u.is_active = ?', array($this->object->id, true));
    else
      $q->andWhere('u.is_active = ?', true);
    
    $this->embedRelation('Picture');
    foreach ( array('name', 'type', 'version', 'height', 'width', 'content_encoding') as $fieldName )
      unset($this->widgetSchema['Picture'][$fieldName], $this->validatorSchema['Picture'][$fieldName]);
    $this->validatorSchema['Picture']['content_file']->setOption('required',false);
    unset($this->widgetSchema['picture_id'], $this->validatorSchema['picture_id']);
  }
  
  public function doSave($con = NULL)
  {
    // picture
    foreach ( array('Picture' => array('width' => 200, 'height' => 300)) as $picform_name => $dimensions )
    {
      $file = $this->values[$picform_name]['content_file'];
      unset($this->values[$picform_name]['content_file']);
      
      if (!( $file instanceof sfValidatedFile ))
        unset($this->embeddedForms[$picform_name]);
      else
      {
        // data translation
        $this->values[$picform_name]['content']  = base64_encode(file_get_contents($file->getTempName()));
        $this->values[$picform_name]['name']     = $file->getOriginalName();
        //$this->values[$picform_name]['width']    = $dimensions['width'];
        //$this->values[$picform_name]['height']   = $dimensions['height'];

        $type = PictureForm::getRealType($file);
        $this->values[$picform_name]['type']     = $type['mime'];
        if ( isset($type['content-encoding']) )
          $this->values[$picform_name]['content_encoding'] = $type['content-encoding'];

        $this->values['updated_at'] = date('Y-m-d H:i:s'); // this is a hack to force root object update
      }
    }
    
    return parent::doSave($con);
  }
}
