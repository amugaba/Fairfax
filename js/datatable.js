/**
 * Data tables and CSV export.
 * Simple tables are used for 1 variable graphs. Crosstabs are for 2.
 * Each web page uses a separate set of functions: highlight, explorer, trend.
 */
"use strict";

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

function getCSVHeader(mainTitle, groupTitle, year, dataset, filterString) {
    var csv = "Fairfax County Youth Survey Data Explorer\r\n";
    csv += "Year: " + year + "\r\n";
    csv += "Dataset: " + (dataset==='6th' ? '6th grade' : '8th, 10th, and 12th grades') + "\r\n";
    csv += '"' + mainTitle + '"\r\n';
    if(groupTitle != null)
        csv += '"Compared to Question: ' + groupTitle + '"\r\n';
    if(filterString != null)
        csv += '"' + filterString + '"\r\n';
    csv += "\r\n";

    return csv;
}

function simpleHighlightCSV(mainTitle, mainLabels, counts, totals, year, dataset) {
    var csv = getCSVHeader("Highlights: " + mainTitle, null, year, dataset, null);

    csv += ",Total Positive,Total Possible, % Positive\r\n";

    for(var i=0; i<mainLabels.length; i++)
    {
        csv += mainLabels[i].replace("<br>"," ")+","+Math.round(counts[i][0]) + ",";
        csv += Math.round(totals[i]) + ",";
        csv += (counts[i][0]/totals[i]*100).toFixed(1) + "%\r\n";
    }

    tableToExcel(csv);
}

function simpleExplorerCSV(mainTitle, mainLabels, counts, totals, year, dataset) {
    var csv = getCSVHeader("Question: " + mainTitle, null, year, dataset, null);

    csv += ",Total,% Total\r\n";

    for(var i=0; i<mainLabels.length; i++)
    {
        csv += mainLabels[i].replace("<br>"," ")+","+Math.round(counts[i][0]) + ",";
        csv += (counts[i][0]/totals[0]*100).toFixed(1) + "%\r\n";
    }
    csv += "Total," + Math.round(totals[0]) + ",100%";

    tableToExcel(csv);
}

function simpleTrendCSV(mainTitle, labels, years, percents, dataset, filterString) {
    var csv = getCSVHeader(mainTitle, null, years[0]+' to '+years[years.length-1], dataset, filterString);

    csv += ",Year\r\n";
    for(var i=0; i<years.length; i++){
        csv += ','+years[i];
    }
    csv += "\r\n";

    for(var i=0; i<labels.length; i++)    {
        csv += '"'+labels[i]+'"';//escape commas
        for(var j=0; j<years.length; j++) {
            csv += ',' + percents[j]['v'+i] + '%';
        }
        csv += "\r\n";
    }

    tableToExcel(csv);
}

function crosstabHighlightCSV(mainTitle, groupTitle, mainLabels, groupLabels, counts, sumPositives, totals, year, dataset) {
    var csv = getCSVHeader("Highlights: " + mainTitle, groupTitle, year, dataset, null);

    csv += ',,"'+groupTitle+'"\r\n';
    csv += ",,"+groupLabels.join(",");
    csv += ",Total Positive,Total Possible, % Positive\r\n";
    csv += '"'+mainTitle+'"';

    for(var i=0; i<mainLabels.length; i++)
    {
        csv += ',"'+mainLabels[i].replace("<br>"," ")+'",';
        for(var j=0; j<groupLabels.length; j++) {
            csv += Math.round(counts[i][j]) + ",";
        }
        csv += Math.round(sumPositives[i]) + "," + Math.round(totals[i]) + "," + (sumPositives[i]/totals[i]*100).toFixed(1) + "%\r\n";
    }

    tableToExcel(csv);
}

function crosstabExplorerCSV(mainTitle, groupTitle, mainLabels, groupLabels, counts, totals, groupTotals, sumTotal, filterString, year, dataset) {
    var csv = getCSVHeader("Question: " + mainTitle, groupTitle, year, dataset, filterString);

    csv += ',,"'+groupTitle+'"\r\n';
    csv += ",,"+groupLabels.join(",");
    csv += ",Total,% Total\r\n";
    csv += '"'+mainTitle+'"';

    for(var i=0; i<mainLabels.length; i++)
    {
        csv += ',"'+mainLabels[i].replace("<br>"," ")+'",';
        for(var j=0; j<groupLabels.length; j++) {
            csv += Math.round(counts[i][j]) + ",";
        }
        csv += Math.round(totals[i]) + "," + (totals[i]/sumTotal*100).toFixed(1) + "%\r\n";
    }

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

    tableToExcel(csv);
}

function tableToExcel(csv) {
    if(!isIE()) {
        var blob = new Blob([csv],{type: "text/csv;charset=utf-8;"});
        if (navigator.msSaveBlob) { // IE 10+
            navigator.msSaveBlob(blob, "fairfaxdata.csv")
        } else {
            csv = "data:text/csv;charset=utf-8," + csv;
            var encodedUri = encodeURI(csv);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "fairfaxdata.csv");
            document.body.appendChild(link); // Required for FF
            link.click();
        }
    }
    else {
        var blob = new Blob([csv],{type: "text/csv;charset=utf-8;"});
        if (navigator.msSaveBlob) { // IE 10+
            navigator.msSaveBlob(blob, "fairfaxdata.csv")
        } else {
            var IEwindow = window.open();
            IEwindow.document.write('sep=,\r\n' + csv);
            IEwindow.document.close();
            IEwindow.document.execCommand('SaveAs', true, "fairfaxdata.csv");
            IEwindow.close();
        }
    }
}

function createSimpleHighlightTable(tableElem, labels, counts, totals) {
    var table = $(tableElem);

    //add header in first row
    table.append('<tr><th class="clearcell">Category</th>' +
        '<th style="text-align: center">Total<br>Positive</th>' +
        '<th style="text-align: center">Total<br>Possible</th>' +
        '<th style="text-align: center">% Positive</th></tr>');

    //add a row for each answer
    for(var i=0; i<labels.length; i++) {
        var row = $('<tr></tr>').appendTo(table);
        row.append('<th>' + labels[i] + '</th>');
        row.append('<td>' + Math.round(counts[i][0]).toLocaleString() + '</td>');
        row.append('<td>' + Math.round(totals[i]).toLocaleString() + '</td>');
        row.append('<td>' + (counts[i][0]/totals[i]*100).toFixed(1) + '%</td>');
    }
}

function createSimpleExplorerTable(tableElem, labels, counts, sumTotal) {
    var table = $(tableElem);

    //add header in first row
    table.append('<tr><th class="clearcell">Answer</th>' +
        '<th style="text-align: center">Total</th>' +
        '<th style="text-align: center">% Total</th></tr>');

    //add a row for each answer
    for(var i=0; i<labels.length; i++) {
        var row = $('<tr></tr>').appendTo(table);
        row.append('<th>' + labels[i] + '</th>');
        row.append('<td>' + Math.round(counts[i]).toLocaleString() + '</td>');
        row.append('<td>' + (counts[i]/sumTotal*100).toFixed(1) + '%</td>');
    }

    //add total row
    table.append('<tr><th>Total</th>' +
        '<td>' + Math.round(sumTotal).toLocaleString() + '</td>' +
        '<td>100.0%</td></tr>');
}

function simpleTrendTable(tableElem, labels, years, percents) {
    var table = $(tableElem);

    //add "Year" in first row
    table.append('<tr><th class="clearcell" rowspan="2">Answer</th>' +
        '<th colspan="'+years.length+'" style="text-align: center">Year</th></tr>');

    //add individual years as headers in second row
    var row = $('<tr></tr>').appendTo(table);
    for(var i=0; i<years.length; i++){
        row.append('<th>'+years[i]+'</th>');
    }

    //add each question as a row
    for(var i=0; i<labels.length; i++){
        var row = $('<tr></tr>').appendTo(table);
        row.append('<th>'+labels[i]+'</th>');
        for(var j=0; j<years.length; j++) {
            row.append('<td>'+percents[j]['v'+i].toFixed(1)+'%</td>');
        }
    }
}

function createCrosstabHighlightTable(tableElem, mainTitle, groupTitle, mainLabels, groupLabels, counts, sumPositives, totals) {
    var table = $(tableElem);

    //add group title in first row
    var row = $('<tr></tr>').appendTo(table);
    row.append('<th colspan="2" rowspan="2" class="clearcell">Category</th>' +
        '<th colspan="'+groupLabels.length+'" style="text-align: center">'+groupTitle+'</th>');

    row.append('<th rowspan="2" style="text-align: center">Total<br>Positive</th>' +
        '<th rowspan="2" style="text-align: center">Total<br>Possible</th>' +
        '<th rowspan="2" style="text-align: center">% Positive</th>');

    //add group answers in second row
    var groupHeader = $('<tr></tr>').appendTo(table);
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
        row.append('<th>'+mainLabels[i]+'</th>');
        for(var j=0; j<groupLabels.length; j++) {
                row.append('<td>'+Math.round(counts[i][j]).toLocaleString()+'</td>');
        }

        //end row with total and percentage
        row.append('<td>'+Math.round(sumPositives[i]).toLocaleString()+'</td>' +
            '<td>' + Math.round(totals[i]).toLocaleString() + '</td>' +
            '<td>' + (sumPositives[i]/totals[i]*100).toFixed(1) + '%</td>');
    }
}

function createCrosstabExplorerTable(tableElem, mainTitle, groupTitle, mainLabels, groupLabels, counts, totals, groupTotals, sumTotal) {
    var table = $(tableElem);

    //add group title in first row
    var row = $('<tr></tr>').appendTo(table);
    row.append('<th colspan="2" rowspan="2" class="clearcell">Answer</th>' +
        '<th colspan="'+groupLabels.length+'" style="text-align: center">'+groupTitle+'</th>');

    row.append('<th rowspan="2" style="text-align: center">Total</th>' +
        '<th rowspan="2" style="text-align: center">% Total</th>');

    //add group answers in second row
    var groupHeader = $('<tr></tr>').appendTo(table);
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
        row.append('<th>'+mainLabels[i]+'</th>');
        for(var j=0; j<groupLabels.length; j++) {
            row.append('<td>'+Math.round(counts[i][j]).toLocaleString()+'</td>');
        }

        //end row with total and percentage
        row.append('<td>'+Math.round(totals[i]).toLocaleString() + '</td>' +
            '<td>' + (totals[i]/sumTotal*100).toFixed(1) + '%</td>');
    }

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