if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  LI.dashboardStats();
  if ( $('#dashboard > .jqplot').length > 0 )
  {
    $('#sf_admin_content .welcome > .ui-widget-content:first')
      .append($('#dashboard'))
      .append('<div class="clear"></div>')
      .find('h3, ul').remove()
    ;
  }
});

LI.dashboardStats = function(){
  $('#dashboard > .jqplot').each(function(){
    var name = $(this).find('.chart').attr('data-series-name');
    var id = $(this).find('.chart').prop('id');
    var title = $(this).find('h2').prop('title') ? $(this).find('h2').prop('title')+': ' : '';
    LI.csvData[name] = [
      [
        title,
        $(this).find('h2').text()
      ],
    ]; 
    
    $.get($(this).find('.chart').attr('data-json-url'), function(json){
      var array = [];

      LI.csvData[name].push(json.csvHeaders);
      
      switch ( name ) {
      case 'debts':
        $.each(json, function(key, data) {

          if(key !== 'csvHeaders'){
            array.push([data.date, data.outcome - data.income]);
            LI.csvData[name].push([data.date, data.outcome, data.income, data.outcome - data.income]);
        }
      });
        break;
      case 'web-origin':
        $.each(json, function(key, value){
        if(key !== 'csvHeaders'){
            array.push([key, value.value]);
            LI.csvData[name].push([key, value.value, value.percent]);
          }
        });
        break;
      case 'geo':
        $.each(json.nb, function(key, value) {
            array.push([json.translations[key], value.value]);
            LI.csvData[name].push([json.translations[key], value.value, value.percent, json.tickets[key].value, json.tickets[key].percent, json.value[key].value + ' €', json.value[key].percent]);
          });
        break;
      default:
        $.each(json, function(key, value) {

        if(key !== 'csvHeaders'){
          array.push([value.name, value.nb]);
          LI.csvData[name].push([value.name, value.nb, value.percent]);
        }
      });
      }
      
      switch ( name ) {
      case 'web-origin':
      case 'debts':
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
            show: true,
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
        break;
      }
    });
  });
}
