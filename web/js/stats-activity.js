if (LI === undefined)
    var LI = {};
if (LI.stats === undefined)
    LI.stats = [];

$(document).ready(function () {

    LI.stats.activity();
});

LI.stats.activity = function () {
    $('#content .jqplot').each(function () {
        var chart = $(this).find('.chart')
        var name = chart.attr('data-series-name');
        var id = chart.prop('id');
        var title = $(this).find('h2').prop('title') ? $(this).find('h2').prop('title') + ': ' : '';
        LI.csvData[name] = [
            [
                title,
                $(this).find('h2').text()
            ]
        ];
        $.get(chart.attr('data-json-url'), function (json) {
            var passing = [];
            var ordered = [];
            var printed = [];
            var labels = [];

            $.each(json, function (key, value) {
                var date = new Date(value.date);
                var formattedDate = date.getDate() + '/' + 
                                    (date.getMonth() + 1) + '/' + 
                                    date.getFullYear().toString().slice(-2)
                                ;

                passing.push(value.passing);
                ordered.push(value.ordered);
                printed.push(value.printed);
                labels.push(formattedDate);

                LI.csvData[name].push([value.date, value.passing]);
            });
            $(this).dblclick(function () {
                $(this).resetZoom();
            });

            $.jqplot(id, [printed, ordered, passing], {
                height: 600,
                stackSeries: true,
                seriesDefaults: {
                    renderer: $.jqplot.BarRenderer,
                    rendererOptions: {
                        barMargin: 10
                    }
                },
                series: [
                        {
                        label: 'Imprimé', color: "#FF0000"
                        },
                        {
                        label: "Réservé", color: "#FFA500" 
                        },
                        { 
                        showLabel: false,
                        color: "#00FF00",
                        disableStack : true,
                        renderer: $.jqplot.LineRenderer,
                        lineWidth: 2,
                        pointLabels: {
                            show: false
                            },
                        markerOptions: {
                            size: 5
                            }
                        }
                    ],
                axes: {
                    xaxis: {
                        renderer: $.jqplot.CategoryAxisRenderer,
                        ticks: labels,
                        tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                        tickOptions: {
                            angle: 50
                        }
                    },
                    yaxis: {
                        autoscale: true,
                        min: 0
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
        });
    });
};

