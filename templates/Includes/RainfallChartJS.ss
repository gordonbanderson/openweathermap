var ctx = document.getElementById("rainfallChart").getContext("2d");
var data = {
    labels: [{$Labels}],
    datasets: [
        {
            label: "Rainfall",
            fillColor: "rgba(0,0,255,0.5)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [{$Rainfall}]
        }
    ]
};
var linechart = new Chart(ctx).Line(data,
$ChartOptions
);
