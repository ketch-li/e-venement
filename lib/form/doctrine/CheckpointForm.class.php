<?php

/**
 * Checkpoint form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class CheckpointForm extends BaseCheckpointForm
{
  public function configure()
  {
    $this->widgetSchema['event_id']->setOption('add_empty',true);
    
    if ( sfConfig::get('app_manifestation_exit_on_timeout', false) )
    {
      $choices = $this->widgetSchema['type']->getOption('choices');
      unset($choices[array_search('exit', $choices)]);
      $this->widgetSchema['type']   ->setOption('choices', $choices);
      $this->validatorSchema['type']->setOption('choices', $choices);
    }
  }
}
