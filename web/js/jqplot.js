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

if ( LI === undefined )
  var LI = {};
if ( LI.chartActions === undefined )
  LI.chartActions = [];
if ( LI.series == undefined )
  LI.series = {};
if ( LI.csvData == undefined )
  LI.csvData = {};
if ( LI.stats === undefined )
  LI.stats = [];

$(document).ready(function(){

  LI.chartActions.exportImg();
  LI.chartActions.exportCsv();
});

LI.chartActions.exportImg = function(){
  
  $('.img-export').click(function(){
    
    var imgData = $(this).parent().siblings('.chart').jqplotToImageStr({});
    var img = $('<img/>').attr('src',imgData);
    window.open(img.attr('src'));
  });
};

LI.chartActions.exportCsv = function(){

  $('.jqplot .actions .record').click(function(){

    var data = LI.clone(LI.csvData[$(this).closest('.jqplot').find('[data-series-name]').attr('data-series-name')]);
    var url = $(this).attr('data-type') == 'csv'
      ? URL.createObjectURL(new Blob([data.join("\n")], { type: "text/csv" }))
      : URL.createObjectURL(new Blob([LI.arrayToTable(data)], { type: "application/vnd.ms-excel" }))
    ;
    $(this).prop('download', LI.slugify(data[0][0]+' '+data[0][1])+'.'+$(this).attr('data-type'))
      .prop('href', url)
    ;
  });
};

LI.stats.pieTooltips = function (str, seriesIndex, pointIndex, plot){

  var total = 0;
  var data = plot.data[seriesIndex];
  var label = data[pointIndex][0];
  var value = data[pointIndex][1];
     
  $(data).each(function(key, value){
    total +=  value[1];     
  });

  var percentage = Math.round(100*value/total);

  return label + ': ' + value + ' (' + percentage + '%) , ' + 'Total: ' + total;
};

LI.stats.resizable = function(plot, name, id){
  $('#resizable-' + name).resizable();

  $('#resizable-' + name).bind('resizestop', function(event, ui) {
    $('#' + id).height($('#resizable-' + name).height()*0.96 -35);
    $('#' + id).width($('#resizable-' + name).width()*0.96);
    plot.replot( { resetAxes:true } );
    LI.stats.fixLegends(plot, id);
  });

  $('#resizable-' + name).children('.actions').css('margin-top', '-15px');
  LI.stats.fixLegends(plot, id);
};

LI.stats.fixLegends = function(plot, id) {
  if ( plot.series[0]._type != 'pie' )
    return;
  
  // Fix legend when too much prices are displayed
  var chartElement = $('#'+id);
  var legendTable = chartElement.find("table.jqplot-table-legend");
  // Not in a class because they are overwritten by jquery.jqplot.css
  legendTable.css({"margin": 0, "right" : 0, "top": 0, "padding": "5px"});
  
  var legendWrapper = $(document.createElement("div"));
  legendWrapper.addClass('legendWrapper')
    .height(chartElement.find(".jqplot-series-canvas").height()-2)
    .width(legendTable.width()+12);
  legendTable.appendTo(legendWrapper);
  legendWrapper.appendTo(chartElement);  
}
