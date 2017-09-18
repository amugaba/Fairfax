/**
 * Created by David on 4/10/2016.
 */
"use strict";
var questions = [], countData = [], percentData = [], mainLabels = [], groupLabels = [], mainTotals = [], groupTotals = [], categoryDivisors = [];
var mainTitle, groupTitle, sumTotal, mainQuestion, groupQuestion, filterString, isCategory, connector;
var chart, mainCode = null, groupCode = null;

var fillColors = ["#70a1c2","#7cc27c","#d4d257","#ddaf45","#c26751","#c273bf","#c29e88","#567ac2"];

function createLineChart() {
    var graphs = [];
    for(var i = 0; i < questions.length; i++) {
        graphs.push({
            "id": "g"+i,
            "balloonText": "[[value]]%",
            "bullet": "round",
            "bulletBorderAlpha": 1,
            "hideBulletsCount": 50,
            "title": questions[i].summary,
            "valueField": questions[i].code,
            "useLineColorForBulletBorder": true,
            "balloon":{
                "drop":true
            }
        });
    }

    var chart = AmCharts.makeChart("chartdiv", {
        "type": "serial",
        "theme": "light",
        "marginRight": 80,
        "autoMarginOffset": 20,
        "marginTop": 25,
        "dataProvider": percentData,
        "valueAxes": [{
            "axisAlpha": 0.2,
            "dashLength": 1,
            "position": "left",
            "minimum": 0,
            "maximum": 100,
            "title": "Percent %"
        }],
        "graphs": graphs,
        "chartCursor": {
            "limitToGraph":"g1"
        },
        "categoryField": "year",
        "categoryAxis": {
            "parseDates": false,
            "axisColor": "#DADADA",
            "dashLength": 1,
            "minorGridEnabled": true,
            "title": "Year"
        },
        "export": {
            "enabled": true
        },
        "legend": {
            "useGraphSettings": true,
            "position":"bottom"
        }
    });
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