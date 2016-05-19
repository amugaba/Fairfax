/**
 * Created by David on 4/10/2016.
 */
"use strict";
var questions = [];
var chart, mainCode = null, groupCode = null;
//var fillColors = ["#70a1c2","#ddaf45","#c273bf","#d4d257","#7cc27c","#c25844","#c29e88","#567ac2"];
var fillColors = ["#70a1c2","#7cc27c","#d4d257","#ddaf45","#c26751","#c273bf","#c29e88","#567ac2"];

function init(qs, main, group) {
    questions = qs;
    mainCode = main;
    groupCode = group;
}

function createPercentChart(chartData, groups, mainTitle, groupTitle) {
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
        categoryAxis.title = mainTitle;

        // Value
        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.axisAlpha = 0;
        valueAxis.gridAlpha = 0.1;
        valueAxis.position = "top";
        valueAxis.title = "Percent %";
        valueAxis.minimum = 0;
        valueAxis.maximum = 100;
        chart.addValueAxis(valueAxis);

        // GRAPHS
        for(var i = 0; i < groups.length; i++) {
            chart.addGraph(createSubGraph(groups[i],'v'+i,i));
        }
        /*if(group == 'I2')
        {
            chart.addGraph(createSubGraph('Grade 8','grade8',1));
            chart.addGraph(createSubGraph('Grade 10','grade10',2));
            chart.addGraph(createSubGraph('Grade 12','grade12',3));
        }
        else if(group == 'I3')
        {
            chart.addGraph(createSubGraph('Female','female',1));
            chart.addGraph(createSubGraph('Male','male',2));
            //chart.addGraph(createSubGraph('Unknown','unknown',3));
        }
        else if(group == 'race')
        {
            chart.addGraph(createSubGraph('White','white',1));
            chart.addGraph(createSubGraph('Non-white','nonwhite',2));
            //chart.addGraph(createSubGraph('Unknown','unknown',3));
        }
        else {
            chart.addGraph(createSubGraph('Total','total',1));
        }*/


        // LEGEND
        var legend = new AmCharts.AmLegend();
        legend.position = "top";
        legend.title = groupTitle;
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
    graph1.balloonText = "[[value]]% of "+title;
    graph1.lineAlpha = 0;
        graph1.fillColors = fillColors[num];
    graph1.fillAlphas = 1;
    return graph1;
}

function createVariablesByCategory(div1,div2,category) {
    var list1 = $("<ul></ul>").appendTo(div1);
    var list2 = $("<ul></ul>").appendTo(div2);

    for(var i=0; i<questions.length; i++) {
        if(questions[i].category == category) {
            $("<li></li>").appendTo(list1).append("<a href='graphs.php?q1="+questions[i].code+"'>"+questions[i].summary+"</a>");
            $("<li></li>").appendTo(list2).append("<a href='graphs.php?q1="+mainCode+"&grp="+questions[i].code+"'>"+questions[i].summary+"</a>");
        }
    }
}