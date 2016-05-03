/**
 * Created by David on 4/10/2016.
 */
"use strict";
var variable = null;
var data = null;
var chart;
var chartData = [
    {
        "year": 2005,
        "grade8": 23.5,
        "grade10": 18.1,
        "grade12": 28.1
    },
    {
        "year": 2006,
        "grade8": 26.2,
        "grade10": 22.8,
        "grade12": 26.1
    },
    {
        "year": 2007,
        "grade8": 30.1,
        "grade10": 23.9,
        "grade12": 25.1
    },
    {
        "year": 2008,
        "grade8": 29.5,
        "grade10": 25.1,
        "grade12": 29.1
    },
    {
        "year": 2009,
        "grade8": 24.6,
        "grade10": 25,
        "grade12": 27.1
    }
];

function init(vari, dat) {
    variable = vari;
    chartData = dat;
}

function createChart() {
    AmCharts.ready(function () {
        // SERIAL CHART
        chart = new AmCharts.AmSerialChart();
        chart.dataProvider = chartData;
        chart.categoryField = "answer";
        chart.startDuration = 1;
        chart.plotAreaBorderColor = "#DADADA";
        chart.plotAreaBorderAlpha = 1;
        // this single line makes the chart a bar chart
        chart.rotate = true;
        chart.columnSpacing = 0;

        // AXES
        // Category
        var categoryAxis = chart.categoryAxis;
        categoryAxis.gridPosition = "start";
        categoryAxis.gridAlpha = 0.1;
        categoryAxis.axisAlpha = 0;
        categoryAxis.title = "Response";

        // Value
        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.axisAlpha = 0;
        valueAxis.gridAlpha = 0.1;
        valueAxis.position = "top";
        valueAxis.title = "Number"
        chart.addValueAxis(valueAxis);

        // GRAPHS
        // first graph
        var graph1 = new AmCharts.AmGraph();
        graph1.type = "column";
        graph1.title = "Total";
        graph1.valueField = "num";
        graph1.balloonText = "Total:[[value]]";
        graph1.lineAlpha = 0;
        graph1.fillColors = "#ADD981";
        graph1.fillAlphas = 1;
        chart.addGraph(graph1);

        // LEGEND
        var legend = new AmCharts.AmLegend();
        legend.position = "top";
        chart.addLegend(legend);

        chart.creditsPosition = "top-right";
        chart.write("chartdiv");
    });
}