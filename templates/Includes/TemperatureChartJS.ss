var ctx = document.getElementById("temperatureChart").getContext("2d");
var data = {
    labels: [{$Labels}],
    datasets: [
        {
            label: "Temperature",
            fillColor: "rgba(255,69,9,0.5)",
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
