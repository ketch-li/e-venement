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
class liOnlineExternalAuthOpenIDConnectPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    require_once sfConfig::get('sf_lib_dir').'/vendor/composer/autoload.php';
    if ( !class_exists('\League\OAuth2\Client\Provider\GenericProvider') )
      throw new liOnlineSaleException('liOnlineExternalAuthOpenIDConnectPlugin: A dependancy is missing: https://github.com/thephpleague/oauth2-client');
  }
}
