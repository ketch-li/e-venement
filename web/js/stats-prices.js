/**********************************************************************************
*
*     This file is part of e-venement.
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
if ( LI === undefined )
  var LI = {};
if ( LI.stats === undefined )
  LI.stats = [];

$(document).ready(function(){

  LI.stats.prices();
});

LI.stats.prices = function(){

	$('#content .jqplot').each(function(){

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

    //retrieve stats
    $.get(chart.attr('data-json-url') + '?id=' + name, function(json){
      var array = [];
      var total = 0;

      LI.csvData[name].push(json.csvHeaders);

      $.each(json, function(key, value) {

        if(key !== 'csvHeaders'){
          array.push([value.name, value.nb]);
          LI.csvData[name].push([value.name, value.nb, value.percent]);
        }
      });

      //init jqplot with data array
      var plot = $.jqplot(id, [array], {
        seriesDefaults: {
          rendererOptions: {
            fill: true,
            showDataLabels: true,
            slideMargin: 4,
            lineWidth: 5
          },
          renderer: $.jqplot.PieRenderer
        },
        highlighter: {
          sizeAdjust: 2,
          show: true,
          useAxesFormatters: false,
          tooltipFormatString: '%s',
          tooltipContentEditor: LI.stats.pieTooltips
        },
        cursor: {
          showTooltip: false,
          show: true
        },
        legend: {
          show: true,
          location: 'e'
        },
        captureRightClick: true
      });

      LI.stats.resizable(plot, name, id);
    });
  });
};