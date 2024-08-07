/**
 * Create bar and line graphs using AmCharts plugin.
 */
"use strict";
var chart;
var fillColors = ["#70a1c2","#7cc27c","#d4d257","#ddaf45","#c26751","#c273bf","#c29e88","#567ac2"];

function createBarGraph(percentData, mainTitle, groupTitle, groupLabels, tooltips, summary) {
    AmCharts.ready(function () {
        chart = new AmCharts.AmSerialChart();
        chart.dataProvider = percentData;

        chart.categoryField = "answer";
        //chart.startDuration = 1; //bounce
        chart.plotAreaBorderAlpha = 0.2;
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
        categoryAxis.title = summary != null ? summary : mainTitle;
        categoryAxis.labelFunction = addLineBreaks;
        chart.fontSize = 13;

        if(tooltips != null) {
            chart.categoryAxis.addListener("rollOverItem", function (event) {
                event.target.setAttr("cursor", "default");
                event.chart.balloon.borderColor = "#70a1c2";
                event.chart.balloon.followCursor(true);
                event.chart.balloon.changeColor(event.serialDataItem.dataContext.color);
                event.chart.balloon.showBalloon("The % of students who reported " + tooltips[percentData.indexOf(event.serialDataItem.dataContext)]);
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
        for(let i = 0; i < groupLabels.length; i++) {
            let graph = new AmCharts.AmGraph();
            graph.type = "column";
            graph.title = groupLabels[i];
            graph.valueField = 'v'+i;
            if(tooltips != null) {
                graph.balloonFunction = function (graphDataItem, graph) {
                    let studentLabel = "";
                    if(graph.title === "Total")
                        studentLabel = "students";
                    else if(groupTitle === "Transgender Status") {
                        switch(i) {
                            case 0: studentLabel = "students who do not identify as transgender"; break;
                            case 1: studentLabel = "students who identify as transgender"; break;
                            case 2: studentLabel = "students who are unsure whether they are transgender"; break;
                            case 3: studentLabel = "students who answered \"don't know\""; break;
                        }
                    }
                    else if(groupTitle === "Has one or more disability (calculated)") {
                        switch(i) {
                            case 0: studentLabel = "students with no disability"; break;
                            case 1: studentLabel = "students with one or more disabilities"; break;
                            case 2: studentLabel = "students who were unsure whether they have a disability"; break;
                        }
                    }
                    else
                        studentLabel = graph.title + " students";
                    return graphDataItem.values.value.toFixed(1) +"% of "+studentLabel+" reported " + tooltips[graphDataItem.index];
                };
            }
            else if(groupTitle == null)
                graph.balloonText = "[[value]]% of students answered '[[category]]' to '"+mainTitle+"'";
            else
                graph.balloonText = "[[value]]% of students who answered <i>'"+graph.title+"'</i> to '"+groupTitle+"' also answered <i>'[[category]]'</i> to '"+mainTitle+"'";
            graph.lineAlpha = 0;
            graph.fillColors = fillColors[i];
            graph.fillAlphas = 1;
            chart.addGraph(graph);
        }

        // LEGEND
        var legend = new AmCharts.AmLegend();
        legend.position = "right";
        legend.labelWidth = 160;
        //legend.title = "";
        //legend.marginTop = (Math.ceil(groupTitle.length/20))*20;
        //legend.height = 300;
        chart.addLegend(legend);

        //chart.addListener("drawn",addLegendLabel);

        chart.export = {
            enabled: true
        };
        chart.write("chartdiv");
    });
}

/*function addLegendLabel(e) {
    var title = document.createElement("div");
    title.innerHTML = "<b>"+groupTitle+"</b>";
    title.className = "legend-title";
    e.chart.legendDiv.append(title)
}*/

function addBreakToTitle(label) {
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

function createLineChart(percentData, labels, xAxisLabel) {
    var graphs = [];
    for(var i = 0; i < labels.length; i++) {
        graphs.push({
            "id": "g"+i,
            "balloonText": "[[value]]%",
            "balloonFunction": function (graphDataItem, graph) {
                return graphDataItem.values.value != null ? graphDataItem.values.value.toFixed(1) +"%" : 'N/A';
            },
            "bullet": "round",
            "bulletBorderAlpha": 1,
            "hideBulletsCount": 50,
            "title": labels[i],
            "valueField": 'v'+i,
            "useLineColorForBulletBorder": true
        });
    }

    var chart = AmCharts.makeChart("chartdiv", {
        "type": "serial",
        "theme": "light",
        "marginRight": 30,
        "autoMarginOffset": 20,
        "marginTop": 25,
        "fontSize": 13,
        "dataProvider": percentData,
        "valueAxes": [{
            "axisAlpha": 0.2,
            "dashLength": 1,
            "position": "left",
            "minimum": 0,
            "title": "Percent %"
        }],
        "graphs": graphs,
        "chartCursor": {

        },
        "categoryField": "answer",
        "categoryAxis": {
            "parseDates": false,
            "axisColor": "#DADADA",
            "dashLength": 1,
            "minorGridEnabled": true,
            "title": xAxisLabel
        },
        "export": {
            "enabled": true
        },
        "legend": {
            "useGraphSettings": true,
            "position":"right",
            "labelWidth": 160,
            "valueFunction": function (graphDataItem) {
                if(graphDataItem.values == null)
                    return "";
                return graphDataItem.values.value != null ? graphDataItem.values.value.toFixed(1) +"%" : 'N/A';
            }
        }
    });

    return chart;
}

function exportToPDF(chart, mainTitle, groupTitle, year, dataset, filterString, pyramid) {
    var exportContent = [
        {
            text: "Fairfax County Youth Survey "+year,
            style: ["header"]
        },
        {
            text: mainTitle,
            style: ["subheader"]
        }];
    if(groupTitle != null) {
        exportContent.push({
                text: "compared to",
                style: ["description"]
            },
            {
                text: groupTitle,
                style: ["subheader"]
            });
    }
    exportContent.push({
        text: "Dataset = " + (dataset==='6th' ? '6th grade' : '8th, 10th, and 12th grades') + (pyramid > 0 ? ", Pyramid = " + pyramid : ''),
        style: ["description"]
    });
    if(filterString != null) {
        exportContent.push({
            text: filterString,
            style: ["description"]
        });
    }
    exportContent.push({
        image: "image_1",
        fit: [720,450],
        style: ["description"]
    });

    var pdf_layout = {
        pageOrientation: "landscape",
        pageSize: "LETTER",
        pageMargins: [ 20, 20, 20, 20 ],
        content: exportContent,
        images: {
        },
        styles: {
            header: {
                fontSize: 16,
                bold: true,
                alignment: "center",
                margin: [0, 0, 0, 10]
            },
            subheader: {
                alignment: "center",
                margin: [0, 0, 0, 5]
            },
            description: {
                fontSize: 10,
                italics: true,
                alignment: "center",
                margin: [ 0, 0, 0, 5]
            }
        }
    };

    chart.export.capture( {}, function() {
        this.toPNG({multiplier: 2},
            function( data ) {
                pdf_layout.images["image_1"] = data;
                this.toPDF(pdf_layout, function (data) {
                    this.download(data, this.defaults.formats.PDF.mimeType, "fairfaxgraph.pdf");
                });
            });
    });
}