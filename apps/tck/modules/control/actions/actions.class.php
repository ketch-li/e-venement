<?php

require_once dirname(__FILE__).'/../lib/controlGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/controlGeneratorHelper.class.php';

/**
 * control actions.
 *
 * @package    e-venement
 * @subpackage control
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class controlActions extends autoControlActions
{
  protected function getControlService()
  {
    return sfContext::getInstance()->getContainer()->get('control_service');
  }
  
  protected function formatControl(ticket $ticket)
  {
    return [
      'id' => $ticket->id,
      'price' => $ticket->Price->getFullName(),
      'manifestation' => [
        'id' => $ticket->manifestation_id,
        'name' => $ticket->Manifestation->getName(),
        'entries' => $ticket->Manifestation->getCurrentEntries(),
        'gauge' => $ticket->Manifestation->getGlobalGauge()
      ]
    ];
  }
  
  public function executeCheck(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(['I18N', 'Date']);
    $data = [
      'code' => null,
      'success' => false,
      'message' => null,
      'timestamp' => format_datetime(date('Y-m-d H:i:s'), 'dd/MM/yyyy HH:mm:ss'),
      'ticket' => []
    ];
    $code = $request->getParameter('code');
    
    try
    {
      $control = $this->getControlService()->Control($code);
      
      $data['success'] = true;
      $data['message'] = __('Checkpoint: success.');
      $data['ticket'] = $this->formatControl($control);
    }
    catch(Exception $e)
    {
      $data['code'] = $e->getCode();
      $data['success'] = false;
      $data['message'] = $e->getMessage();
    }
    
    $this->data = $data;
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( $request->hasParameter('light') )
      $this->setLayout('nude');
  }
}
