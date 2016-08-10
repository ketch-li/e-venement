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
