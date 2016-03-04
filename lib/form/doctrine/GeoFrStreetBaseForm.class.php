<?php

/**
 * GeoFrStreetBase form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class GeoFrStreetBaseForm extends BaseGeoFrStreetBaseForm
{
  public function configure()
  {
    $this->disableCSRFProtection();
  }

  public function setup()
  {
    $this->setValidators(array(
      'address'    => new sfValidatorString(array('max_length' => 255)),
      'locality'   => new sfValidatorBoolean(array('required' => false)),
      'num'        => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'city'       => new sfValidatorString(array('max_length' => 255)),
      'zip'        => new sfValidatorString(array('max_length' => 7)),
      'rivoli'     => new sfValidatorString(array('max_length' => 4, 'required' => false)),
      'iris2008'   => new sfValidatorString(array('max_length' => 9, 'required' => false)),
      'longitude'  => new sfValidatorNumber(array('required' => false)),
      'latitude'   => new sfValidatorNumber(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('geo_fr_street_base[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();
  }

  public function changeObject($object)
  {
    $class = $this->getModelName();
    if (!$object)
    {
      $this->object = new $class();
    }
    else
    {
      if (!$object instanceof $class)
      {
        throw new sfException(sprintf('The "%s" form only accepts a "%s" object.', get_class($this), $class));
      }

      $this->object = $object;
      $this->isNew = !$this->getObject()->exists();
    }

    $this->updateDefaultsFromObject();
  }
}
