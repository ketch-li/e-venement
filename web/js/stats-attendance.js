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
if (LI === undefined)
    var LI = {};
if (LI.stats === undefined)
    LI.stats = [];

$(document).ready(function () {

    LI.stats.attendance();
});

LI.stats.attendanceLegends = [];

LI.stats.attendance = function () {
    $('#content .jqplot').each(function () {
        var chart = $(this).find('.chart')
        var name = chart.attr('data-series-name');
        var id = chart.prop('id');
        var title = $(this).find('h2').prop('title') ? $(this).find('h2').prop('title') + ': ' : '';
        
        LI.csvData[name] = [
            [
                title,
                $(this).find('h2').text()
            ]
        ];
        
        $.get(chart.attr('data-json-url'), function (json) {
            var array = [];
            var ordered = [];
            var printed = [];
            var labels = [];
            LI.stats.attendanceLegends = json.legends;
            LI.csvData[name].push(json.csvHeaders);

            $.each(json, function (key, value) {

                if(key !== 'csvHeaders' && key !== 'legends'){

                    var eventName = value.Event.Translation.fr.name;

                    array.push(value.free);
                    ordered.push(value.ordered);
                    printed.push(value.printed);
                    labels.push(eventName);

                    var csvData = [
                        eventName, 
                        value.dotw,
                        value.date,
                        value.time, 
                        value.location_name, 
                        value.location_city, 
                        value.gauge, 
                        value.printed, 
                        value.printed_with_payment, 
                        value.printed_gifts, 
                        value.printed_deposits, 
                        value.ordered,
                        value.asked, 
                        value.free,
                        value.printed_percentage,
                        value.printed_with_payment_percentage,
                        value.printed_gifts_percentage,
                        value.printed_deposits_percentage,
                        value.ordered_percentage,
                        value.asked_percentage,
                        value.free_percentage,
                        value.cashflow,
                        value.meta_event,
                        value.event_category
                    ];
                    
                    if(value.asked === 'false'){
                        csvData.splice(12, 1);
                        csvData.splice(18, 1);
                    }
                    
                    LI.csvData[name].push(csvData);
                } 
            });

            var seriesLegend = [{label: json.legends.printed, color: "#FF0000"}, {label: json.legends.ordered, color: "#FFA500"}, {label: json.legends.available, color: "#00FF00"}];

            $.jqplot(id, [printed, ordered, array], {
                height: 800,
                stackSeries: true,
                seriesDefaults: {
                    renderer: $.jqplot.BarRenderer,
                },
                series: seriesLegend,
                axes: {
                    xaxis: {
                        renderer: $.jqplot.CategoryAxisRenderer,
                        tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                        ticks: labels,
                        tickOptions: {
                            angle: 50
                        }
                    },
                    yaxis: {
                        //max: 1000
                    }
                },
                highlighter: {
                    sizeAdjust: 2,
                    show: true,
                    tooltipContentEditor: LI.stats.attendanceTooltips
                },
                legend: {
                    show: true,
                    location: 'n',
                    placement: 'inside'
                },
                cursor: {
                    show: true,
                    showTooltip: false,
                    zoom: true
                },
                captureRightClick: true
            });
        });
    });
};

LI.stats.attendanceTooltips = function (str, seriesIndex, pointIndex, plot){
    
    var label;
    var total = plot.data[0][pointIndex] + plot.data[1][pointIndex] + plot.data[2][pointIndex];

    switch(seriesIndex){
        case 0:
            label = LI.stats.attendanceLegends.printed;
            break;
        case 1:
            label = LI.stats.attendanceLegends.ordered;
            break;
        default:
            label = LI.stats.attendanceLegends.available;
    }

    return label + ': ' + plot.data[seriesIndex][pointIndex] + ', '
         + LI.stats.attendanceLegends.total + ': ' + total;
};