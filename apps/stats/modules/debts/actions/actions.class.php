<?php

/**
 * activity actions.
 *
 * @package    e-venement
 * @subpackage activity
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class debtsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    if ( $request->hasParameter('criterias') )
    {
      $this->criterias = $request->getParameter('criterias');
      $this->getUser()->setAttribute('stats.criterias',$this->criterias,'admin_module');
      $this->redirect($this->getContext()->getModuleName().'/index');
    }
    
    $this->form = new StatsCriteriasForm();
    $this->form->addEventCriterias();
    $this->form->addIntervalCriteria();
    
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
  }
  
  public function executeJson(sfWebRequest $request)
  {
    $this->lines = $this->getRawData();
  }
  
  protected function getRawData()
  {
    //$criterias = $this->getUser()->getAttribute('stats.criterias',array(),'admin_module');
    $beginning = strtotime('1 year ago'); //,strtotime('next month',strtotime(date('01-d-Y'))));
    for ( $days = array(
        strtotime(date('01-m-Y',strtotime('next month'))),
        strtotime(date('20-m-Y')),
        strtotime(date('10-m-Y')),
      )
      ; $days[count($days)-1] >= $beginning
      ; $days[] = strtotime('1 month ago',$days[count($days)-3]) );
    foreach ( $days as $key => $day )
      $days[$key] = date('Y-m-d',$day);
    
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $q = "SELECT d.date,
            (SELECT CASE WHEN COUNT(tck.id) = 0 THEN 0 ELSE SUM(tck.value + CASE WHEN tck.taxes IS NULL THEN 0 ELSE tck.taxes END) END
              FROM Ticket tck
              WHERE tck.duplicating IS NULL
                AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.cancelling IS NOT NULL)
                AND (tck.cancelling IS NULL AND (tck.printed_at IS NOT NULL AND tck.printed_at < d.date::date OR tck.integrated_at IS NOT NULL AND tck.integrated_at < d.date::date) OR tck.cancelling IS NOT NULL AND tck.created_at < d.date::date)
            ) +
            (SELECT (CASE WHEN COUNT(pdt.id) = 0 THEN 0 ELSE SUM(pdt.value) END)
              FROM bought_product pdt
              WHERE pdt.integrated_at IS NOT NULL
                AND pdt.integrated_at < d.date::date
            ) AS outcome,
            (SELECT sum(value) FROM payment p  WHERE p.created_at <= d.date::date) AS income
          FROM (SELECT '".implode("'::date AS date UNION SELECT '",$days)."'::date AS date) AS d
          ORDER BY date";
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    
    return $stmt->fetchAll();
  }
}
