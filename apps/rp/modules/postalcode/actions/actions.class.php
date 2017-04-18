<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2016 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/postalcodeGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/postalcodeGeneratorHelper.class.php';

/**
 * postalcode actions.
 *
 * @package    e-venement
 * @subpackage postalcode
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postalcodeActions extends autoPostalcodeActions
{
  public function executeAjax(sfWebRequest $request)
  {
    $this->postalcodes = array();
    if ( strlen($request->getParameter('q')) > 2 )
    {
      $charset = sfConfig::get('software_internals_charset');
      $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
      
      $q = Doctrine::getTable('Postalcode')
        ->createQuery()
        ->orderBy('city')
        ->andWhere('postalcode LIKE ?',$request->getParameter('q').'%');
      $postalcodes = $q->execute();
      
      $this->postalcodes['...'] = '...';      
      foreach ( $postalcodes as $cp )
        $this->postalcodes[$cp->city.' %%'.$cp->postalcode.'%%'] = (string)$cp;
      return 'Success';
    }
    
    // empty
    return 'Success';
  }

  public function executeAjaxPub(sfWebRequest $request)
    {
      $this->setTemplate('ajax');
      $this->postalcodes = array();
      if ( strlen($request->getParameter('q')) > 2 )
      {
        $charset = sfConfig::get('software_internals_charset');
        $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
        
        $q = Doctrine::getTable('Postalcode')
          ->createQuery('p')
          ->select('p.postalcode, trim(p.city) as city, (SELECT count(*) FROM geoFrStreetBase s WHERE s.city = trim(p.city) AND s.zip = p.postalcode) as nb_streets')
          ->orderBy('p.city')
          ->andWhere('p.postalcode LIKE ?',$request->getParameter('q').'%');
        $postalcodes = $q->execute();
        
        $this->postalcodes['...'] = '...';
        foreach ( $postalcodes as $cp ) {
          $this->postalcodes[$key = sprintf('[%s][%s][%s]', $cp->city, $cp->postalcode, $cp->nb_streets)] = $cp->city;
        }

        if ($request->hasParameter('debug')) {
          $this->getResponse()->setContentType('text/html');
          sfConfig::set('sf_debug',true);
          $this->setLayout('layout');
        }
      }

      return 'Success';
    }
}
