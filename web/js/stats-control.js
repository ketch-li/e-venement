if ( LI === undefined )
  var LI = {};
if ( LI.stats === undefined )
  LI.stats = [];

$(document).ready(function(){
  LI.stats.control();
});

LI.stats.control = function(){
  $('#content .jqplot').each(function(){
    var chart = $(this).find('.chart')
    var id = chart.prop('id');
    var title = $(this).find('h2') ? $(this).find('h2').text() : '';
    LI.csvData = { hours: [
      [
        $(this).find('#csvTitle').text(),
        title
      ],
    ] }; 

    $.get($(this).find('.chart').attr('data-json-url'), function(json){
      var array = [];
 
      LI.csvData.hours.push(json.csvHeaders);

      $.each(json, function(key, value){
        if ( key !== 'csvHeaders' )
        {
          array.push([key, value.value]);
          LI.csvData.hours.push([key, value.value, value.percent]);
        }
      });
      
      var plot = $.jqplot(id, [array, array], {
        animate: true,
        seriesDefaults: {
          label: title
        },
        series: [
          {
            renderer: $.jqplot.BarRenderer,
            pointLabels: { show: true },
            showMarker: false
          },
          {
            showMarker: false,
            rendererOptions: {
              smooth: true,
            }
          },
        ],
        axes: {
          xaxis: {
            renderer: $.jqplot.CategoryAxisRenderer,
            label: LI.csvData.hours[1][0],
            max: 24,
            min: 0,
            tickOptions: { formatString: '%d' }
         },
         yaxis: {
            min: 0,
            tickOptions: { formatString: '%d' }
          }
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

      LI.stats.resizable(plot, name, id);
    });
  });
};

LI.stats.webOriginTooltips = function (str, seriesIndex, pointIndex, plot){

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
