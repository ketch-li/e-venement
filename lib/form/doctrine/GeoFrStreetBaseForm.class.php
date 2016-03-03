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
  public $noTimestampableUnset = true;

  public function configure()
  {
      $this->noTimestampableUnset = true;

//    parent::configure();
//    $this->validatorSchema['updated_at']->setOption('required', false);
//    $this->validatorSchema['created_at']->setOption('required', false);
  }
}
