/**
 * Created by David on 4/10/2016.
 */
"use strict";
var data = null;
var chart;
var chartData = [];

function init(dat) {
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

function createPercentChart(group) {
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
        chart.precision = 1;

        // AXES
        // Category
        var categoryAxis = chart.categoryAxis;
        categoryAxis.gridPosition = "start";
        categoryAxis.gridAlpha = 0.1;
        categoryAxis.axisAlpha = 0;
        //categoryAxis.title = "Response";

        // Value
        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.axisAlpha = 0;
        valueAxis.gridAlpha = 0.1;
        valueAxis.position = "top";
        valueAxis.title = "Percent %"
        valueAxis.minimum = 0;
        valueAxis.maximum = 100;
        chart.addValueAxis(valueAxis);

        // GRAPHS
        if(group == 'grade')
        {
            chart.addGraph(createSubGraph('Grade 8','grade8',1));
            chart.addGraph(createSubGraph('Grade 10','grade10',2));
            chart.addGraph(createSubGraph('Grade 12','grade12',3));
        }
        else if(group == 'gender')
        {
            chart.addGraph(createSubGraph('Female','female',1));
            chart.addGraph(createSubGraph('Male','male',2));
            chart.addGraph(createSubGraph('Unknown','unknown',3));
        }
        else if(group == 'race')
        {
            chart.addGraph(createSubGraph('White','white',1));
            chart.addGraph(createSubGraph('Non-white','nonwhite',2));
            chart.addGraph(createSubGraph('Unknown','unknown',3));
        }
        else {
            chart.addGraph(createSubGraph('Total','total',1));
        }


        // LEGEND
        var legend = new AmCharts.AmLegend();
        legend.position = "top";
        chart.addLegend(legend);

        chart.creditsPosition = "top-right";
        chart.write("chartdiv");
    });
}

function createSubGraph(title,field,num) {
    var graph1 = new AmCharts.AmGraph();
    graph1.type = "column";
    graph1.title = title;
    graph1.valueField = field;
    graph1.balloonText = "[[value]]%";
    graph1.lineAlpha = 0;
    if(num==1)
        graph1.fillColors = "#e7dc8e";
    else if(num==2)
        graph1.fillColors = "#e7b88e";
    else
        graph1.fillColors = "#e78ea2";
    graph1.fillAlphas = 1;
    return graph1;
}

function createVariablesList(div,letter) {

}