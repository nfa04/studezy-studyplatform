function sendChapter(chapterID) {
    var node = document.createElement("div");
    var popup = new Popup(node);
    var title = document.createElement("h2");
    title.innerText = "Send chapter";
    node.appendChild(title);
    var selectorNode = document.createElement("div");
    var selector = new ChatSelector(selectorNode);
    selector.choose().then(chatID => {
        window.location.href = "/messages/chat?chid=" + chatID + "&itype=chapter&id=" + chapterID + "&si=true";
        popup.close();
    });
    node.appendChild(selectorNode);
}