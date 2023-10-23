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

    document.getElementById("videocall_option").addEventListener("click", async () => {
        activeCall = new Call(socket, chat, "video");
        activeCall.confirmCallInitiation().then(() => {
            activeCall.initiate();
        });
    });

    document.getElementById("call_option").addEventListener("click", () => {
        activeCall = new Call(socket, chat, "audio");
        activeCall.confirmCallInitiation().then(() => {
            activeCall.initiate();
        });
    });

    socket.on("call", async (data) => {
          if(activeCall === null || typeof activeCall == "undefined") activeCall = new Call(socket, data['chatID'], data['type'], data['chatName']);
          activeCall.join(data['caller'], data['offer']);
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

class Call {

    connections;
    socket;
    type;
    chatID;
    popup;
    popupNode;
    memberContainer;
    dataChannels;
    userIsInitiator;
    stream;
    containers;
    chatContainer;
    muteButtons;
    muteStates;
    muteStateDisplays;
    confirmed;
    chatName;
    stateDisplay;
    videoStates;
    videoButtons;
    videoStateDisplays;
    userMediaElements;

    constructor(socket, chatID, type, chatName = undefined) {
        this.muteButtons = {};
        this.muteStates = {};
        this.muteStateDisplays = {};

        this.videoStates = {};
        this.videoButtons = {};
        this.videoStateDisplays = {};

        this.chatID = chatID;
        this.socket = socket;
        this.type = type;
        this.chatName = chatName;
        this.connections = {};

        this.dataChannels = {};
        this.userIsInitiator = false;
        this.containers = {};
        this.confirmed = false;

        this.userMediaElements = {};

        this.popupNode = document.createElement("div");
        var ptitle = document.createElement("h2");
        ptitle.innerText = "Active call";
        this.popupNode.appendChild(ptitle);
        this.memberContainer = document.createElement("div");
        this.memberContainer.className = "call_container";
        this.popupNode.appendChild(this.memberContainer);

        // Create a toolbar
        var toolbar = document.createElement("div");
        this.stateDisplay = document.createElement("div");
        toolbar.appendChild(this.stateDisplay);
        toolbar.className = "call_toolbar";
        if(this.type == "video") {
            var stopVideo = document.createElement("img");
            stopVideo.src = "/res/img/camera_off.svg";
            stopVideo.addEventListener("click", this.changeVideoState.bind(this, activeUser));
            this.videoButtons[activeUser] = stopVideo;
            toolbar.appendChild(stopVideo);
        }
        var muteSelf = document.createElement("img");
        muteSelf.src = "/res/img/mute.svg";
        muteSelf.addEventListener("click", this.muteUser.bind(this, activeUser));
        toolbar.appendChild(muteSelf);
        this.muteButtons[activeUser] = muteSelf;
        var speechRequestBtn = document.createElement("img");
        speechRequestBtn.src = "/res/img/front_hand.svg";
        speechRequestBtn.addEventListener("click", this.requestSpeech.bind(this));
        toolbar.appendChild(speechRequestBtn);
        var endCallBtn = document.createElement("img");
        endCallBtn.src = "/res/img/call_end.svg";
        endCallBtn.addEventListener("click", this.leaveCall.bind(this));
        toolbar.appendChild(endCallBtn);
        this.popupNode.appendChild(toolbar);


        this.popup = new Popup(this.popupNode, false, "3%");
        this.popup.hide();
        /*this.chatContainer = document.createElement("div");
        this.popupNode.appendChild(this.chatContainer);*/

        // Add listeners for the socket
        socket.on("call_ans", data => {
            this.addCallAnswer(data['caller'], data['desc']);
        });
    
        socket.on("ice", data => {
            this.addIceCandidate(data['caller'], data['candidate']);
        });
    }

    display() {
        this.popup.show();
    }

    addStream(stream, userID) {
        this.muteStates[userID] = false;
        if(typeof this.userMediaElements[userID] == "undefined") {
            var container = document.createElement("div");
            container.className = "call_streamcontainer";
            var media;
            if(this.type == "video") {
                media = document.createElement("video");
                media.srcObject = stream;
                media.play();
                container.appendChild(media);
            } else {
                media = document.createElement("audio");
                media.srcObject = stream;
                if(userID != activeUser) media.play();
                container.appendChild(media);
            }
            if(userID == activeUser) media.muted = true;
            this.userMediaElements[userID] = media;
            getUsernameAndCacheIfNeeded(userID, this.socket);
            var nameDiv = document.createElement("div");
            container.appendChild(nameDiv);
            Promise.all(arrayFilterObjects(Object.values(userDataCache))).then(() => {
                nameDiv.innerText = userDataCache[userID]['userName'];
            });
            
            // Create a toolbar for every user if user is the initiator (initiator = admin rights for call)
            if(this.userIsInitiator && userID != activeUser) {
                var userToolbar = document.createElement("div");
                userToolbar.className = "call_user_toolbar";
                if(this.type == "video") {
                    var userVideo = document.createElement("img");
                    userVideo.src = "/res/img/camera_off.svg";
                    userVideo.title = "Disable video";
                    userVideo.addEventListener("click", this.changeVideoState.bind(this, userID));
                    this.videoButtons[userID] = userVideo;
                    userToolbar.appendChild(userVideo);
                }
                var muteUser = document.createElement("img");
                muteUser.src = "/res/img/mute.svg";
                muteUser.title = "Mute user";
                muteUser.addEventListener("click", this.muteUser.bind(this, userID));
                this.muteButtons[userID] = muteUser;
                userToolbar.appendChild(muteUser);
                var wordAssignment = document.createElement("img");
                wordAssignment.src = "/res/img/speaking.svg";
                wordAssignment.title = "Assign user to speak";
                wordAssignment.addEventListener("click", this.assignWord.bind(this, userID));
                userToolbar.appendChild(wordAssignment);
                var removeUser = document.createElement("img");
                removeUser.src = "/res/img/person_remove.svg";
                removeUser.title = "Remove user";
                removeUser.addEventListener("click", this.removeUser.bind(this, userID));
                userToolbar.appendChild(removeUser);
                container.appendChild(userToolbar);
            }
    
            this.memberContainer.appendChild(container);
            this.containers[userID] = container;
        } else {
            this.userMediaElements[userID].srcObject = stream;
        }
    }

    confirmCallInitiation() {
        if(this.chatID !== null)
        return new Promise((resolve, reject) => {
            var confirm = window.confirm("Would you like to initiate a call?");
            if(confirm) resolve();
            else {
                this.popup.close();
                reject();
            }
        }); else return new Promise((resolve, reject) => {
            alert("Pick a chat first"); 
            reject();
        });
    }

    connect(userID) {
        console.log("Connecting to: ", userID);
        var rtc = new RTCPeerConnection({
            iceServers: [
            // Information about ICE servers - Use your own!
            {
                urls: "stun:stun.stunprotocol.org"
            },
            ],
        }, {optional:[{RtpDataChannels: true}]});
        this.connections[userID] = rtc;
        var dc = rtc.createDataChannel("data");
        dc.addEventListener("open", () => console.log("datachannel opened"));
        dc.addEventListener("message", this.processMessage.bind(this, userID))
        this.dataChannels[userID] = dc;
        rtc.addEventListener('icecandidate', event => {
            console.log("ice");
            if(event.candidate) {
                setTimeout(() => {
                    this.socket.emit("ice", {
                        user: userID,
                        candidate: event.candidate
                    });
                }, 200); // Wait before sending ice candidates to make sure the other parties are properly set up
            }
        });
        rtc.addEventListener('track', async (event) => {
            console.log(event);
            var [remoteStream] = event.streams;
            this.addStream(remoteStream, userID);
        });
        this.stream.getTracks().forEach(track => {
            rtc.addTrack(track, this.stream);
        });
        rtc.createOffer().then(async (offer) => {
            console.log(offer);
            return await rtc.setLocalDescription(new RTCSessionDescription(offer));
        })
        .then(() => {
        console.log('sending local desc:', rtc.localDescription);
            this.socket.emit("call", {
                user: userID,
                desc: rtc.localDescription,
                chatID: this.chatID,
                type: this.type
            });
        });
    }

    initiate() {
        this.display();
        this.userIsInitiator = true;
        // Create a connection for each chat member
        console.log(this.chatID);
        this.socket.emit("cmfetch", this.chatID, (res) => {
            navigator.mediaDevices.getUserMedia({
                audio: true,
                video: true
            }).then(stream => {
                this.stream = stream;
                this.addStream(stream, activeUser);
                res['members'].forEach(member => {
                    if(member['uid'] != activeUser) {
                        this.connect(member['uid']);
                    }
                });
            });
        });
    }

    // PROBABLY NEEDS A BUGFIX: IF OTHER PEOPLE TRY TO CONNECT BEFORE CALL WAS ACCEPTED
    confirmJoin() {
        return new Promise((resolve, reject) => {
            if(this.confirmed) resolve();
            else {
                var notification = new InAppNotification("Incoming call", "\"" + this.chatName + "\" is calling you...", 30);
                notification.setOptions(["Accept", "Decline"], 30).then((result) => {
                    notification.kill();
                    if(result == 0) {
                        this.confirmed = true;
                        this.display();
                        resolve(true);
                    }
                    else reject();
                });
            }
        });
    }

    async join(userID, offer) {
        console.log(this.chatID);
        this.confirmJoin().then(async () => {
        var rtc = new RTCPeerConnection({
            iceServers: [
            // Information about ICE servers - Use your own!
            {
                urls: "stun:stun.stunprotocol.org",
            },
            ],
        }, {optional:[{RtpDataChannels: true}]});
        rtc.ondatachannel = (event) => {
            this.dataChannels[userID] = event.channel;
            event.channel.addEventListener("message", this.processMessage.bind(this, userID))
        }
        this.connections[userID] = rtc;
        rtc.setRemoteDescription(new RTCSessionDescription(offer));
        rtc.onicecandidate = (event) => {
            console.log("ice");
            if(event.candidate) {
                this.socket.emit("ice", {
                    user: userID,
                    candidate: event.candidate
                });
            }
        }
        rtc.addEventListener('track', async (event) => {
            var [remoteStream] = event.streams;
            this.addStream(remoteStream, userID);
        });
        var stream = await navigator.mediaDevices.getUserMedia({
            audio: true, video: true
        });
        this.stream = stream;
        this.addStream(stream, activeUser);
            console.log("adding track");
            stream.getTracks().forEach(track => {
                rtc.addTrack(track, stream);
            });
        rtc.createAnswer().then(function(offer) {
            return rtc.setLocalDescription(offer);
        })
        .then(() => {
            console.log('sending local desc:', rtc.localDescription);
            this.socket.emit("call_ans", {
                user: userID,
                desc: rtc.localDescription,
                chatID: this.chatID
            });
        });
    });
    }

    addCallAnswer(userID, answer) {
        this.connections[userID].setRemoteDescription(new RTCSessionDescription(answer));
        // Tell other peers to connect to this user if you're the initiator of the call
        if(this.userIsInitiator) {
            this.dataChannels[userID].addEventListener("open", () => {
                console.log("connection changed");
                if(this.connections[userID].connectionState == "connected") this.sendConnectionRequest(userID);
            });
        }
    }

    addIceCandidate(userID, candidate) {
        if(typeof this.connections[userID] != "undefined") 
        this.connections[userID].addIceCandidate(new RTCIceCandidate(candidate));
    }

    leaveCall() {
        if(this.userIsInitiator) {
            this.sendToPeers({
                type: "callEnd"
            });
        } else {
            this.sendToPeers({
                type: "callLeave"
            });
        }
        this.disconnect();
        
        // Killing this call object to make sure we're able to connect to new incoming
        activeCall = null;
    }

    disconnect() {
        Object.keys(this.connections).forEach(key => {
            this.connections[key].close();
        });
        this.stream.getTracks().forEach(function(track) {
            track.stop();
        });
        this.popup.close();
    }

    sendConnectionRequest(userID) {
        // Send a connection request to tell all already connected peers a new user has connected and they should initialize a connection now
        console.log("Sending connection requests");
        Object.keys(this.dataChannels).forEach(key => {
            // Don't send the request to the user which just joined obviously
            if(key !== userID) {
                this.dataChannels[key].send(JSON.stringify({
                    type: "connectionRequest",
                    userID: userID
                }));
            }
        });
    }

    processMessage(sender, message) {
        var msg = JSON.parse(message.data);
        switch(msg['type']) {
            case "connectionRequest":
                console.log("got connection request for: ", msg['userID']);
                this.connect(msg['userID']);
                break;
            case "speechRequest":   
                console.log("Got a speech request from:", sender);
                var div = document.createElement("div");
                var img = document.createElement("img");
                img.src = "/res/img/front_hand.svg";
                div.appendChild(img);
                var textNode = document.createTextNode(" Would like to speak");
                div.appendChild(textNode);
                this.containers[sender].appendChild(div);
                setTimeout(() => {
                    div.remove();
                }, 30000); // Remove the request after 30s
                break;
            case "wordAssignment":
                var div = document.createElement("div");
                var img = document.createElement("img");
                img.src = "/res/img/exclamation.svg";
                div.appendChild(img);
                div.appendChild(document.createTextNode(" was assigned to talk"));
                this.containers[msg['user']].appendChild(div);
                if(msg['user'] == activeUser) {
                    this.addStateNotification("You are assigned to speak now.", 15);
                }
                break;
            case "callEnd":
                this.disconnect();
                break;
            case "callLeave":
                var div = document.createElement("div");
                div.innerText = "Just left.";
                this.containers[sender].appendChild(div);
                this.removeStream(sender);
                break;
            case "muteStateChange":
                this.updateUserMuteState(msg['user']);
                break;
            case "userRemoved":
                if(msg['user'] == activeUser) {
                    this.leaveCall();
                    new InAppNotification("Removed from call", "You were just removed from your active call", 15);
                }
                else {
                    this.connections[msg['user']].close();
                    this.removeStream(msg['user']);
                }
                break;
            case "userVideoStateChange":
                this.updateUserVideoState(msg['user']);
                break;
        }
    }

    removeStream(userID, timeout = 5) {
        setTimeout(() => {
            this.containers[userID].remove();
        }, timeout * 1000); // After 5s remove the user from display
    }

    // Send messages to all peers
    sendToPeers(message) {
        Object.keys(this.dataChannels).forEach(key => {
            if(this.dataChannels[key].readyState == "open") this.dataChannels[key].send(JSON.stringify(message));
        });
    }

    requestSpeech() {
        this.addStateNotification("You are requesting to speak", 30);
        this.sendToPeers({
            type: "speechRequest"
        });
    }

    assignWord(userID) {
        this.sendToPeers({
            type: "wordAssignment",
            user: userID
        });
    }

    updateUserMuteState(userID) {
        this.muteStates[userID] = !this.muteStates[userID];
        if(this.userIsInitiator || userID == activeUser) {
            if(this.muteStates[userID]) {
                this.muteButtons[userID].src = "/res/img/unmute.svg";
                this.muteButtons[userID].title = "Unmute user";
            } else {
                this.muteButtons[userID].src = "/res/img/mute.svg";
                this.muteButtons[userID].title = "Mute user";
            }
        } else {
            var div = document.createElement("div");
            var img = document.createElement("img");
            div.appendChild(img);
            if(this.muteStates[userID]) {
                img.src = "/res/img/mute.svg";
                div.appendChild(document.createTextNode(" is muted"));
                this.muteStateDisplays[userID] = div;
            } else {
                this.muteStateDisplays[userID].remove();
            }
            this.containers[userID].appendChild(div);
        }
        if(userID == activeUser) {
            this.setMuteState(this.muteStates[userID]);
        }
    }

    muteUser(userID) {
        this.updateUserMuteState(userID);
        this.sendToPeers({
            type: "muteStateChange",
            user: userID
        });
    }

    setMuteState(muted) {
        console.log("User muted? ", muted);
        this.stream.getAudioTracks()[0].enabled = !muted;
    }

    removeUser(userID) {
        this.sendToPeers({
            type: "userRemoved",
            user: userID
        });
        this.removeStream(userID, 0);
    }

    addStateNotification(notification, timeout = null) {
        this.stateDisplay.innerText = notification;
        if(timeout !== null) setTimeout(this.removeStateNotification.bind(this), timeout * 1000)
    }

    removeStateNotification() {
        this.stateDisplay.innerText = "";
    }

    updateUserVideoState(userID) {
        this.videoStates[userID] = !this.videoStates[userID];
        if(this.userIsInitiator || userID == activeUser) {
            if(this.videoStates[userID]) {
                this.videoButtons[userID].src = "/res/img/camera.svg";
                this.videoButtons[userID].title = "Enable video";
            } else {
                this.videoButtons[userID].src = "/res/img/camera_off.svg";
                this.videoButtons[userID].title = "Disable video";
            }
        } else {
            var div = document.createElement("div");
            var img = document.createElement("img");
            div.appendChild(img);
            if(this.videoStates[userID]) {
                img.src = "/res/img/camera_off.svg";
                div.appendChild(document.createTextNode(" camera is disabled"));
                this.videoStateDisplays[userID] = div;
            } else {
                this.videoStateDisplays[userID].remove();
            }
            this.containers[userID].appendChild(div);
        }
        if(userID == activeUser) {
            this.setVideoState(this.videoStates[userID]);
        }
    }

    changeVideoState(userID) {
        this.updateUserVideoState(userID);
        this.sendToPeers({
            type: "userVideoStateChange",
            user: userID
        });
    }

    setVideoState(disabled) {
        this.stream.getVideoTracks()[0].enabled = !disabled;
    }

    /*addStream(stream) {
        var video = document.createElement("video");
        video.srcObject = stream;
        this.popupNode.appendChild(video);
    }*/

}

class CallMember {

    rtc;

    constructor() {
        this.rtc = new RTCPeerConnection();
        this.rtc.setRemoteDescription(offer);
    }

    addIceCandidate(candidate) {
        this.rtc.addIceCandidate(candidate);
    }
}