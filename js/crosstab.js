/**
 * Created by David on 4/10/2016.
 */
"use strict";
var questions = [], countData = [], percentData = [], mainLabels = [], groupLabels = [], mainTotals = [], groupTotals = [], categoryDivisors = [];
var mainTitle, groupTitle, sumTotal, mainQuestion, groupQuestion, filterString, isCategory, connector;
var chart, mainCode = null, groupCode = null;

var fillColors = ["#70a1c2","#7cc27c","#d4d257","#ddaf45","#c26751","#c273bf","#c29e88","#567ac2"];

function createPercentChart(counts, percents, mLabels, gLabels, mTitle, gTitle, category, tooltips) {
    //save global variables
    countData = counts;
    percentData = percents;
    mainLabels = mLabels;
    groupLabels = gLabels;
    mainTitle = mTitle;
    groupTitle = gTitle;
    isCategory = category;

    AmCharts.ready(function () {
        chart = new AmCharts.AmSerialChart();
        chart.dataProvider = percentData;

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
        //categoryAxis.ignoreAxisWidth = true;
        //categoryAxis.autoWrap = true;
        categoryAxis.labelFunction = addLineBreaks;
        chart.fontSize = 13;

        if(tooltips != null) {
            chart.categoryAxis.addListener("rollOverItem", function (event) {
                event.target.setAttr("cursor", "default");
                event.chart.balloon.borderColor = "#70a1c2";
                event.chart.balloon.followCursor(true);
                event.chart.balloon.changeColor(event.serialDataItem.dataContext.color);
                event.chart.balloon.showBalloon(tooltips[percentData.indexOf(event.serialDataItem.dataContext)]);
            });
            chart.categoryAxis.addListener("rollOutItem", function (event) {
                event.chart.balloon.hide();
            });
        }

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
        for(var i = 0; i < groupLabels.length; i++) {
            chart.addGraph(createSubGraph(groupLabels[i], 'v'+i, i, isCategory));
        }

        // LEGEND
        var legend = new AmCharts.AmLegend();
        legend.position = "top";
        legend.title = groupTitle;
        chart.addLegend(legend);

        chart.creditsPosition = "top-right";
        chart.export = {
            enabled: true
        };
        chart.write("chartdiv");
    });
    return chart;
}

function createSubGraph(title,field,num,isCategory) {
    var graph1 = new AmCharts.AmGraph();
    graph1.type = "column";
    graph1.title = title;
    graph1.valueField = field;
    if(isCategory) {
        if(title == "Total")
            graph1.balloonText = "[[value]]% of students reported " + connector + "[[category]]";
        else
            graph1.balloonText = "[[value]]% of "+title+" students reported " + connector + "[[category]]";
    }
    else if(groupTitle == null)
        graph1.balloonText = "[[value]]% of students answered '[[category]]' to '"+mainTitle+"'";
    else
        graph1.balloonText = "[[value]]% of students who answered <i>'"+title+"'</i> to '"+groupTitle+"' also answered <i>'[[category]]'</i> to '"+mainTitle+"'";
    graph1.lineAlpha = 0;
        graph1.fillColors = fillColors[num];
    graph1.fillAlphas = 1;
    return graph1;
}

function createVariablesByCategory(title,category) {
    $("#accordion1").append("<h3>"+title+"</h3>");
    $("#accordion2").append("<h3>"+title+"</h3>");
    var div1 = $('<div id="demo1"></div>').appendTo('#accordion1');
    var div2 = $('<div id="demo2"></div>').appendTo('#accordion2');

    var list1 = $("<ul></ul>").appendTo(div1);
    var list2 = $("<ul></ul>").appendTo(div2);

    for(var i=0; i<questions.length; i++) {
        if(questions[i].category == category) {
            $("<li></li>").appendTo(list1).append("<a href='graphs.php?q1="+questions[i].code+"'>"+questions[i].summary+"</a>");
            $("<li></li>").appendTo(list2).append("<a href='graphs.php?q1="+mainCode+"&grp="+questions[i].code+"'>"+questions[i].summary+"</a>");
        }
    }
}

function addLineBreaks(label, item, axis) {
    var breaksNeeded = Math.floor(label.length / 20);
    if(breaksNeeded == 0)
        return label;

    var lengthPerLine = Math.floor(label.length / (breaksNeeded+1));
    var words = label.split(' ');
    var insertPoints = [];
    var startWord = 0;

    for(var i=0; i<breaksNeeded; i++) {
        var lineLength = 0;
        //starting at the beginning of the line, add words until the length exceeds the line length
        for(var j=startWord; j<words.length; j++) {
            lineLength += words[j].length;
            if(lineLength > lengthPerLine) {
                //check if more than half the word would fit on this line
                if(lineLength - lengthPerLine < words[j].length/2)
                    startWord = j+1;
                else
                    startWord = j;
                insertPoints.push(startWord);
                break;
            }
            lineLength++;//for space
        }
    }

    //reconstruct string with <br> at insertion points
    var newstring = "";
    for(var i=0; i<words.length; i++) {
        if(i != 0) {
            if(insertPoints.indexOf(i) >= 0)
                newstring += "<br>";
            else
                newstring += " ";
        }
        newstring += words[i];
    }
    return newstring;
}

function filter() {
    var url = "graphs.php?q1=" + mainCode;
    if(groupCode != null)
        url += "&grp="+groupCode;

    if($("#filtergrade option:selected").val() > 0)
        url += "&grade=" + $("#filtergrade option:selected").val();
    if($("#filtergender option:selected").val() > 0)
        url += "&gender=" + $("#filtergender option:selected").val();
    if($("#filterrace option:selected").val() > 0)
        url += "&race=" + $("#filterrace option:selected").val();

    window.location.href = url;
}

function makeFilterString(grade, gender, race) {
    var grades = ['8th grade','10th grade','12th grade'];
    var genders = ['Female','Male'];
    var races = ['White','Black','Hispanic','Asian/Pacific Islander','Other/Multiple'];

    var clauses = [];
    if(grade!=null)
        clauses.push("Grade = " + grades[grade-1]);
    if(gender!=null)
        clauses.push("Gender = " + genders[gender-1]);
    if(race!=null)
        clauses.push("Race/Ethnicity = " + races[race-1]);

    if(clauses.length > 0)
        return "Filtered by " + clauses.join(", ");
    else
        return null;
}

function isIE() {
    if (window.navigator.userAgent.indexOf("MSIE ") > 0 || !!window.navigator.userAgent.match(/Trident.*rv\:11\./))
        return true;
    else
        return false;
}

function writeCSV() {
    var csv = "Fairfax County Youth Survey Data Explorer\r\n";
    var descriptor = isCategory ? "" : "Question: ";
    csv += "Year: "+year+"\r\n";
    csv += '"' + descriptor + mainQuestion + '"\r\n';
    if(groupQuestion != null)
        csv += '"Compared to Question: ' + groupQuestion + '"\r\n';
    if(filterString != null)
        csv += '"' + filterString + '"\r\n';
    csv += "\r\n";

    if(groupLabels.length > 1) {
        csv += ",,"+groupTitle+"\r\n";
        csv += ",,"+groupLabels.join(",");
        if(isCategory)
            csv += ",Total Positive,Total Possible, % Positive\r\n";
        else
            csv += ",Total,% Total\r\n";
        csv += mainTitle;

        for(var i=0; i<mainLabels.length; i++)
        {
            csv += ","+mainLabels[i].replace("<br>"," ")+",";
            for(var j=0; j<groupLabels.length; j++) {
                csv += Math.round(countData[i]["v"+j]) + ",";
            }
            if(isCategory)
                csv += Math.round(mainTotals[i]) + "," + Math.round(categoryDivisors[i]) + "," + (mainTotals[i]/categoryDivisors[i]*100).toFixed(1) + "%\r\n";
            else
                csv += Math.round(mainTotals[i]) + "," + (mainTotals[i]/sumTotal*100).toFixed(1) + "%\r\n";
        }
        if(!isCategory) {
            csv += ",Total,";
            for (var j = 0; j < groupLabels.length; j++) {
                csv += Math.round(groupTotals[j]) + ",";
            }
            csv += Math.round(sumTotal) + ",100%\r\n";
            csv += ",% Total,";
            for (var j = 0; j < groupLabels.length; j++) {
                csv += (groupTotals[j] / sumTotal * 100).toFixed(1) + "%,";
            }
            csv += "100%";
        }
    }
    else {
        if(isCategory)
            csv += ",Total Positive,Total Possible, % Positive\r\n";
        else
            csv += ",Total,% Total\r\n";

        for(var i=0; i<mainLabels.length; i++)
        {
            csv += mainLabels[i].replace("<br>"," ")+","+Math.round(countData[i]["v0"]) + ",";
            if(isCategory)
                csv += Math.round(categoryDivisors[i]) + ",";
            csv += (percentData[i]["v0"]).toFixed(1) + "%\r\n";
        }
        if(!isCategory) {
            csv += "Total," + Math.round(sumTotal) + ",100%";
        }
    }

    return csv;
}

function tableToExcel() {
    var csv = writeCSV();
    if(!isIE()) {
        csv = "data:text/csv;charset=utf-8," + csv;
        var encodedUri = encodeURI(csv);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "fairfaxdata.csv");
        document.body.appendChild(link); // Required for FF
        link.click();
    }
    else {
        var IEwindow = window.open();
        IEwindow.document.write('sep=,\r\n' + csv);
        IEwindow.document.close();
        IEwindow.document.execCommand('SaveAs', true, "fairfaxdata.csv");
        IEwindow.close();
    }
}

function createSimpleTable(tableElem) {
    var table = $(tableElem);

    //add header in first row
    if(isCategory){
        table.append('<tr><th class="clearcell"></th>' +
            '<th style="text-align: center">Total<br>Positive</th>' +
            '<th style="text-align: center">Total<br>Possible</th>' +
            '<th style="text-align: center">% Positive</th></tr>');
    }
    else {
        table.append('<tr><th class="clearcell"></th>' +
            '<th style="text-align: center">Total</th>' +
            '<th style="text-align: center">% Total</th></tr>');
    }

    //add a row for each answer
    for(var i=0; i<mainLabels.length; i++) {
        var row = $('<tr></tr>').appendTo(table);
        row.append('<th>' + countData[i]['answer'] + '</th>');
        row.append('<td>' + Math.round(countData[i]['v0']).toLocaleString() + '</td>');
        if(isCategory){
            row.append('<td>' + Math.round(categoryDivisors[i]).toLocaleString() + '</td>');
        }
        row.append('<td>' + percentData[i]['v0'].toFixed(1) + '%</td>');
    }

    if(!isCategory) {
        //add total row
        table.append('<tr><th>Total</th>' +
            '<td>' + Math.round(sumTotal).toLocaleString() + '</td>' +
            '<td>100.0%</td></tr>');
    }
}

//create table from counts, percents, and labels
function createCrosstabTable(tableElem) {
    var table = $(tableElem);

    //add group title in first row
    var row = $('<tr></tr>').appendTo(table);
    row.append('<th colspan="2" class="clearcell"></th>' +
        '<th colspan="'+groupLabels.length+'" style="text-align: center">'+groupTitle+'</th>');

    if(isCategory){
        row.append('<th rowspan="2" style="text-align: center">Total<br>Positive</th>' +
            '<th rowspan="2" style="text-align: center">Total<br>Possible</th>' +
            '<th rowspan="2" style="text-align: center">% Positive</th>');
    }
    else {
        row.append('<th rowspan="2" style="text-align: center">Total</th>' +
            '<th rowspan="2" style="text-align: center">% Total</th>');
    }

    //add group answers in second row
    var groupHeader = $('<tr><th colspan="2" class="clearcell"></th></tr>').appendTo(table);
    for(var i=0; i<groupLabels.length; i++) {
        groupHeader.append('<th>'+groupLabels[i]+'</th>');
    }

    //add a row for each main var answers
    for(var i=0; i<mainLabels.length; i++) {
        var row = $('<tr></tr>').appendTo(table);

        //main title in first column
        if(i==0)
            row.append('<th style="width: 80px;" rowspan="'+mainLabels.length+'">'+mainTitle+'</th>');

        //answer label in second column, followed by data
        for(var j=0; j<=groupLabels.length; j++) {
            if(j == 0) {
                row.append('<th>'+countData[i]['answer']+'</th>')
            }
            else {
                row.append('<td>'+Math.round(countData[i]['v'+(j-1)]).toLocaleString()+'</td>');
            }
        }

        //end row with total and percentage
        if(isCategory){
            row.append('<td>'+Math.round(mainTotals[i]).toLocaleString()+'</td>' +
                '<td>' + Math.round(categoryDivisors[i]).toLocaleString() + '</td>' +
                '<td>' + (mainTotals[i]/categoryDivisors[i]*100).toFixed(1) + '%</td>');
        }
        else {
            row.append('<td>'+Math.round(mainTotals[i]).toLocaleString() + '</td>' +
                '<td>' + (mainTotals[i]/sumTotal*100).toFixed(1) + '%</td>');
        }
    }

    if(!isCategory) {
        //final two rows have the group totals
        var row = $('<tr><th colspan="2">Total</th></tr>').appendTo(table);
        for (var i = 0; i < groupLabels.length; i++) {
            row.append('<td>' + Math.round(groupTotals[i]).toLocaleString() + '</td>');
        }
        row.append('<td>' + Math.round(sumTotal).toLocaleString() + '</td><td>100.0%</td>');

        var row = $('<tr><th colspan="2">% Total</th></tr>').appendTo(table);
        for (var i = 0; i < groupLabels.length; i++) {
            row.append('<td>' + (groupTotals[i] / sumTotal * 100).toFixed(1) + '%</td>');
        }
        row.append('<td>100.0%</td>');
    }
}