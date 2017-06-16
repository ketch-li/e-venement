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
        var title = $(this).find('h2') ? $(this).find('h2').text() : '';
        LI.csvData[name] = [
          [
            $(this).find('#csvTitle').text(),
            title
          ],
        ]; 
        
        $.get(chart.attr('data-json-url'), function (json) {
            var array = [];
            var ordered = [];
            var printed = [];
            var labels = [];
            var over = [];
            LI.stats.attendanceLegends = json.legends;
            LI.csvData[name].push(json.csvHeaders);

            $.each(json, function (key, manif) {
                if(key !== 'csvHeaders' && key !== 'legends'){

                    array.push(manif.free);
                    ordered.push(manif.ordered);
                    printed.push(manif.printed);
                    labels.push(manif.name);
                    over.push(manif.over);

                    var csvData = [
                        manif.event_name,
                        manif.dotw,
                        manif.date,
                        manif.time, 
                        manif.location_name, 
                        manif.location_city, 
                        manif.gauge, 
                        manif.printed, 
                        manif.printed_with_payment, 
                        manif.printed_gifts, 
                        manif.printed_deposits, 
                        manif.ordered,
                        manif.asked, 
                        manif.free,
                        manif.over,
                        manif.printed_percentage,
                        manif.printed_with_payment_percentage,
                        manif.printed_gifts_percentage,
                        manif.printed_deposits_percentage,
                        manif.ordered_percentage,
                        manif.asked_percentage,
                        manif.free_percentage,
                        manif.cashflow,
                        manif.meta_event,
                        manif.event_category
                    ];
                    
                    if(manif.asked === 'false'){
                        csvData.splice(12, 1);
                        csvData.splice(18, 1);
                    }
                    
                    LI.csvData[name].push('"'+csvData.join('","')+'"');
                } 
            });
            
            var barOptions = Object.keys(json).length < 5 ? { barWidth: 200 } : {};
            barOptions['fillToZero'] = true;

            var seriesLegend = [
              { label: json.legends.printed, color: "#FF0000" }, 
              { label: json.legends.ordered, color: "#FFA500" }, 
              { label: json.legends.available, color: "#00FF00" },
              { label: json.legends.over, color: "#666666" }
            ];

            var plot = $.jqplot(id, [printed, ordered, array, over], {
                height: 800,
                stackSeries: true,
                seriesDefaults: {
                    renderer: $.jqplot.BarRenderer,
                    rendererOptions: barOptions
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

            LI.stats.resizable(plot, name, id);
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
        case 3:
            label = LI.stats.attendanceLegends.over;
            break;
        default:
            label = LI.stats.attendanceLegends.available;
    }

    return label + ': ' + plot.data[seriesIndex][pointIndex] + ', '
         + LI.stats.attendanceLegends.total + ': ' + total;
};
