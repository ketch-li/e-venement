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

class liOnlineExternalAuthOpenIDConnectActions
{
  protected $provider, $action, $request;
  const READY     = 1;
  const REDIRECT  = 2;
  const ERROR     = 255;
  
  public function __construct(sfAction $action, sfWebRequest $request)
  {
    $this->provider = new liOnlineExternalAuthOpenIDConnect;
    $this->action = $action;
    $this->request = $request;
  }
  
  public function remoteLogout()
  {
    $homepage = url_for('@homepage', true);
    
    if ( $urlLogout = $this->provider->getBaseUrl() )
    {
      $this->action->redirect($urlLogout.'/?url='.base64_encode($homepage).'&logout=1');
    }
    else
    {
      $this->action->redirect($homepage);
    }
  }
  
  public function remoteAuthenticate()
  {
    $this->action->redirect($this->provider->getAuthorizationUrl());
  }
  
  public function getUserInformations()
  {
    return $this->provider
      ->retrieveAccessToken($this->request)
      ->getResourceOwnerData()
    ;
  }
  
  public function routing()
  {
    // If we don't have an authorization code then get one
    if ( !$this->request->getParameter('code', false) && !$this->request->getParameter('error', false) )
    {
      $url = $this->provider->getAuthorizationUrl();
      $this->action->getUser()->setAttribute('li_online_external_auth_state', $this->provider->getState());
      $this->action->redirect($url);
      return self::REDIRECT;
    }
    
    // We have an error
    if ( !$this->request->getParameter('code', false) )
    {
      throw new liOnlineSaleException(sprintf('liOnlineExternalAuthOpenIDConnectPlugin: %s, %s', $this->request->getParameter('error'), $this->request->getParameter('error_description')));
      return self::ERROR;
    }
    
    // Check given state against previously stored one to mitigate CSRF attack
    if ( !$this->request->getParameter('state', false)
      || !( $this->action->getUser()->getAttribute('li_online_external_auth_state') && $this->action->getUser()->getAttribute('li_online_external_auth_state') === $this->request->getParameter('state', false) )
    )
    {
      $this->action->getUser()->getAttributeHolder()->remove('li_online_external_auth_state');
      throw new liOnlineSaleException('liOnlineExternalAuthOpenIDConnectPlugin: Invalid state.');
      return self::ERROR;
    }
    
    return self::READY;
  }
  
  public function getUserData()
  {
    return $this->provider
      ->retrieveAccessToken($this->request)
      ->getResourceOwnerData()
    ;
  }
  public function getContact()
  {
    $data = $this->getUserData();
    $q = Doctrine::getTable('Contact')->createQuery('c')
      ->leftJoin('c.OpenId oid')
      ->andWhere('oid.id = ?', $data['sub'])
    ;
    
    if (!( $contact = $q->fetchOne() ))
    {
      $contact = new Contact;
      $contact->OpenId = new ContactOpenIdConnect;
      $contact->OpenId->id = $data['sub'];
    }
    
    foreach ( sfConfig::get('app_openidconnect_data_matching', array('email' => 'email', 'name' => 'name')) as $openid => $field )
    if ( isset($data[$openid]) )
      $contact->$field = $data[$openid];
    $contact->save();
    
    return $contact;
  }
}
