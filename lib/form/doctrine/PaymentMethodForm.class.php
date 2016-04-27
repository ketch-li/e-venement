<?php

/**
 * PaymentMethod form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PaymentMethodForm extends BasePaymentMethodForm
{
  public function configure()
  {
    // pictures & co
    foreach ( array('picture_id' => 'Picture') as $field => $rel )
    {
      $this->embedRelation($rel);
      foreach ( array('name', 'type', 'version', 'height', 'width', 'content_encoding') as $fieldName )
        unset($this->widgetSchema[$rel][$fieldName], $this->validatorSchema[$rel][$fieldName]);
      $this->validatorSchema[$rel]['content_file']->setOption('required',false);
      unset($this->widgetSchema[$field], $this->validatorSchema[$field]);
    }
    
    parent::configure();
  }

  public function doSave($con = NULL)
  {
    // picture
    foreach ( array('Picture' => array('width' => 32, 'height' => 32)) as $picform_name => $dimensions )
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
        $this->values[$picform_name]['width']    = $dimensions['width'];
        $this->values[$picform_name]['height']   = $dimensions['height'];
        
        $type = PictureForm::getRealType($file);
        $this->values[$picform_name]['type']     = $type['mime'];
        if ( isset($type['content-encoding']) )
          $this->values[$picform_name]['content_encoding'] = $type['content-encoding'];
        
        $this->values['picture_id'] = 0; // this is a hack to force root object update
      }
    }
    
    return parent::doSave($con);
  }
}
