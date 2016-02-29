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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class liCacher
{
  protected $data = NULL;
  protected $path = NULL;
  protected $cache = NULL;
  protected $domain = 'cache_files';
  const refreshKeyword = 'refresh';
  
  static public function create($path, $escape = false)
  {
    if ( $path instanceof sfWebRequest && $path->getUri() )
      return new self(self::componePath($path->getUri()));
    return new self($escape ? self::componePath($path) : $path);
  }
  static public function componePath($uri)
  {
    $uri = preg_replace('![?&/]{0,1}'.self::refreshKeyword.'([=/][\w\d]*){0,1}!', '', $uri);
    $uri = preg_replace('![?&/]+$!', '', $uri);
    
    return $uri;
  }
  static public function requiresRefresh(sfWebRequest $request)
  {
    return $request->hasParameter(self::refreshKeyword);
  }
  
  public function __construct($path)
  {
    $this->setPath($path);
  }
  
  public function setPath($path)
  {
    $this->path = $path;
    return $this;
  }
  public function getPath()
  {
    return $this->path;
  }
  
  public function __toString()
  {
    return serialize($this->getData());
  }
  
  public function getDomain()
  {
    return $this->domain;
  }
  public function setDomain($domain)
  {
    $this->domain = $domain;
    return $this;
  }
  
  public function getData()
  {
    return $this->data;
  }
  public function setData($data)
  {
    $this->data = $data;
    return $this;
  }
  
  protected function getDBCache()
  {
    if ( $this->cache )
      return $this->cache;
    
    if (!( $this->cache = Doctrine::getTable('Cache')->createQuery('c')
      ->andWhere('c.identifier = ?', $this->getPath())
      ->andWhere('c.domain = ?', $this->domain)
      ->fetchOne() ))
    {
      $this->cache = new Cache;
      $this->cache->domain = $this->domain;
      $this->cache->identifier = $this->getPath();
    }
    
    return $this->cache;
  }
  public function clear()
  {
    $this->getDBCache()->delete();
    return $this;
  }
  public function loadData()
  {
    $this->setData(unserialize($this->getDBCache()->content));
    return $this;
  }
  public function writeData()
  {
    $this->getDBCache()->content = (string)$this;
    $this->getDBCache()->updated_at = date('Y-m-d H:i:s');
    $this->getDBCache()->save();
    
    return $this;
  }
  
  public function needsRefresh($interval = NULL)
  {
    if ( is_null($interval) )
      $interval = sfConfig::get('app_cacher_timeout', '1 day ago');
    
    if ( $this->getDBCache()->isNew() )
      return true;
    
    $ctime = strtotime($this->getDBCache()->updated_at);
    if ( $ctime !== false && $ctime < strtotime($interval) )
      return true;
    
    return false;
  }
  
  public function useCache($interval = NULL)
  {
    return !$this->needsRefresh($interval) ? $this->loadData()->getData() : false;
  }
}
