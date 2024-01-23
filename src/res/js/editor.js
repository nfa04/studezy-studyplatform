var api = new APIRequest("get_doc_token");
var quill;
var editingIndicators = {};

const BlockEmbed = Quill.import("blots/block/embed");
const Embed = Quill.import("blots/embed");

var assetAPI = new APIRequest("get_asset_root");
var ASSET_ROOT_LOCATION;
assetAPI.fetch().then(res => {
    ASSET_ROOT_LOCATION = res;
});

class StudezyItemBlot extends Embed {

    static create(iconLink) {
        let node = super.create();

        var toolNode = document.createElement("div");

        let timer;

        node.addEventListener("mouseover", () => {

            toolNode.style.display = "flex";

            clearTimeout(timer);

            timer = setTimeout(() => {
                toolNode.style.display = "none";
            }, 2000);

        });

        var tools = document.createElement("span");
        var remove = document.createElement("img");
        remove.src = "/res/img/delete.svg";
        remove.addEventListener("click", () => {
            console.log(quill.deleteText(quill.getIndex(Quill.find(node)), 1, Quill.sources.USER));
        });

        toolNode.appendChild(remove);
        toolNode.className = "editor_inline_tools";
        toolNode.appendChild(tools);
        node.appendChild(toolNode);

        var icon = document.createElement("img");
        icon.src = "/res/img/" + iconLink + ".svg";
        icon.className = "editor_item_icon";
        node.appendChild(icon);

        node.className = "studezy_media_item";

        return node;
    }

    static formats(node) {
        // We still need to report unregistered embed formats
        let format = {};
        if (node.hasAttribute('height')) {
          format.height = node.getAttribute('height');
        }
        if (node.hasAttribute('width')) {
          format.width = node.getAttribute('width');
        }
        return format;
      }

      format(name, value) {
        // Handle unregistered embed formats
        if (name === 'height' || name === 'width') {
          if (value) {
            this.domNode.setAttribute(name, value);
          } else {
            this.domNode.removeAttribute(name, value);
          }
        } else {
          super.format(name, value);
        }
      }
}

class AudioBlot extends StudezyItemBlot {
    static create(url) {
      let node = super.create();
      node.setAttribute('src', url);
      // Set non-format related attributes with static values
      node.setAttribute('frameborder', '0');
      node.setAttribute('allowfullscreen', false);
  
      return node;
    }
  
    static value(node) {
      return node.getAttribute('src');
    }
  }

AudioBlot.blotName = 'audio';
AudioBlot.tagName = 'iframe';

class SurveyBlot extends StudezyItemBlot {

    surveyID;

    static create(surveyID) {

        let node = super.create("survey");

        node.className += " ql-survey";
        node.contentEditable = false;

        var title = document.createElement("h3");
        var description = document.createElement("div");
        var user = document.createElement("div");

        new APIRequest("get_survey_metadata").sendAndFetch({
            id: surveyID
        }).then(json => {
            var data = JSON.parse(json);
            title.innerText = data['name']
            description.innerText = data['description'];
            user.innerText = "by " + data['owner'];
        });

        this.surveyID = surveyID;
        
        node.appendChild(title);
        node.appendChild(description);
        node.appendChild(user);

        title.addEventListener("click", () => {
            window.location.href = "/surveys/view?i=" + surveyID;
        })

        return node;
    }

    static value() {
        return this.surveyID;
    }

}

SurveyBlot.tagName = 'div';
SurveyBlot.blotName = 'survey';

Quill.register(SurveyBlot);

class AssignmentBlot extends StudezyItemBlot {

    assignmentID;

    static create(assignmentID) {

        let node = super.create("assignment");

        node.className += " ql-assignment";
        node.contentEditable = false;

        var title = document.createElement("h3");
        var description = document.createElement("div");
        var user = document.createElement("div");

        new APIRequest("get_assignment_metadata").sendAndFetch({
            id: assignmentID
        }).then(json => {
            var data = JSON.parse(json);
            title.innerText = data['name']
            description.innerText = data['description'];
            user.innerText = "by " + data['owner'];
        });

        this.assignmentID = assignmentID;
        
        node.appendChild(title);
        node.appendChild(description);
        node.appendChild(user);

        title.addEventListener("click", () => {
            window.location.href = "/assignments/view?i=" + assignmentID;
        })

        return node;
    }

    static value() {
        return this.assignmentID;
    }
}

AssignmentBlot.tagName = 'div';
AssignmentBlot.blotName = 'assignment';

Quill.register(AssignmentBlot);

class AnnouncementBlot extends StudezyItemBlot {

    announcementID;

    static create(announcementID) {

        let node = super.create("announcement");

        node.className += " ql-announcement";
        node.contentEditable = false;

        var title = document.createElement("h3");
        var description = document.createElement("div");
        var user = document.createElement("div");

        new APIRequest("get_announcement_metadata").sendAndFetch({
            id: announcementID
        }).then(json => {
            var data = JSON.parse(json);
            title.innerText = data['name']
            description.innerText = data['description'];
            user.innerText = "by " + data['owner'];
        });

        this.announcementID = announcementID;
        
        node.appendChild(title);
        node.appendChild(description);
        node.appendChild(user);

        title.addEventListener("click", () => {
            window.location.href = "/announcements/view?i=" + announcementID;
        })

        return node;
    }

    static value() {
        return this.announcementID;
    }

}

AnnouncementBlot.tagName = 'div';
AnnouncementBlot.blotName = 'announcement';

Quill.register(AnnouncementBlot);

class ChatBlot extends StudezyItemBlot {
    chatID;

    static create(chatID) {
        let node = super.create("chat");

        this.chatID = chatID;

        var title = document.createElement("h3");
        var memberCount = document.createElement("div");
        
        new APIRequest("get_chat_metadata").sendAndFetch({
            id: chatID
        }).then(res => {
            var data = JSON.parse(res);
            title.innerText = data['name'];
            memberCount.innerText = data['memberCount'].toString() + " members";
        });

        node.appendChild(title);
        node.appendChild(memberCount);

        return node;
    }

    static value() {
        return this.chatID;
    }
}

ChatBlot.tagName = 'div';
ChatBlot.blotName = 'chat';

Quill.register(ChatBlot);

api.fetch().then(data => {
    var userData = JSON.parse(data);
    var socket = io(userData['serverLocation'], {
        auth:  {
            fileID: getIDParam(),
            fileType: getGETParam("type"),
            courseID: getGETParam("cid"),
            token: userData['token'],
            userID: userData['userID'],
            userName: userData['userName']
        }
    });

    var icons = Quill.import("ui/icons");
    icons["undo"] = `<svg viewbox="0 0 18 18">
    <polygon class="ql-fill ql-stroke" points="6 10 4 12 2 10 6 10"></polygon>
    <path class="ql-stroke" d="M8.09,13.91A4.6,4.6,0,0,0,9,14,5,5,0,1,0,4,9"></path>
  </svg>`;
    icons["redo"] = `<svg viewbox="0 0 18 18">
    <polygon class="ql-fill ql-stroke" points="12 10 14 12 16 10 12 10"></polygon>
    <path class="ql-stroke" d="M9.91,13.91A4.6,4.6,0,0,1,9,14a5,5,0,1,1,5-5"></path>
  </svg>`;
    icons['audio'] = '<svg xmlns="http://www.w3.org/2000/svg" height="48" width="48" viewbox="35 0 48 48"><path d="M21.35 38.4q2.05 0 3.5-1.425Q26.3 35.55 26.3 33.5V23.4h5.8v-3h-7.8v9.35q-.55-.45-1.325-.7-.775-.25-1.625-.25-1.95 0-3.3 1.375Q16.7 31.55 16.7 33.5q0 2 1.35 3.45 1.35 1.45 3.3 1.45ZM11 44q-1.2 0-2.1-.9Q8 42.2 8 41V7q0-1.2.9-2.1Q9.8 4 11 4h18.05L40 14.95V41q0 1.2-.9 2.1-.9.9-2.1.9Zm16.55-27.7V7H11v34h26V16.3ZM11 7v9.3V7v34V7Z"/></svg>';
    icons['survey'] = '<svg xmlns="http://www.w3.org/2000/svg" height="48" width="48" viewbox="0 -2.5 48 48"><path d="M27.15 31q.85 0 1.45-.6t.6-1.45q0-.85-.6-1.45t-1.45-.6q-.85 0-1.45.6t-.6 1.45q0 .85.6 1.45t1.45.6Zm-1.25-6.3h2.35q.1-1.45.425-2.15.325-.7 1.625-1.95 1.35-1.3 1.875-2.275.525-.975.525-2.275 0-2.3-1.575-3.75Q29.55 10.85 27 10.85q-1.9 0-3.4 1.025t-2.2 2.875l2.25.95q.55-1.25 1.375-1.9.825-.65 1.975-.65 1.5 0 2.425.85.925.85.925 2.15 0 1-.45 1.75t-1.6 1.6q-1.6 1.45-2 2.325-.4.875-.4 2.875ZM13 38q-1.2 0-2.1-.9-.9-.9-.9-2.1V7q0-1.2.9-2.1.9-.9 2.1-.9h28q1.2 0 2.1.9.9.9.9 2.1v28q0 1.2-.9 2.1-.9.9-2.1.9Zm0-3h28V7H13v28Zm-6 9q-1.2 0-2.1-.9Q4 42.2 4 41V10h3v31h31v3Zm6-37v28V7Z"/></svg>';
    icons['assignment'] = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><svg   height="48"   width="48" viewbox="0 4 48 48"   version="1.1"   id="svg4"   sodipodi:docname="assignment_FILL0_wght400_GRAD0_opsz48.svg"   inkscape:version="1.1.2 (0a00cf5339, 2022-02-04)"   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"   xmlns="http://www.w3.org/2000/svg"   xmlns:svg="http://www.w3.org/2000/svg">  <defs     id="defs8" />  <sodipodi:namedview     id="namedview6"     pagecolor="#ffffff"     bordercolor="#999999"     borderopacity="1"     inkscape:pageshadow="0"     inkscape:pageopacity="0"     inkscape:pagecheckerboard="0"     showgrid="false"     inkscape:zoom="16.291667"     inkscape:cx="24"     inkscape:cy="14.148338"     inkscape:window-width="1846"     inkscape:window-height="1016"     inkscape:window-x="0"     inkscape:window-y="0"     inkscape:window-maximized="1"     inkscape:current-layer="svg4" />  <path     d="M 9,48 Q 7.75,48 6.875,47.125 6,46.25 6,45 V 15 Q 6,13.75 6.875,12.875 7.75,12 9,12 H 19.25 Q 19.5,10.25 20.85,9.125 22.2,8 24,8 25.8,8 27.15,9.125 28.5,10.25 28.75,12 H 39 q 1.25,0 2.125,0.875 Q 42,13.75 42,15 v 30 q 0,1.25 -0.875,2.125 Q 40.25,48 39,48 Z M 9,45 H 39 V 15 H 9 Z m 5,-5 H 27.65 V 37 H 14 Z m 0,-8.5 h 20 v -3 H 14 Z M 14,23 H 34 V 20 H 14 Z m 10,-8.85 q 0.7,0 1.225,-0.525 Q 25.75,13.1 25.75,12.4 25.75,11.7 25.225,11.175 24.7,10.65 24,10.65 q -0.7,0 -1.225,0.525 -0.525,0.525 -0.525,1.225 0,0.7 0.525,1.225 Q 23.3,14.15 24,14.15 Z M 9,45 V 15 Z"     id="path2" /></svg>';
    icons['announcement'] = '<svg xmlns="http://www.w3.org/2000/svg" height="48" width="48" viewbox="4 4 44 44"><path d="M36.5 25.5v-3H44v3ZM39 40l-6.05-4.5 1.8-2.4 6.05 4.5Zm-4.1-25.15-1.8-2.4L39 8l1.8 2.4ZM10.5 38v-8H7q-1.25 0-2.125-.875T4 27v-6q0-1.25.875-2.125T7 18h9l10-6v24l-10-6h-2.5v8ZM15 24Zm13 6.7V17.3q1.35 1.2 2.175 2.925Q31 21.95 31 24t-.825 3.775Q29.35 29.5 28 30.7ZM7 21v6h9.8l6.2 3.7V17.3L16.8 21Z"/></svg>';
    icons['chat'] = '<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 48 48" height="48" width="48"><path d="M12 28.05h15.65v-3H12Zm0-6.5h24v-3H12Zm0-6.5h24v-3H12ZM4 44V7q0-1.15.9-2.075Q5.8 4 7 4h34q1.15 0 2.075.925Q44 5.85 44 7v26q0 1.15-.925 2.075Q42.15 36 41 36H12Zm3-7.25L10.75 33H41V7H7ZM7 7v29.75Z"/></svg>';

    let Font = Quill.import('formats/font');
    Font.whitelist.push('times-new-roman', 'arial', 'courier-new', 'georgia', 'trebuchet-ms', 'verdana', 'roboto', 'oswald', 'raleway', 'cormorant', 'saira', 'jura', 'overpass', 'fredoka', 'hahmlet');
    Quill.register(Font, true);

    quill = new Quill('#content', {
        modules: {
           toolbar: {
            container: [
                ['undo', 'redo'],
                [{ header: []}],
                [{size: []}],
                [{font: ['sans-serif', 'serif', 'monospace', 'arial', 'times-new-roman', 'courier-new', 'georgia', 'trebuchet-ms', 'verdana', 'roboto', 'oswald', 'raleway', 'cormorant', 'saira', 'jura', 'overpass', 'fredoka', 'hahmlet']}],
                [{ 'indent': '-1'}, { 'indent': '+1' }, { 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                ['image', 'video', 'audio', 'link'],
                [{ 'color': [] }, { 'background': [] }],
                ['code-block'],
                ['survey', 'assignment', 'announcement', 'chat']
            ], handlers: {
                'undo': () => {
                    quill.history.undo();
                },
                'redo': () => {
                    quill.history.redo();
                },
                'audio': audioHandler,
                'survey': surveyHandler,
                'assignment': assignmentHandler,
                'announcement': announcementHandler,
                'chat': chatHandler
            }
        }, 
        history: {
            userOnly: true,
            delay: 500
        },
        syntax: true
        },
        placeholder: 'No contents yet... Change it now!',
        theme: 'snow'  // or 'bubble'
    });

    Quill.register(AudioBlot);


    var toolbar = quill.getModule("toolbar");
    toolbar.addHandler("image", imgHandler);
    toolbar.addHandler("video", videoHandler);
    toolbar.addHandler("audio", audioHandler);

    function imgHandler() {
        var node = document.createElement("div");
        var popup = new Popup(node);
        var nodeTitle = document.createElement("h2");
        nodeTitle.innerText = "Insert an image";
        node.appendChild(nodeTitle);
        var selectorNode = document.createElement("div");
        var fileSelector = new FileSelector(selectorNode, ["image/jpg", "image/jpeg", "image/png", "image/gif"]);
        fileSelector.assetBtn.addEventListener("click", () => {
            fileSelector.chooseFromAssets().then((asset) => {
                quill.insertEmbed(quill.getSelection(), "image", ASSET_ROOT_LOCATION + asset['name'] + "." + mime2ext(asset['type']), Quill.sources.USER);
                popup.close();
            });
        });
        fileSelector.localBtn.addEventListener("click", () =>  {
            fileSelector.chooseFromLocalFS().then(file => {
                processFile(file).then(blob => {
                    var reader = new FileReader();
                    reader.readAsDataURL(blob); 
                    reader.onloadend = function() {
                        base64data = reader.result;                
                        quill.insertEmbed(quill.getSelection(), "image", base64data, Quill.sources.USER);
                        popup.close();
                    }
                });
            });
        });
        node.appendChild(selectorNode);
    }

    function videoHandler() {
        var node = document.createElement("div");
        var popup = new Popup(node);
        var nodeTitle = document.createElement("h2");
        nodeTitle.innerText = "Insert video";
        node.appendChild(nodeTitle);
        var selectNode = document.createElement("div");
        var fileSelector = new FileSelector(selectNode, ["video/mp4"]);
        fileSelector.localBtn.addEventListener("click", () => {
            fileSelector.selectorNode.replaceChildren();
            fileSelector.selectorNode.innerText = "Not available at the moment due to browser restrictions ):";
        });
        fileSelector.assetBtn.addEventListener("click", () => {
            fileSelector.chooseFromAssets().then(asset => {
                quill.insertEmbed(quill.getSelection(), "video", ASSET_ROOT_LOCATION + asset['name'] + "." + mime2ext(asset['type']), Quill.sources.USER);
                popup.close();
            });
        });
        node.appendChild(selectNode);
    }

    function audioHandler() {
        var node = document.createElement("div");
        var popup = new Popup(node);
        var nodeTitle = document.createElement("h2");
        nodeTitle.innerText = "Insert audio";
        node.appendChild(nodeTitle);
        var selectNode = document.createElement("div");
        var fileSelector = new FileSelector(selectNode, ["audio/ogg"]);
        fileSelector.localBtn.addEventListener("click", () => {
            fileSelector.selectorNode.replaceChildren();
            fileSelector.selectorNode.innerText = "Not available at the moment due to browser restrictions ):";
        });
        fileSelector.assetBtn.addEventListener("click", () => {
            fileSelector.chooseFromAssets().then(asset => {
                quill.insertEmbed(quill.getSelection(), "audio", ASSET_ROOT_LOCATION + asset['name'] + "." + mime2ext(asset['type']), Quill.sources.USER);
                popup.close();
            });
        });
        node.appendChild(selectNode);
    }

    function surveyHandler() {
        var selection = quill.getSelection();
        var node = document.createElement("div");
        var popup = new Popup(node);
        var title = document.createElement("h2");
        title.innerText = "Insert a survey";
        node.appendChild(title);
        var selectorNode = document.createElement("div");
        node.appendChild(selectorNode);
        var selector = new SurveySelector(selectorNode);
        selector.ownSurveyBtn.addEventListener("click", () => {
            selector.chooseFromOwnSurveys().then(surveyID => {
                quill.insertEmbed(selection, "survey", surveyID, Quill.sources.USER);
                popup.close();
            });
        });
        selector.searchSurveyBtn.addEventListener("click", () => {
            selector.chooseFromAll().then(surveyID => {
                quill.insertEmbed(quill.getSelection(), "survey", surveyID, Quill.sources.USER);
                popup.close();
            });
        });
        //quill.insertEmbed(quill.getSelection(), "survey", "1", Quill.sources.USER);
    }

    function assignmentHandler() {
        var selection = quill.getSelection();
        function insert(assignmentID) {
            quill.insertEmbed(selection, "assignment", assignmentID, Quill.sources.USER);
        }
        var node = document.createElement("div");
        var popup = new Popup(node);
        var title = document.createElement("h2");
        title.innerText = "Insert an assignment";
        node.appendChild(title);
        var selectorNode = document.createElement("div");
        var selector = new AssignmentSelector(selectorNode, getGETParam("cid"));
        selector.courseBtn.addEventListener("click", () => {
            selector.chooseFromCourse().then(insert);
        });
        selector.ownBtn.addEventListener("click", () => {
            selector.chooseFromUser().then(insert);
        });
        selector.allBtn.addEventListener("click", () => {
            selector.chooseFromAll().then(insert);
        });
        node.appendChild(selectorNode);
    }

    function announcementHandler() {
        var selection = quill.getSelection();
        function insert(announcementID) {
            quill.insertEmbed(selection, "announcement", announcementID, Quill.sources.USER);
        }
        var node = document.createElement("div");
        var popup = new Popup(node);
        var title = document.createElement("h2");
        title.innerText = "Insert an announcement";
        node.appendChild(title);
        var selectorNode = document.createElement("div");
        var selector = new AnnouncementSelector(selectorNode, getGETParam("cid"));
        selector.courseBtn.addEventListener("click", () => {
            selector.chooseFromCourse().then(id => {
                insert(id);
                popup.close();
            });
        });
        selector.ownBtn.addEventListener("click", () => {
            selector.chooseFromUser().then(id => {
                insert(id);
                popup.close();
            });
        });
        selector.allBtn.addEventListener("click", () => {
            selector.chooseFromAll().then(id => {
                insert(id);
                popup.close();
            });
        });
        node.appendChild(selectorNode);
    }

    function chatHandler() {
        var selection = quill.getSelection();
        var node = document.createElement("div");
        var popup = new Popup(node);
        var title = document.createElement("h2");
        title.innerText = "Insert a link to a chat";
        node.appendChild(title);
        var selectorNode = document.createElement("div");
        var selector = new ChatSelector(selectorNode);
        selector.choose().then(id => {
            quill.insertEmbed(selection, "chat", id, Quill.sources.USER);
        });
        node.appendChild(selectorNode);
    }

    socket.on("init", delta => {
        quill.setContents(delta);
        quill.on('text-change', (delta, oldDelta, c) => {
            socket.emit("delta", {
                userID: userData['userID'],
                userName: userData['userName'],
                delta: delta
            });
        });
    });

    var editingIndicatorTimeouts = {};
    socket.on("delta", data => {
        var delta = data.delta;
        quill.updateContents(delta, 'silent');
        var containerBounds = document.getElementById("content").getBoundingClientRect();
        var bounds = quill.getBounds(delta.ops[0]['retain'] + (typeof delta.ops[1]['insert'] == 'undefined' ? 0 : delta.ops[1]['insert'].length));
        if(typeof editingIndicators[data['userID']] == 'undefined') editingIndicators[data['userID']] = document.createElement("span");
        var indicator = editingIndicators[data['userID']];
        indicator.innerText = data.userName;
        indicator.className = "editing_indicator";
        indicator.style.top = Math.round(bounds.top + containerBounds.top - 15).toString() + "px";
        indicator.style.left = Math.round(bounds.left + containerBounds.left - 10).toString() + "px";
        indicator.style.visibility = "visible";
        document.body.appendChild(indicator);
        clearTimeout(editingIndicatorTimeouts[data.userID]);
        editingIndicatorTimeouts[data.userID] = setTimeout(() => indicator.style.visibility = "hidden", 5000);
    });

    const pub_btn = document.getElementById("publish_btn");
    if(pub_btn !== null) pub_btn.addEventListener("click", () => { // In document editing mode (non-chapters) this button doesn't exist, so we need to make sure the script doesn't crash then
        if(window.confirm("Would you like to publish the changes?")) socket.emit("publish");
    });

    const share_btn = document.getElementById("share");
    if(share_btn !== null) share_btn.addEventListener("click", () => {
        
        var node = document.createElement("div");
        var popup = new Popup(node);
        var title = document.createElement("h2");
        title.innerText = "Sharing preferences";
        node.appendChild(title);
        var form = document.createElement("form");
        form.className = "sharing_options";
        node.appendChild(form);

        new APIRequest("get_document_metadata").sendAndFetch({
            id: getIDParam()
        }).then(res => {
            var metaData = JSON.parse(res);

            function updateSharingStatus() {
                metaData['private'] = !metaData['private'];
                new APIRequest("modify_document_sharing_preferences").sendAndTrigger({
                    id: getIDParam(),
                    action: 'state-change',
                    private: metaData['private']
                });
            }

            // Private option will only share the document with specified users

            var privateOption = document.createElement("input");
            privateOption.type = "radio";
            privateOption.name = "sharing_option";
            privateOption.id = "private_option";
            if(metaData['private']) privateOption.checked = true;
            form.appendChild(privateOption);

            privateOption.addEventListener("click", updateSharingStatus);

            var privateLabel = document.createElement("label");
            privateLabel.for = "private_option";
            privateLabel.innerText = "Private";
            form.appendChild(privateLabel);
            var sharedWithDiv = document.createElement("div");



            metaData['co-authors'].forEach(author => {
                var userNode = document.createElement("div");
                var img = document.createElement("img");
                img.src = '/res/img/' + (author['write_access']  ? 'edit' : 'no_write_access') + '.svg';
                img.addEventListener("click", () => {
                    author['write_access'] = !author['write_access'];
                    img.src = '/res/img/' + (author['write_access'] ? 'edit' : 'no_write_access') + '.svg';
                    new APIRequest("modify_document_sharing_preferences").sendAndTrigger({
                        id: getIDParam(),
                        action: 'update',
                        user: author['id'],
                        writeAccess: author['write_access']
                    });
                });
                userNode.appendChild(img);
                var removeImg = document.createElement("img");
                removeImg.src = "/res/img/delete.svg";
                removeImg.addEventListener("click", () => {
                    userNode.remove();
                    new APIRequest("modify_document_sharing_preferences").sendAndTrigger({
                        id: getIDParam(),
                        action: 'remove',
                        user: author['id']
                    });
                });
                userNode.appendChild(removeImg);
                userNode.appendChild(document.createTextNode(" " + author['name']));
                sharedWithDiv.appendChild(userNode);
            });
            var addDiv = document.createElement("div");
            var addBtn = document.createElement("input");
            addBtn.type = "button";
            addBtn.value = "Add user";

            // Open a new popup to pick a user
            addBtn.addEventListener("click", () => {

                var pAddNode = document.createElement("div");
                var pAddPopup = new Popup(pAddNode);

                function addUser(user) {
                    new APIRequest("modify_document_sharing_preferences").sendAndTrigger({
                        id: getIDParam(),
                        action: 'add',
                        'user': user['id'],
                        writeAccess: writeCheckbox.checked
                    });
                    pAddPopup.close();
                }

                // close the old popup in the back
                popup.close();

                var pAddTitle = document.createElement("h2");
                pAddTitle.innerText = "Share this document";
                pAddNode.appendChild(pAddTitle);
                

                // Add a user picker
                var selectorNode = document.createElement("div");
                var userSelector = new UserSelector(selectorNode);
                pAddNode.appendChild(selectorNode);

                userSelector.userBtn.addEventListener("click", () => {
                    userSelector.chooseFromAll().then(addUser);
                });

                userSelector.contactBtn.addEventListener("click", () => {
                    userSelector.chooseFromContacts().then(addUser);
                });

                var writeCheckbox = document.createElement("input");
                writeCheckbox.type = "checkbox";
                writeCheckbox.id = "write_checkbox"
                pAddNode.appendChild(writeCheckbox);
                var writeLabel = document.createElement("label");
                writeLabel.for = "write_checkbox";
                writeLabel.innerText = "Allow write access";
                pAddNode.appendChild(writeLabel);

            });

            addDiv.appendChild(addBtn);
            sharedWithDiv.appendChild(addDiv);

            form.appendChild(sharedWithDiv);

            form.appendChild(document.createElement("hr"));

            var publicOption = document.createElement("input");
            publicOption.type = "radio";
            publicOption.name = "sharing_option"
            publicOption.id = "public_option";
            if(!metaData['private']) publicOption.checked = true;
            publicOption.addEventListener("click", updateSharingStatus);
            form.appendChild(publicOption);
            var publicLabel = document.createElement("label");
            publicLabel.for = "public_option";
            publicLabel.innerText = "Public";
            form.appendChild(publicLabel);

            });

    });

    var chapterNameInput = document.getElementById("chapter_name");
    chapterNameInput.addEventListener("focusout", () => {
        socket.emit("rename", chapterNameInput.value);
    });

});