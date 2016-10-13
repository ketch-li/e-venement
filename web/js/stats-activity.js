if (LI === undefined)
    var LI = {};
if (LI.stats === undefined)
    LI.stats = [];

$(document).ready(function () {

    LI.stats.activity();
});

LI.stats.activityLegends = [];

LI.stats.activity = function () {
    $('#content .jqplot').each(function () {
        var chart = $(this).find('.chart')
        var name = chart.attr('data-series-name');
        var id = chart.prop('id');
        var title = $(this).find('h2').prop('title') ? $(this).find('h2').prop('title') + ': ' : '';
        var title = $(this).find('h2') ? $(this).find('h2').text() : '';
        LI.csvData[name] = [
          [
            $(this).find('#csvTitle').text(),
            title
          ],
        ]; 
        
        $.get(chart.attr('data-json-url'), function (json) {
            var passing = [];
            var ordered = [];
            var printed = [];
            var labels = [];
            
            LI.stats.activityLegends = json.legends;
            LI.csvData[name].push(json.csvHeaders);

            $.each(json, function (key, value) {

                if(key !== 'csvHeaders' && key !== 'legends'){
                    var date = new Date(value.date);
                    var formattedDate = date.getDate() + '/' + 
                                        (date.getMonth() + 1) + '/' + 
                                        date.getFullYear().toString().slice(-2)
                                    ;

                    passing.push(value.passing);
                    ordered.push(value.ordered);
                    printed.push(value.printed);
                    labels.push(formattedDate);

                    LI.csvData[name].push([formattedDate, value.passing, value.printed, value.ordered, value.asked]);
                }
            });

            $.jqplot(id, [printed, ordered, passing], {
                height: 600,
                stackSeries: true,
                seriesDefaults: {
                    highlighter: {show: true},
                    renderer: $.jqplot.BarRenderer,
                    rendererOptions: {
                        barMargin: 10
                    }
                },
                series: [
                        {
                            label: json.legends.printed,
                            color: "#FF0000",
                            highlighter: {show: true}
                        },
                        {
                            label: json.legends.ordered,
                            color: "#FFA500",
                            highlighter: {show: true}
                        },
                        { 
                            label: json.legends.passing,
                            color: "#00FF00",
                            disableStack : true,
                            renderer: $.jqplot.LineRenderer,
                            lineWidth: 2,
                            pointLabels: {
                                show: false
                            },
                            markerOptions: {
                                size: 5
                            },
                            highlighter: {show: true}
                        }
                    ],
                axes: {
                    xaxis: {
                        renderer: $.jqplot.CategoryAxisRenderer,
                        ticks: labels,
                        tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                        tickInterval: 3,
                        tickOptions: {
                            angle: 50,

                        }
                    },
                    yaxis: {
                        autoscale: true,
                        min: 0
                    }
                },
                legend: {
                    show: true,
                    location: 'nw',
                    placement: 'inside'
                },
                highlighter: {
                    sizeAdjust: 2,
                    show: true,
                    //useAxesFormatters: false,
                    tooltipContentEditor: LI.stats.activityTooltips
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

LI.stats.activityTooltips = function (str, seriesIndex, pointIndex, plot){

    var label;
    var total = plot.data[0][pointIndex] + plot.data[1][pointIndex];

    switch(seriesIndex ){
        case 0:
            label = LI.stats.activityLegends.printed;
            break;
        case 1:
            label = LI.stats.activityLegends.ordered;
            break;
        default:
            return plot.axes.xaxis.ticks[pointIndex] + ', ' + plot.data[2][pointIndex];
    }

    return label + ': ' + plot.data[seriesIndex][pointIndex] + ', '
         + 'Total: ' + total;
};

