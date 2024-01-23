const api = new APIRequest("get_doc_token");
api.fetch().then(data => {
    var userData = JSON.parse(data);
    var socket = io("http://localhost:3004", {
        auth:  {
            map: getIDParam(),
            token: userData['token'],
            userID: userData['userID'],
            userName: userData['userName']
        }
    });

    var map = new MindMap("test map", null, document.getElementById("tree_container"));
    map.createFromTree({"main":{"id":3522,"c":{"child":{"c":{},"id":4668},"other":{"c":{"inner":{"c":{"test":{"c":{},"id":1828}},"id":5110}},"id":3679}}}});
    map.draw(document.body);

    document.getElementById("add_node_btn").addEventListener("click", () => {
        var pNode = document.createElement("div");
        var popup = new Popup(pNode);
        var title = document.createElement("h2");
        title.innerText = "Add a bubble";
        var contentInput = document.createElement("input");
        contentInput.type = "text";
        contentInput.placeholder = "Content...";
        var confirmBtn = document.createElement("input");
        confirmBtn.type = "button";
        confirmBtn.value = "Add";
        confirmBtn.addEventListener("click", () => {
            var delta = map.addToSelectedNode(contentInput.value);
            socket.emit("update", delta.getDeltaString());
            popup.close();
        });
        pNode.appendChild(title);
        pNode.appendChild(contentInput);
        pNode.appendChild(confirmBtn);
    });

    document.getElementById("remove_node_btn").addEventListener("click", () => {
        socket.emit("update", map.removeSelectedNode().getDeltaString());
    });

    socket.on("update", deltaString => {
        var delta = new MindDelta();
        delta.createFromDeltaString(deltaString, map);
        map.applyDelta(delta);
    });
    
});