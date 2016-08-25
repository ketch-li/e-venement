if ( LI === undefined )
  var LI = {};
if ( LI.stats === undefined )
  LI.stats = [];

$(document).ready(function(){

  LI.stats.debts();
});

LI.stats.debts = function(){

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

      LI.csvData[name].push(json.csvHeaders);

      $.each(json, function(key, data) {

        if(key !== 'csvHeaders'){
          array.push([data.date, data.outcome - data.income]);
          LI.csvData[name].push([data.date, data.outcome, data.income, data.outcome - data.income]);
        }
      });
      $(this).dblclick(function(){
        $(this).resetZoom();
      });
      
      //init jqplot with data array
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
    });
  });
};