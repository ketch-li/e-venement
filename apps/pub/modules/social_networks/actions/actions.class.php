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
*    Copyright (c) 2016 Marcos Bezerra de Menezes <marcos.bezerra AT libre-informatique.fr>
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/

class social_networksActions extends sfActions
{
  private $facebook_api_url = 'https://graph.facebook.com/?ids=';
  private $twitter_api_url = 'https://urls.api.twitter.com/1/urls/count.json?url=';
  private $google_api_url = 'https://plusone.google.com/_/+1/fastbutton?url=';

  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }

  public function executeCount(sfWebRequest $request)
  {
    sfConfig::set('sf_web_debug',false);
    $this->getResponse()->setContentType('application/json');
    $this->getResponse()->setHttpHeader('Access-Control-Allow-Origin', '*');

    $url = $request->getParameter('url');

    $counters = array();
    if ( sfConfig::get('app_social_media_display', false) ) {
      $networks = sfConfig::get('app_social_media_networks', array());
      foreach( $networks as $network => $options)
        $counters[$network] = $this->count($network, $url);
    }

    return $this->renderText(json_encode($counters));
  }

  /**
   * Returns the url share count for a given network (-1 on failure)
   * @param string $network
   * @param string $url
   * @return int
   */
  private function count($network, $url)
  {
    switch($network) {
      case 'facebook':
        $file_contents = @file_get_contents($this->facebook_api_url . $url);
        $response      = json_decode($file_contents, true);
        if (isset($response[$url]['shares'])) {
          return intval($response[$url]['shares']);
        }
        return -1;
      case 'twitter':
        return -1;
        // This does not work anymore since since November 20th, 2015
        /* $file_contents = @file_get_contents($this->twitter_api_url . urlencode($url));
        $response      = json_decode($file_contents, true);
        if (isset($response['count'])) {
          return intval($response['count']);
        }
        return -1; */
      case 'googleplus':
        $file_contents = @file_get_contents($this->google_api_url . urlencode($url));
        preg_match('/window\.__SSR = {c: ([\d]+)/', $file_contents, $response);
        if (isset($response[0])) {
          $total = intval(str_replace('window.__SSR = {c: ', '', $response[0]));
          return $total;
        }
        return -1;
      default:
        return -1;
    }
  }
}
