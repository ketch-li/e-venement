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


}
