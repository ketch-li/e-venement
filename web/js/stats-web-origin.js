if ( LI === undefined )
  var LI = {};
if ( LI.stats === undefined )
  LI.stats = [];

$(document).ready(function(){

  LI.stats.webOrigin();
});

LI.stats.webOrigin = function(){
  $('#content .jqplot').each(function(){
    var chart = $(this).find('.chart')
    var name = chart.attr('data-series-name');
    var id = chart.prop('id');
    var title = $(this).find('h2').prop('title') ? $(this).find('h2').prop('title')+': ' : '';
    LI.csvData[name] = [
      [
        title,
        $(this).find('h2').text()
      ],
    ]; 

    $.get($(this).find('.chart').attr('data-json-url'), function(json){
      var array = [];
      var series = [];
 
      switch ( name ) {
      case 'evolution':
       $.each(json, function(date, value){
          array.push([date, value]);
          LI.csvData[name].push([date, value]);
        });
        $(this).dblclick(function(){
          $(this).resetZoom();
        });
        break;
      default:
        $.each(json, function(key, value){
          array.push([key, value]);
          LI.csvData[name].push([key, value]);
        });
      }
      
      switch ( name ) {
      case 'evolution':
        $.jqplot(id, [array], {
          seriesDefaults: {
            showMarker: false
          },
          series: [{ label: title }],
          axes: {
            xaxis: {
              renderer: $.jqplot.DateAxisRenderer,
              tickOptions: { formatString:'%d/%m/%Y' }
            },
           yaxis: {
              min: name == 'web-origin' ? 0 : null,
              //tickInterval: 1,
              tickOptions: {
                formatString: '%d'
              }
            }
          },
          highlighter: {
            sizeAdjust: 2,
            show: true
          },
          legend: {
            show: false,
            location: 'e',
            placement: 'outside'
          },
          cursor: {
            show: true,
            showTooltip: false,
            zoom: true
          },
          captureRightClick: true
        });
        break;
      default:
        $.jqplot(id, [array], {
          seriesDefaults: {
            rendererOptions: {
              fill: true,
              showDataLabels: true,
              slideMargin: 4,
              lineWidth: 5
            },
            renderer: $.jqplot.PieRenderer
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
        break;
      }
    });
  });
};
