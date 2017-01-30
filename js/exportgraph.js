/**
 * Created by tiddd on 1/3/2017.
 */
function exportGraph() {
    var exportContent = [
        {
            text: "2015 Fairfax County Youth Survey",
            style: ["header"]
        },
        {
            text: mainQuestion,
            style: ["subheader"]
        }];
    if(groupQuestion != null) {
        exportContent.push({
                text: "compared to",
                style: ["description"]
            },
            {
                text: groupQuestion,
                style: ["subheader"]
            });
    }
    if(filterString != null) {
        exportContent.push({
            text: filterString,
            style: ["description"]
        });
    }
    exportContent.push({
        image: "image_1",
        fit: [720,470],
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
