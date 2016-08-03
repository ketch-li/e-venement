if (LI === undefined)
    var LI = {};
if (LI.stats === undefined)
    LI.stats = [];

$(document).ready(function () {

    LI.stats.attendance();
});

LI.stats.attendance = function () {
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
            var array = [];
            var ordered = [];
            var printed = [];
            var labels = [];

            $.each(json, function (key, value) {
                var eventName = value.Event.Translation.fr.name;
                var orderedAmount = value.ordered;
                var printedAmount = value.printed + value.printed_deposits + value.printed_gifts + value.printed_with_payment;
                var freeAmount = value.gauge - printedAmount - orderedAmount;

                array.push(freeAmount);
                ordered.push(orderedAmount);
                printed.push(printedAmount);
                labels.push(eventName);

                LI.csvData[name].push([eventName, value.happens_at, value.location_name, value.location_city, value.gauge, value.printed, value.printed_with_payments, value.printed_gifts, value.printed_deposits, value.ordered, freeAmount]);
            });
            $(this).dblclick(function () {
                $(this).resetZoom();
            });

            var seriesLegend = [{label: 'Imprimé', color: "#FF0000"}, {label: "Réservé", color: "#FFA500"}, {label: "Total", color: "#00FF00"}];

            $.jqplot(id, [printed, ordered, array], {
                height: 800,
                stackSeries: true,
                seriesDefaults: {
                    renderer: $.jqplot.BarRenderer,
                },
                series: seriesLegend,
                axes: {
                    xaxis: {
                        renderer: $.jqplot.CategoryAxisRenderer,
                        tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                        ticks: labels,
                        tickOptions: {
                            angle: 50
                        }
                    },
                    yaxis: {
                        //max: 1000
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

