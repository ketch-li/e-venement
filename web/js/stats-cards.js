if ( LI === undefined )
  var LI = {};
if ( LI.stats === undefined )
  LI.stats = [];

$(document).ready(function(){

  LI.stats.cards();
});

LI.stats.cards = function(){

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
    $.get(chart.attr('data-json-url') + '?type=' + name, function(json){
      var array = [];

      LI.csvData[name].push(json.csvHeaders);

      //build data array depending on approach filter
      $.each(json, function(key, value) {
         if(key != 'csvHeaders'){      
            array.push([value.name, value.nb]);
            LI.csvData[name].push([value.name, value.nb]);
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
