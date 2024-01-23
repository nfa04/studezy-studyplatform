var vd = [];
function loadData() {
    vd = [];
    var api = new APIRequest("survey_results");
    var mode = document.getElementById("result_mode").value;
    api.sendAndFetch({
        "sid": getIDParam(),
        "mode": mode,
        "question": document.getElementById("question_selected").value
    }).then((response) => {
        var json = JSON.parse(response);
        var containers = document.getElementsByClassName("question_results");
        for(i = 0; i < containers.length; i++) {
            var visualized_set = new VisualizedDataset(json[containers[i].id], containers[i].getElementsByTagName("canvas")[0]);
            vd.push(visualized_set);
            visualized_set.piechart(50,50,50);
            var set = json[containers[i].id];
            containers[i].getElementsByTagName("select")[0].addEventListener("change", changeType.bind(this, i));
            for(index = 0; index < Object.keys(set).length; index++) {
                var tr = document.createElement("tr");
                var keyTd = document.createElement("td");
                var valueTd = document.createElement("td");
                keyTd.innerText = Object.keys(set)[index];
                valueTd.innerText = Object.values(set)[index];
                tr.appendChild(keyTd);
                tr.appendChild(valueTd);
                containers[i].getElementsByTagName("tbody")[0].appendChild(tr);
            }
        }
    });
}
loadData();
function changeType(i,e) {
    switch(parseInt(e.target.value)) {
        case 0:
            vd[i].piechart(50, 50, 50);
            break;
        case 1:
            vd[i].barchart();
            break;
    }
}