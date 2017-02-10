<?php

require_once dirname(__FILE__).'/../lib/gauge_timeoutGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/gauge_timeoutGeneratorHelper.class.php';

/**
 * gauge_timeout actions.
 *
 * @package    e-venement
 * @subpackage gauge_timeout
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class gauge_timeoutActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new OptionGaugeTimeoutForm();
    $this->options = $this->form->getDBOptions();
    $this->form->setDefaults($this->options);
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->form = new OptionGaugeTimeoutForm();
    $this->form->bind($request->getPostParameters());

    if ( !$this->form->isValid() )
    {
      $this->getUser()->setFlash('error',__('Your form cannot be validated.'));
      return $this->setTemplate('index');
    }

    $user_id = NULL;

    $cpt = $this->form->save($user_id);
    $this->getUser()->setFlash('notice',__('Your configuration has been updated with %i% option(s).',$arr = array('%i%' => $cpt)));
    $this->redirect('gauge_timeout/index');
  }

  /**
   * Creates an exit checkpoint for all events that:
   *  - doesn't have an exit checkpoint yet
   *  - have an entrance checkpoint created after a given timestamp
   *
   * @param sfWebRequest $request     $request['since'] is the limit Unix timestamp
   */
  public function executeAutoExit(sfWebRequest $request)
  {
    $since = date('Y-m-d H:i:s', (int)$request->getParameter('since'));
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    // find out every checkpoint which has no exit sibling
    $q = Doctrine::getTable('Control')->createQuery('c')
      ->leftJoin('c.Checkpoint cp WITH cp.type = ?', 'entrance')
      ->leftJoin('cp.Event e')      
      ->leftJoin('c.Ticket tck')
      ->leftJoin('tck.Controls c2 WITH c2.id != c.id')
      ->leftJoin('c2.Checkpoint cp2 WITH cp2.type = ?', 'exit')      
      ->andWhere('c2.id IS NULL')
      ->andWhere('c.created_at < ?', $since)
      ->select('c.*, cp.*, e.*')
    ;
    
    foreach ( $q->execute() as $control )
    {
      // getting back the first exit checkpoint foundable
      $checkpoint = null;
      foreach ( $control->Checkpoint->Event->Checkpoints as $cp )
      if ( $cp->type == 'exit' )
      {
        $checkpoint = $cp;
        break;
      }
      
      // creating an exit checkpoint if not present yet
      if ( !$checkpoint )
      {
        $checkpoint = new Checkpoint;
        $checkpoint->Event = $control->Checkpoint->Event;
        $checkpoint->type = 'exit';
        $checkpoint->name = __('Timeout');
        $checkpoint->description = __('This exit checkpoint has been created automatically after gauge timeout.');
        $checkpoint->save();
        $control->Checkpoint->Event->Checkpoints->Add($checkpoint);
      }
      
      $c = new Control;
      $c->Checkpoint = $checkpoint;
      $c->Ticket = $control->Ticket;
      $c->automatic = true;
      $c->save();
    }
  }

}
