const mime2db_types = {
    "image/jpg": 1,
    "image/jpeg": 1,
    "video/mp4": 2,
    "audio/ogg": 3,
    "survey": 4,
    "announcement": 5,
    "assignment": 6,
    "chat": 7,
    "chapter": 8
}

function dbtype2mime(dbType) {
    return Object.keys(mime2db_types)[Object.values(mime2db_types).indexOf(dbType)];
}

var pathAPI = new APIRequest("get_asset_root");
var CHAT_MEDIA_PATH = pathAPI.fetch() + "chat-";

var messages = document.getElementById('messages');
var form = document.getElementById('form');
var input = document.getElementById('input');

var active_chat_name = document.getElementById("active_chat_name");
var active_chat_img = document.getElementById("active_chat_img");

var userDataCache = {};
var userContactCache = {};

var chatManagers = [];

var activeCall;

var activeUser;

const typingWaitTime = 1500;   // Wait time for user typing events in milliseconds

function arrayFilterObjects(array) {
    return array.filter(item => typeof item === "object");
}

function cleanURL() {
    window.history.replaceState(null, document.title, "chat");
}

function getUsernameAndCacheIfNeeded(userID, socket) {
    if(Object.keys(userDataCache).indexOf(userID) === -1) {
        // username is not cached, fetch it.
        userDataCache[userID] = new Promise((resolve, reject) => {
            socket.emit("fetch_username", userID, (res) => {
                var data = JSON.parse(res);
                //addMessage(data['userName'], userID, content, data['contactName'], readBy);
                resolve(data);
                userDataCache[userID] = data;
            });
        });
    }
    // The following code lead to a bug whereas messages where displayed as soon as data had arrived which lead to them being displayed in wrong order

    // Code was removed

}

var chat = null;

var api = new APIRequest("get_messaging_token");
api.fetch().then((response) => {

    console.log(response);
    var userData = JSON.parse(response);

    var socket = io(userData['serverLocation'], {
    auth:  {
        token: userData['token'],
        userID: userData['userID'],
        userName: userData['userName']
    }
    });

    activeUser = userData['userID'];

    // check if a file has been uploaded and needs to be sent
    if(getGETParam("scf") !== null) {
        socket.emit("fsend", JSON.stringify({
            "fileID": getGETParam("scf"),
            "ott": getGETParam("ott"),
            "chatID": getGETParam("chid"),
            "ftype": mime2db_types[getGETParam("t")]
        }));
        // clean up the url
        cleanURL();
    }
    
    // auto-load a chat
    if(getGETParam("i") !== null) {
        chat = getGETParam("i");
        setTimeout(() => socket.emit("fetch_messages", chat), 1000);
        cleanURL();
    }

    // auto-generate a chat
    if(getGETParam("cc") !== null) {
        socket.emit("create_chat", JSON.stringify({
            "name": "New chat",
            "users": [getGETParam("cc")]
        }), (chat_id) => {
            console.log(chat_id);
            chat = chat_id;
            cleanURL();
        });
    }

    if(getGETParam("si") !== null) {
        var messageData = {
            r: getGETParam("chid"),
            c: getGETParam("id"),
            t: mime2db_types[getGETParam("itype")]
        }

        socket.emit('msg', JSON.stringify(messageData));
        chat = getGETParam("chid");

        cleanURL();
    }

    // opens a popup to create a new contact
    function newContact() {
        var popupnode = document.createElement("div");
        var ptitle = document.createElement("h2");
        ptitle.innerText = "New contact";
        popupnode.appendChild(ptitle);
        var pform = document.createElement("form");
        var pcname = document.createElement("input");
        pcname.type = "text";
        pcname.placeholder = "Contact name";
        pform.appendChild(pcname);
        popupnode.appendChild(pform);
        var puselect = document.createElement("div");
        var puname = document.createElement("input");
        puname.type = "text";
        puname.placeholder = "StudEzy username";
        var pusearch = document.createElement("input");
        pusearch.type = "button";
        pusearch.value = "Search";
        puresults = document.createElement("div");
        pusearch.addEventListener("click", () => {
            puresults.replaceChildren();
            var api = new APIRequest("get_user_by_name");
            api.sendAndFetch({
                "uname": puname.value
            }).then((response) => {
                if(response != 0) {
                    var data = JSON.parse(response);
                    var result = document.createElement("div");
                    result.innerText = "Found: " + data['userName'];
                    puresults.appendChild(result);
                    var pusubmit = document.createElement("input");
                    pusubmit.type = "button";
                    pusubmit.value = "Create";
                    pusubmit.addEventListener("click", () => {
                        console.log("Creating contact");
                        socket.emit("contact_create", JSON.stringify({
                            "uid": data['userID'],
                            'name': pcname.value
                        }));
                        popup.close();
                    });
                    puresults.appendChild(pusubmit);
                } else puresults.innerText = "Not found.";
            });
        });
        puselect.appendChild(puname);
        puselect.appendChild(pusearch);
        popupnode.appendChild(puselect);
        popupnode.appendChild(puresults);
        var popup = new Popup(popupnode);
    }
    document.getElementById("new_contact").addEventListener("click", newContact);

    function createChat() {
        var popupnode = document.createElement("div");
        var ptitle = document.createElement("h2");
        ptitle.innerText = "New chat";
        popupnode.appendChild(ptitle);
        var pform = document.createElement("form");
        var pcname = document.createElement("input");
        pcname.type = "text";
        pcname.placeholder = "Chat name";
        pform.appendChild(pcname);

        var selected = [];
        function processID(user, callback) {
            if(selected.indexOf(user.id) === -1) {
                selected.push(user.id);
                var span = document.createElement("span");
                span.innerText = user.name;
                var remove = document.createElement("a");
                remove.href = "javascript:;";
                remove.addEventListener("click", () => {
                    selected.splice(selected.indexOf(user.id),1);
                    span.remove();
                });
                remove.innerHTML = " (Remove <img src='/res/img/minus.svg' height='18'>)";
                span.appendChild(remove);
                pcselected.appendChild(span);
                callback();
            }
        }

        function chooseUserFromContacts() {
            selector.chooseFromContacts().then(user => processID(user, chooseUserFromContacts));
        }

        function chooseUserFromUsers() {
            selector.chooseFromAll().then(user => processID(user, chooseUserFromUsers));
        }

        var selectNode = document.createElement("div");
        var selector = new UserSelector(selectNode);
        var pcselected = document.createElement("div");
        selector.contactBtn.addEventListener("click", chooseUserFromContacts.bind(this));
        selector.userBtn.addEventListener("click", chooseUserFromUsers.bind(this));
        pform.appendChild(selectNode);
        pform.appendChild(pcselected);
        var pccreate = document.createElement("input");
        pccreate.value = "Create";
        pccreate.type = "button";
        pccreate.addEventListener("click", () => {
            socket.emit("create_chat", JSON.stringify({
                "name": pcname.value,
                "users": selected
            }), (chat_id) => {
                socket.emit("fetch_chats");
            });
            /*// Set a timeout to make sure the chat was created before chats are refetched
            setTimeout(() => socket.emit("fetch_chats"), 500);*/
            popup.close();
        });
        pform.appendChild(pccreate);
        popupnode.appendChild(pform);
        var popup = new Popup(popupnode);
    }
    document.getElementById("new_chat").addEventListener("click", createChat);

    // fetch chats the user is in
    socket.emit("fetch_chats");
    
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if(input.value) {
            var messageData = {
                r: chat,
                c: input.value,
                t: 0
            }

            socket.emit('msg', JSON.stringify(messageData));
            addMessage('You', userData['userID'], input.value);
        }
    });

    let timer;              // Timer identifier
    let lastUTEvent = 0;
    const waitTime = 1500;   // Wait time in milliseconds

    input.addEventListener("keyup", () => {
        if(chat !== null) {
            // reduce amount of ut events emitted
            if((Date.now() - lastUTEvent) > typingWaitTime) {
                console.log(chat);
                socket.emit("ut", chat);
                lastUTEvent = Date.now();
            }

            clearTimeout(timer);

            timer = setTimeout(() => {
                socket.emit("ust", chat); // ust = user stopped typing
            }, typingWaitTime);
        }
    });


    socket.on('msg', (msg) => {
        var messageData = JSON.parse(msg);
        if(chat == messageData['r']) {
            // The message is intended for the chat looked at at the time
            getUsernameAndCacheIfNeeded(messageData['s'], socket);
            Promise.all(arrayFilterObjects(Object.values(userDataCache))).then(() => {
                addMessage(userDataCache[messageData['s']]['userName'], messageData['s'], messageData['c'], userDataCache[messageData['s']]['contactName'], undefined, messageData['t']);
            });
        } else chatManagers[messageData['r']].indicateNewMessage();
    });

    // fires when chats are fetched successfully. Display them in the UI
    socket.on("chats_fetched", (data) => {
        var chatContainer = document.getElementById("chat_container");
        chatContainer.replaceChildren();
        JSON.parse(data).forEach(element => {
            var chatDiv = document.createElement("div");
            var chatImg = document.createElement("img");
            chatImg.src = "/assets/chat_images/" + element['chat_id'] + ".jpg";
            chatDiv.appendChild(chatImg);
            var chatName = document.createTextNode(" " + element['name']);
            chatDiv.appendChild(chatName);
            chatDiv.addEventListener("click", () => {
                chat = element['chat_id'];
                chatManagers[chat].setActive();
                socket.emit("fetch_messages", element['chat_id']);
            });
            chatManagers[element['chat_id']] = new ChatManager(chatDiv, element['chat_id'], element['name']);
            chatContainer.appendChild(chatDiv);
        });
        console.log(JSON.parse(data));
    });

    // fires when messages are fetched, display them
    socket.on("messages_fetched", (data) => {
        //var messageContainer = document.getElementById("messages");
        messages.replaceChildren();
        JSON.parse(data).reverse().forEach(element => {
            getUsernameAndCacheIfNeeded(element['sender'], socket);
            Promise.all(arrayFilterObjects(Object.values(userDataCache))).then(() => {
                addMessage(userDataCache[element['sender']]['userName'], element['sender'], element['content'], userDataCache[element['sender']]['contactName'], element['readBy'], element['message_type']);
            });
        });
    });

    socket.on("ut", (d) => {
        var data = JSON.parse(d);
        getUsernameAndCacheIfNeeded(data['user'], socket);
        Promise.all(arrayFilterObjects(Object.values(userDataCache))).then(() => {
            chatManagers[data['chat']].indicateUserTyping(userDataCache[data['user']]['userName']);
        });
    });

    socket.on("ust", (chatID) => {
        chatManagers[chatID].stopTypingIndication();
    });

    active_chat_name.addEventListener("click", () => {

        // Helper function to add the user in the end
        function addUserToGroup(userID, popup) {
            socket.emit("auser", { // = add user
                userID: userID,
                chatID: chat
            });
        }

        socket.emit("cmfetch", chat, (res) => { // = chat metainformation
            function activeUserIsAdmin() {
                return userIsAdmin(userData['userID']);
            }
            function userIsAdmin(userID) {
                return res['members'].find(o => o['uid'] === userID)['admin'];
            }
            console.log(res);
            var popupnode = document.createElement("div");
            var ptitle = document.createElement("h2");
            ptitle.innerText = res['name'];
            if(activeUserIsAdmin()) { // Check if user is admin in this chat, otherwise don't display editing options
                var editTitleIcon = document.createElement("img");
                editTitleIcon.src = "/res/img/edit.svg";
                editTitleIcon.height = "20";
                editTitleIcon.addEventListener("click", () => {
                    popup.close();
                    var editingNode = document.createElement("div");
                    var editingTitle = document.createElement("h2");
                    editingTitle.innerText = "Edit chat";
                    editingNode.appendChild(editingTitle);
                    var optionsTable = document.createElement("table");
                    var nameRow = document.createElement("tr");
                    var nameField = document.createElement("td");
                    nameField.innerText = "Chat name";
                    var nameInputField = document.createElement("td");
                    var nameInput = document.createElement("input");
                    nameInput.type = "text";
                    optionsTable.appendChild(nameRow);
                    nameRow.appendChild(nameField);
                    nameRow.appendChild(nameInputField);
                    nameInputField.appendChild(nameInput);
                    editingNode.appendChild(optionsTable);
                    var confirmBtn = document.createElement("input");
                    confirmBtn.type = "button";
                    confirmBtn.value = "Confirm";
                    confirmBtn.addEventListener("click", () => {
                        socket.emit("ccname", { // = change chat name
                            chatID: chat,
                            name: nameInput.value
                        }, () => {
                            editingPopup.close();
                            socket.emit("fetch_chats");
                        });
                    });
                    editingNode.appendChild(confirmBtn);
                    var editingPopup = new Popup(editingNode);
                });
                ptitle.appendChild(editTitleIcon);
            }
            popupnode.appendChild(ptitle);
            var createddiv = document.createElement("div");
            createddiv.innerText = "Created: " + res['created'];
            popupnode.appendChild(createddiv);
            var membertitle = document.createElement("h3");
            membertitle.innerText = "Members";
            popupnode.appendChild(membertitle);
            var memberlist = document.createElement("ul");
            res['members'].forEach((member) => {
                var memberli = document.createElement("li");
                getUsernameAndCacheIfNeeded(member['uid'], socket);
                Promise.all(arrayFilterObjects(Object.values(userDataCache))).then(() => {
                    memberli.innerText = userDataCache[member['uid']]['userName'] + " ";
                    if(activeUserIsAdmin()) {
                        var memberremove = document.createElement("a");
                        memberremove.href = "javascript:;";
                        memberremove.innerText = "(Remove)";
                        memberremove.addEventListener("click", () => {
                            var confirm = window.confirm("Do you really want to remove this user?");
                            if(confirm) {
                                socket.emit("ruser", {
                                    chatID: chat,
                                    userID: member['uid']
                                }, () => {
                                    memberli.remove();
                                });
                            }
                        });
                        memberli.appendChild(memberremove);
                    }
                    memberlist.appendChild(memberli);
                });
            });
            if(activeUserIsAdmin()) {
                var addmember = document.createElement("li");
                var addmembera = document.createElement("a");
                addmembera.href = "javascript:;";
                addmembera.innerText = "Add user";
                addmembera.addEventListener("click", () => {
                    popup.close();
                    var adduser = document.createElement("div");
                    var addtitle = document.createElement("h2");
                    addtitle.innerText = "Add user";
                    adduser.appendChild(addtitle);
                    var selectorDiv = document.createElement("div");
                    var selector = new UserSelector(selectorDiv);
                    selector.userBtn.addEventListener("click", () => {
                        selector.chooseFromAll().then((user) => {
                            addUserToGroup(user.id);
                            addUserPopup.close();
                        });
                    });
                    selector.contactBtn.addEventListener("click", () => {
                        selector.chooseFromContacts().then((user) => {
                            addUserToGroup(user.id);
                            addUserPopup.close();
                        });
                    });
                    adduser.appendChild(selectorDiv);
                    var addUserPopup = new Popup(adduser);
                });
                addmember.appendChild(addmembera);
                memberlist.appendChild(addmember);
            }
            popupnode.appendChild(memberlist);
            var popup = new Popup(popupnode);
        }); 
    });

});

function addMessage(userName, userID, content, contactName = undefined, readBy = undefined, messageType = undefined) {
    var i = document.createElement("div");
    i.className = "message";
    var userDiv = document.createElement("div");
    var a = document.createElement("a");
    a.textContent = (typeof contactName !== "undefined" ? contactName + " (" + userName + ")" : userName);
    a.href = "/account/view?i=" + userID;
    userDiv.appendChild(a);
    i.appendChild(userDiv);
    var messageDiv = document.createElement("div");
    if(messageType == 0 || typeof messageType == "undefined" || messageType == null) messageDiv.textContent = content;
    else if(messageType == mime2db_types["image/jpeg"]) {
        var img = document.createElement("img");
        img.src = CHAT_MEDIA_PATH + content + ".jpeg";
        messageDiv.appendChild(img);
    }
    else if(messageType == mime2db_types["video/mp4"]) {
        var video = document.createElement("video");
        video.src = CHAT_MEDIA_PATH + content + ".mp4";
        video.controls = true;
        messageDiv.appendChild(video);
    }
    else if(messageType == mime2db_types["audio/ogg"]) {
        var audio = document.createElement("audio");
        audio.src = CHAT_MEDIA_PATH + content + ".ogg";
        audio.controls = true;
        messageDiv.appendChild(audio);
    } else if(messageType == mime2db_types["chapter"]) {
        console.log(content);
        var icon = document.createElement("img");
        icon.src = "/res/img/chapter.svg";
        messageDiv.appendChild(icon);
        var a = document.createElement("a");
        a.href = "/courses/chapter?i=" + content;
        var info = document.createElement("div");
        var chapterAPI = new APIRequest("get_chapter_metadata");
        chapterAPI.sendAndFetch({
            id: content
        }).then(data => {
            var chapter = JSON.parse(data);
            a.innerText = chapter['name'];
            info.appendChild(document.createTextNode("by "));
            var aOwner = document.createElement("a");
            aOwner.href = "/account/view?i=" + chapter['ownerID'];
            aOwner.innerText = chapter['ownerName'];
            info.appendChild(aOwner);
            info.appendChild(document.createTextNode(" in "));
            var aCourse = document.createElement("a");
            aCourse.href = "/courses/view?i=" + chapter['courseID'];
            aCourse.innerText = chapter['courseName'];
            info.appendChild(aCourse);
        });
        messageDiv.className = "studezy_item";
        messageDiv.appendChild(a);
        messageDiv.appendChild(info);
    }
    i.appendChild(messageDiv);
    if(typeof readBy != "undefined") {
        var readByDiv = document.createElement("div");
        readByDiv.innerText = "Read by: " + readBy.toString();
        messageDiv.appendChild(readByDiv);
    }
    messages.append(i);
    window.scrollTo(0, document.body.scrollHeight);
    input.value = '';
}

class ChatManager {

    node;
    chid;
    indicateCounter = 0;
    indicator;
    typingIndicator;
    name;

    constructor(containerNode, chatID, chatName) {
        this.node = containerNode;
        this.chid = chatID;
        this.indicator = document.createElement("span");
        this.indicator.className = "message_indicator";
        this.node.appendChild(this.indicator);
        this.node.addEventListener("click", this.removeIndicator.bind(this));
        this.typingIndicator = document.createElement("div");
        this.name = chatName;
    }

    indicateNewMessage() {
        this.indicateCounter++;
        this.indicator.textContent = " +" + this.indicateCounter.toString();
    }

    removeIndicator() {
        this.indicator.textContent = "";
    }

    indicateUserTyping(userName) {
        this.typingIndicator.innerText = userName + " is typing...";
        this.node.appendChild(this.typingIndicator);
    }

    stopTypingIndication() {
        this.typingIndicator.remove();
    }

    setActive() {
        active_chat_name.innerText = this.name;
        active_chat_img.src = "/assets/chat_images/" + this.chid + ".jpg";
    }
    
}