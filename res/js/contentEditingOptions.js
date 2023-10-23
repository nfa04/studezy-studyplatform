var contentTable = document.getElementById("contentTable");
function addChapter() {
    var newRow = document.createElement("tr");
    var nameInput = document.createElement("input");
    nameInput.type = "text";
    nameInput.name = "name";
    var nameCell = document.createElement("td");
    var form = document.createElement("form");
    form.method = "post";
    form.action = "createChapter?i=" + getIDParam();
    form.appendChild(nameInput);
    var btn = document.createElement("input");
    btn.type = "submit";
    btn.value = "Create";
    form.appendChild(btn);
    nameCell.appendChild(form);
    newRow.appendChild(nameCell);
    contentTable.appendChild(newRow);
}
function moveChapter(elem, direction) {
    var tr = elem.parentNode.parentNode;
    if(!((tr.isSameNode(tr.parentNode.firstChild) && direction == 1) || (tr.isSameNode(tr.parentNode.lastChild) && direction == 0))) {
        var newNode = tr.cloneNode(true);
        contentTable.insertBefore(newNode, (direction == 1 ? tr.previousSibling : tr.nextSibling.nextSibling));
        tr.remove();
        var nodes = contentTable.childNodes;
        var ids = [];
        for(i = 0; i < nodes.length; i++) {
            if(nodes[i].nodeName == "TR") {
                ids.push(nodes[i].id.replace("content_", ""));
            }
        }
        var api = new APIRequest("chapter_update_order");
        api.sendAndTrigger({
            'cid': getIDParam(),
            'order': JSON.stringify(ids)
        });
    } else console.log("Tried to move first or last element");
}

function saveDescription() {
    var api = new APIRequest("course_update_description");
    api.sendAndTrigger({
        "cid": getIDParam(),
        "description": document.getElementById("description_textarea").value
    });
    var messageDiv = document.createElement("div");
    messageDiv.innerHTML = "Saved successfully!";
    var popup = new Popup(messageDiv);
}