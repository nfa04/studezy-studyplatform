class UserRepresentation {
    name;
    id;

    constructor(id, name) {
        this.name = name;
        this.id = id;
    }

}

class UserSelector {

    node;
    contactBtn;
    userBtn;
    optionContainer;
    input;
    searchBtn;
    contacts; // Acts as a kind of cache

    constructor(domNode) {

        domNode.className = "studezy_selector";

        this.node = domNode;
        var optionsDiv = document.createElement("div");
        optionsDiv.className = "studezy_selector_btns";
        this.contactBtn = document.createElement("input");
        this.contactBtn.type = "button";
        this.contactBtn.value = "From contacts";
        optionsDiv.appendChild(this.contactBtn);
        this.userBtn = document.createElement("input");
        this.userBtn.type = "button";
        this.userBtn.value = "From all users";
        optionsDiv.appendChild(this.userBtn);
        this.node.appendChild(optionsDiv);
        var searchbarContainer = document.createElement("div");
        this.input = document.createElement("input");
        this.input.type = "text";
        searchbarContainer.appendChild(this.input);
        this.searchBtn = document.createElement("input");
        this.searchBtn.type = "button";
        this.searchBtn.value = "Search";
        searchbarContainer.appendChild(this.searchBtn);
        this.node.appendChild(searchbarContainer);
        this.optionContainer = document.createElement("div");
        this.node.appendChild(this.optionContainer);
        this.contacts = null;
    }

    chooseFromAll() {
        this.optionContainer.replaceChildren();
        return new Promise((resolve, reject) => {
            this.searchBtn.addEventListener("click", () => {
                this.optionContainer.replaceChildren();
                var lookupApi = new APIRequest("get_user_suggestion");
                lookupApi.sendAndFetch({
                    "uname": this.input.value
                }).then((response) => {
                    var data = JSON.parse(response);
                    data.forEach(user => {
                        var userDiv = document.createElement("div");
                        userDiv.innerText = user['uname'];
                        userDiv.addEventListener("click", () => {
                            var confirm = window.confirm("Do you want to add this user?");
                            if(confirm) {
                                resolve(new UserRepresentation(user['uid'], user['uname']));
                            }
                        });
                        this.optionContainer.appendChild(userDiv);
                    });
                });
            });
        });
    }

    chooseFromContacts() {
        function setSearchListener(ctx, contacts) {
            ctx.searchBtn.addEventListener("click", () => {
                listContacts(contacts.filter(contact => { 
                    return contact['cname'].startsWith(this.input.value)
                }));
            });
        }
        this.optionContainer.replaceChildren();
        return new Promise((resolve, reject) => {
            var listContacts = (contacts) => {
                this.optionContainer.replaceChildren();
                contacts.forEach(contact => {
                    var contactDiv = document.createElement("div");
                    contactDiv.innerText = contact['cname'] + " (" + contact['uname'] + ")";
                    contactDiv.addEventListener("click", () => {
                        var confirm = window.confirm("Do you want to add this user?");
                        if(confirm) resolve(new UserRepresentation(contact['uid'], contact['uname']));
                    });
                    this.optionContainer.appendChild(contactDiv);
                });
            }
            if(this.contacts == null) {
                var contactsApi = new APIRequest("get_contacts");
                contactsApi.fetch().then((response) => {
                    var contacts = JSON.parse(response);
                    this.contacts = contacts;
                    listContacts(contacts);
                    setSearchListener(this, contacts);
                });
            } else {
                listContacts(this.contacts);
                setSearchListener(this, this.contacts);
            }
        });
    }
}