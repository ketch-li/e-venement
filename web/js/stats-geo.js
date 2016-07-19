if ( LI === undefined )
  var LI = {};
if ( LI.stats === undefined )
  LI.stats = [];

$(document).ready(function(){

  LI.stats.geo();
});

LI.stats.geo = function(){

  $('#content .jqplot').each(function(){
console.log($(this));
    var chart = $(this).find('.chart')
    var name = chart.attr('data-series-name');
console.log(name);
    var id = chart.prop('id');
console.log(id);
    var title = $(this).find('h2').prop('title') ? $(this).find('h2').prop('title')+': ' : '';
    LI.csvData[name] = [
      [
        title,
        $(this).find('h2').text()
      ],
    ]; 
    
    $.get(chart.attr('data-json-url') + '?type=' + name, function(json){
      var array = [];
      var series = [];
      console.log(json);
      switch ( name ) {
      case 'ego':
        $.each(json.tickets, function(key, value) {
          console.log(key + '-' + value);
          array.push([json.translations[key], value]);
          LI.csvData[name].push([json.translations[key], value]);
        });
        break;
      default:
       $.each(json.value, function(key, value) {
          array.push([json.translations[key], value]);
          LI.csvData[name].push([json.translations[key], value]);
        });
      }
      
      $.jqplot(id, [array], {
        seriesDefaults: {
          rendererOptions: {
            fill: false,
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
