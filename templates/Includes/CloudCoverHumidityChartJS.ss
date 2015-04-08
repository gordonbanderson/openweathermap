var ctx = document.getElementById("cloudHumidityChart").getContext("2d");
var data = {
    labels: [{$Labels}],
    datasets: [
        {
            label: "Humidity",
            fillColor: "rgba(0,0,255,0.2)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [{$Humidity}]
        },
        {
            label: "Cloud Cover",
            fillColor: "rgba(128,128,128,0.5)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [{$CloudCover}]
        },
    ]
};
var linechart = new Chart(ctx).Line(data,
$ChartOptions
);
