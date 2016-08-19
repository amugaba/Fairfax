/**
 * Created by David on 8/19/2016.
 */
var finalCounts = [], finalPercents = [], answerLables = [], groupLabels = [];

function writeCSV(isPercent) {
    var csvContent = "data:text/csv;charset=utf-8,";
    csvContent += ","+groupLabels.join(",") + "\n";

    for(var i=0; i<answerLables.length; i++)
    {
        csvContent += answerLables[i]+",";
        for(var j=0; j<groupLabels.length; j++) {
            if(isPercent)
                csvContent += finalPercents[i]["v"+j]+",";
            else
                csvContent += finalCounts[i]["v"+j]+",";
        }

        if(i < answerLables.length-1)
            csvContent += "\n";
    }
    return csvContent;
}

function tableToExcel(isPercent) {
    var encodedUri = encodeURI(writeCSV(isPercent));
    var link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "fairfaxdata.csv");
    document.body.appendChild(link); // Required for FF
    link.click();
}