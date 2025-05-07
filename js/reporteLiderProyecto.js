function drawChart() {
    // Tu lógica de ApexCharts o lo que necesites
    var options = {
        chart: {
            type: 'bar'
        },
        series: [{
            name: 'Progreso',
            data: [10, 20, 30]
        }],
        xaxis: {
            categories: ['Actividad 1', 'Actividad 2', 'Actividad 3']
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart-container"), options);
    chart.render();
}
