class ChatSelector {

    node;
    optionContainer;

    constructor(domNode) {

        domNode.className = "studezy_selector";

        this.node = domNode;
        
        this.optionContainer = document.createElement("div");
        this.node.appendChild(this.optionContainer);
    }

    choose() {
        return new Promise((resolve, reject) => {
            new APIRequest("get_chats").fetch().then(res => {
                var chats = JSON.parse(res);
                chats.forEach(chat => {
                    var div = document.createElement("div");
                    var img = document.createElement("img");
                    img.src = "/res/img/group.svg";
                    div.appendChild(img);
                    div.appendChild(document.createTextNode(" " + chat['name']));
                    div.addEventListener("click", () => resolve(chat['chat_id']));
                    this.optionContainer.appendChild(div);
                });
            });
        });
    }

}