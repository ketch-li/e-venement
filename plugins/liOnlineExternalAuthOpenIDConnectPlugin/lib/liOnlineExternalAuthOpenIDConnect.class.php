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

class liOnlineExternalAuthOpenIDConnect extends OpenIdConnectProvider
{
  protected $config, $redirectUri, $scopes = [], $scopeSeparator = ' ', $provider, $accessToken;
  
  public function getConfig($key = NULL)
  {
    if ( $key === NULL )
      return $this->config;
    return $this->config[$key];
  }
  
  /**
   * @inheritdoc
   **/
  public function __construct(array $options = [], array $collaborators = [])
  {
    $this->config = json_decode(file_get_contents(sfConfig::get('app_openidconnect_wellknownurl', null), false, stream_context_create(array(
      'ssl' => array('verify_peer' => false)
    ))), true);
    
    $sf_context = sfContext::getInstance();
    $sf_context->getConfiguration()->loadHelpers(array('CrossAppLink', 'Url'));
    
    if ( is_array($urls = sfConfig::get('app_openidconnect_redirect_urls', array()) )
      $url = $sf_context->getModuleName() == 'cart' && $sf_context->getActionName() == 'order'
        ? $urls['order']
        : $urls['login'];
    else
      $url = $urls;
    
    parent::__construct([
      'clientId'                => sfConfig::get('app_openidconnect_client_id', null),
      'clientSecret'            => sfConfig::get('app_openidconnect_client_secret', null),
      'redirectUri'             => public_path($url,true),
      'urlAuthorize'            => $this->config['authorization_endpoint'],
      'urlAccessToken'          => $this->config['token_endpoint'],
      'urlResourceOwnerDetails' => $this->config['userinfo_endpoint'],
      'scopes'                  => $this->config['scopes_supported'],
      'scopeSeparator'          => sfConfig::get('app_openidconnect_scope_separator', ' '),
      'verify'                  => sfConfig::get('app_openidconnect_verify_ssl'),
    ]);
  }
  
  /**
   * function retrieveAccessToken
   * returns \League\OAuth2\Client\Token\AccessToken OAuth access token
   **/
  public function retrieveAccessToken(sfWebRequest $request)
  {
    $this->accessToken = parent::getAccessToken('authorization_code', [
      'code' => $request->getParameter('code'),
    ]);
    return $this;
  }
  
  /**
   * @inheritdoc
   **/
  public function getInternalResourceOwner()
  {
    return parent::getResourceOwner($this->accessToken);
  }
  
  /**
   * function getResourceOwnerData
   * @returns array of data
   **/
  public function getResourceOwnerData()
  {
    return $this->getInternalResourceOwner()->toArray();
  }
}
