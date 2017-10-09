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
*    Foundation, Inc., 5'.$rank.' Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $path = 'manifestation/statsControlsData?id='.$request->getParameter('id');
    if ( !$request->hasParameter('refresh')
      && ($this->json = liCacher::create($path, true)->useCache($this->getRoute()->getObject()->getCacheTimeout())) !== false ) {
        if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) ) {
            return 'Success';
        }
        
        switch ( $request->getParameter('type', 'json') ) {
            case 'html':
                return 'Success';
            case 'csv':
                return 'Csv';
            default:
                return 'Json';
        }
    }
    
    if ( sfConfig::get('sf_web_debug', false) ) {
      error_log("Refreshing the cache for Manifestation's control statistics (manifestation->id = ".$request->getParameter('id').")");
    }
    
    $this->getContext()->getConfiguration()->loadHelpers('Number');
    
    $q = Doctrine_Query::create()
        ->from('Price p')
          ->leftJoin('p.Translation pt WITH pt.lang = ?', $this->getUser()->getCulture())
          ->leftJoin('p.Tickets tck')
          ->leftJoin('tck.Controls c')
          ->leftJoin('c.Checkpoint cp')
          ->andWhere('tck.manifestation_id = ?', $request->getParameter('id'))
          ->select('p.id, pt.id, pt.lang, pt.name, pt.description, count(c.id) AS count, sum(tck.value) AS amount')
          ->groupBy('p.id, pt.id, pt.lang, pt.name, pt.description, cp.type')
          ->having('count(c.id) > 0')
          ->orderBy('count(c.id) DESC')
    ;
    $rq = $q->getRawSql();
    $rq = str_replace('SELECT ', 'SELECT c2.type AS control_type, ', $rq);
    $con = Doctrine_Manager::getInstance()->connection();
    $st = $con->execute($rq);
    $rows = $st->fetchAll();
    
    $this->json = array(
        'prices' => array(
        ),
    );
    
    foreach ( $rows as $row ) {
        if ( !isset($this->json['prices'][$row['p2__name']]) ) {
            $this->json['prices'][$row['p2__name']] = array(
                'name' => $row['p2__name'],
                'description' => $row['p2__description'],
                'controls' => array(),
            );
        }
        $this->json['prices'][$row['p2__name']]['controls'][$row['control_type']] = array(
            'type' => $row['control_type'],
            'count' => $row['c__0'],
            'amount' => (float)$row['t__1'],
            'amount_txt' => format_currency($row['t__1'],'€'),
        );
    }
  
  if ( sfConfig::get('sf_web_debug', false) )
    error_log("Creating the cache file for Manifestation's statistics (manifestation->id = ".$request->getParameter('id').")");
  liCacher::create($path)
    ->setData($this->json)
    ->writeData();
  
  if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) ) {
    return 'Success';
  }
  
  switch ( $request->getParameter('type', 'json') ) {
    case 'html':
        return 'Success';
    case 'csv':
        return 'Csv';
    default:
        return 'Json';
  }
