var api = new APIRequest("get_course_statistics").sendAndFetch({
    id: getIDParam()
}).then(res => {
    var data = JSON.parse(res);
    data.forEach(chapterData => {
        var progCanvas = document.getElementById(chapterData['id'] + "_progress");
        var progVS = new VisualizedDataset({Progress: chapterData['progress']}, progCanvas);
        progVS.horizontalBarchart(100);
        var starCanvas = document.getElementById(chapterData['id'] + "_stars");
        var starVS = new VisualizedDataset({Stars: chapterData['stars']}, starCanvas);
        starVS.setFillStyle("orange");
        starVS.horizontalBarchart(5);
    });
});