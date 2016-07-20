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
    var title = $(this).find('h2').prop('title') ? $(this).find('h2').prop('title')+': ' : '';
    LI.csvData[name] = [
      [
        title,
        $(this).find('h2').text()
      ],
    ]; 
    console.log(name);
    //retrieve stats
    $.get(chart.attr('data-json-url') + '?id=' + name, function(json){
      var array = [];
      var series = [];

      $.each(json, function(key, value) {
      	console.log(value);
        array.push([value.name, value.nb]);
        LI.csvData[name].push([value.name, value.nb]);
      });
      
      //init jqplot with data array
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
    });
  });
};

