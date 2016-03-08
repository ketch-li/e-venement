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
  $type = $request->getParameter('type', 'std');
  $this->forward404Unless(
     ($transaction = Doctrine::getTable('Transaction')->fetchOneById($request->getParameter('id')))
  && $transaction->contact_id == $this->getUser()->getTransaction()->contact_id
  && !is_null($transaction->contact_id)
  );
  
  $this->getContext()->getConfiguration()->loadHelpers(array('Date', 'Number'));
  
  $fields = array(
    'meta_event',
    'event',
    'manifestation',
    'location',
    'workspace',
    'contact',
    'email',
    'zip',
    'city',
    'country',
    'price',
    'value',
    'taxes',
  );
  
  $this->lines = array();
  $gauges = array();
  
  foreach ( $transaction->Tickets as $ticket )
  {
    if ( !isset($gauges[$ticket->gauge_id]) )
      $gauges[$ticket->gauge_id] = $ticket->Gauge;
    $this->lines[] = array(
      'meta_event'      => (string)$gauges[$ticket->gauge_id]->Manifestation->Event->MetaEvent,
      'event'           => (string)$gauges[$ticket->gauge_id]->Manifestation->Event,
      'manifestation'   => format_datetime($gauges[$ticket->gauge_id]->Manifestation->happens_at),
      'workspace'       => (string)$gauges[$ticket->gauge_id]->Workspace,
      'location'        => (string)$gauges[$ticket->gauge_id]->Manifestation->Location,
      'contact'         => (string)($contact = $ticket->contact_id ? $ticket->DirectContact : $transaction->Contact),
      'email'           => $contact->email,
      'zip'             => $contact->postalcode,
      'city'            => $contact->city,
      'country'         => $contact->country,
      'price'           => $ticket->price_name,
      'value'           => format_currency($ticket->value, '€'),
      'taxes'           => format_currency($ticket->taxes, '€'),
    );
  }
  
  $this->options = array(
    'ms'        => $type == 'ms',
    'fields'    => $fields,
    'noheader'  => false,
  );
  
  if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
    return 'Debug';

  $this->outstream = 'php://output';
  $this->charset   = sfConfig::get('software_internals_charset');
  $this->delimiter = $this->options['ms'] ? ';' : ',';
  $this->enclosure = '"';
  
  sfConfig::set('sf_escaping_strategy', false);
  return 'Success';
