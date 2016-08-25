<?php
/**********************************************************************************
*
*       This file is part of e-venement.
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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
$json = $sf_data->getRaw('lines'); 
$json['csvHeaders'] = [
    __('Event'),
    __('Day'),
    __('Date'),
    __('Time'),
    __('Location'),
    __('City'),
    __('Gauge'),
    __('Printed'),
    __('Printed with a payment'),
    __('Printed for free'),
    __('Printed for deposit'),
    __('Ordered'),
    __('Asked'),
    __('Free'),
    __('Printed').' %',
    __('Printed with a payment').' %',
    __('Printed for free').' %',
    __('Printed for deposit').' %',
    __('Ordered').' %',
    __('Asked').' %',
    __('Free').' %',
    __('Cashflow'),
    __('Meta-event'),
    __('Event category'),
];

if ( !sfConfig::get('project_tickets_count_demands',false) )
{
    unset($json['csvHeaders'][19],$json['csvHeaders'][12]);
    $json['csvHeaders'] = array_values($json['csvHeaders']);
}

$json['legends'] = array(
    'total'     => __('Total'), 
    'ordered'   => __('Ordered'),
    'printed'   => __('Printed'),
    'available' => __('Free')
);

echo json_encode($json);
?>