var ctx = document.getElementById("forecastChart").getContext("2d");
var data = {
    labels: [{$Labels}],
    datasets: [
        {
            label: "Temperature",
            fillColor: "rgba(220,220,40,0.2)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [{$Temperatures}]
        }
    ]
};
var linechart = new Chart(ctx).Line(data,
$ChartOptions
);
