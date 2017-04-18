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

/**
 * liValidatorEmail validates emails, and takes care of iphenation
 *
 * @package    symfony
 * @subpackage validator
 * @author     Baptiste SIMON <beta@e-glop.net>
 */
class liValidatorDoctrineGeoFrStreetBase extends sfValidatorString
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    $this->addRequiredOption('form');
  }
  
  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    if (! ($form = $this->getOption('form')) instanceof sfForm )
      throw new sfValidatorError($this, 'You need to provide a correct "form" option', array('form' => $this->getOption('form')));
    
    // sfValidatorString
    $value = parent::doClean($value);
    
    // get back the values
    $values = $form->getTaintedValues();
    
    $city       = strtolower(str_replace(array('STE-', 'ST-'), array('SAINTE-', 'SAINT-'), GeoFrStreetBaseForm::sanitizeSearch($values['city'], false)));
    $postalcode = GeoFrStreetBaseForm::sanitizeSearch($values['postalcode'], false);
    $address    = strtolower(GeoFrStreetBaseForm::sanitizeSearch($value, false));
    
    // if no address can be found in the DB
    $q = Doctrine::getTable('GeoFrStreetBase')->createQuery('sb')
      ->andWhere('LOWER(sb.city)    = ?', $city)
      ->andWhere('LOWER(sb.zip)     = ?', $postalcode)
      ->select('id');
    if ( $q->count() == 0 )
      return $value;
    
    $value = '';
    $tmp  = explode("\n", $address);
    $address = array_pop($tmp);
    $personaladdr = implode("\n", $tmp);
    
    // if an address can match, it must match!!
    $q = Doctrine::getTable('GeoFrStreetBase')->createQuery('sb')
      ->andWhere('LOWER(sb.city)    = ?', $city)
      ->andWhere('LOWER(sb.zip)     = ?', $postalcode)
      ->andWhere('LOWER(sb.address) = ?', $address)
      ->limit(1)
    ;
    
    if ( $personaladdr )
      $value = $personaladdr."\n";

    if ( $sb = $q->fetchOne() )
      return $value.$sb->address;
    
    // no luck...
    throw new sfValidatorError($this, 'invalid', array('value' => $value));
  }
}
