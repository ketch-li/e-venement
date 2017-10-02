<?php

require_once dirname(__FILE__).'/../lib/categoryGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/categoryGeneratorHelper.class.php';

/**
 * category actions.
 *
 * @package    e-venement
 * @subpackage category
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class categoryActions extends autoCategoryActions
{
  public function executeShow(sfWebRequest $request)
  {
    $this->redirect('category/edit?id='.$request->getParameter('id'));
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    if ( $request->hasParameter('debug') && $this->getContext()->getConfiguration()->getEnvironment() == 'dev' )
    {
      $this->getResponse()->setContentType('text/html');
      sfConfig::set('sf_debug',true);
      $this->setLayout('layout');
    }
    else
    {
      sfConfig::set('sf_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
    
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],strtolower($request->getParameter('q')));
    
    $lang = $this->getUser()->getCulture();
    
    $q = Doctrine::getTable('ProductCategory')->createQuery('pc')
      ->select("pc.*, pct.*, (CASE WHEN ppc.id IS NULL THEN pct.name ELSE ppt.name||' ' END) AS parent")
      ->leftJoin("ppc.Translation ppt WITH ppt.lang = '$lang'")
      ->limit($request->getParameter('limit', $request->getParameter('max', 10)))
      ->orderBy("parent, pct.name")
    ;
    
    if ( $search )
    $q = Doctrine_Core::getTable('ProductCategory')
      ->search($search.'*',$q);

    $this->getContext()->getConfiguration()->loadHelpers('Url');
    $this->cats = array();
    foreach ( $q->execute() as $cat )
    //if ( $cat->isAccessibleBy($this->getUser()) )
    if ( $request->hasParameter('keep-order') )
    {
      $this->cats[] = array(
        'id'    => $cat->id,
        'color' => (string)$cat->Color,
        'name'  => ($cat->parent == (string)$cat ? '' : $cat->parent.'- ').$cat,
        'gauge_url' => NULL,
      );
    }
    else
      $this->cats[$cat->id] = $request->hasParameter('with_colors')
        ? array('name' => (string)$cat, 'color' => (string)$cat->Color,)
        : ($cat->parent == (string)$cat ? '' : $cat->parent.'- ').$cat;
  }
}
