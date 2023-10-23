function comment(type) {
    var content = document.getElementById("comment_textarea").value;
    var api = new APIRequest("comment");
    api.sendAndTrigger({
        "comment_type": type,
        "comment_on": getIDParam(),
        "content": content
    });
    var commentDiv = document.createElement("div");
    var infoDiv = document.createElement("div");
    var nameText = document.createElement("b");
    nameText.innerText = "You";
    infoDiv.appendChild(nameText);
    infoDiv.appendChild(document.createTextNode(" - just now"));
    var contentDiv = document.createElement("div");
    contentDiv.innerText = content;
    commentDiv.appendChild(infoDiv);
    commentDiv.appendChild(contentDiv);
    document.getElementById("comment_container").insertBefore(commentDiv, document.getElementById("comment_adding_section"));
    document.getElementById("comment_textarea").value = "";
}