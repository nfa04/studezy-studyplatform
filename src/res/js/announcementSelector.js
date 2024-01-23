class AnnouncementSelector {
    
    node;
    courseID;
    courseBtn;
    ownBtn;
    allBtn;
    optionsNode;
    controlsNode;

    constructor(node, courseID = null) {

        node.className = "studezy_selector";

        this.node = node;
        this.courseID = courseID;

        var btnNode = document.createElement("div");
        btnNode.className = "studezy_selector_btns";

        // Add a course button if this selector is used in a course context
        if(courseID !== null) {
            this.courseBtn = document.createElement("input");
            this.courseBtn.type = "button";
            this.courseBtn.value = "From this course";
            btnNode.appendChild(this.courseBtn);
        }

        this.ownBtn = document.createElement("input");
        this.ownBtn.type = "button";
        this.ownBtn.value = "From my announcements";
        btnNode.appendChild(this.ownBtn);

        this.allBtn = document.createElement("input");
        this.allBtn.type = "button";
        this.allBtn.value = "From all announcements";
        btnNode.appendChild(this.allBtn);

        node.appendChild(btnNode);

        this.controlsNode = document.createElement("div");
        node.appendChild(this.controlsNode);

        this.optionsNode = document.createElement("div");
        node.appendChild(this.optionsNode);

    }

    displayAnnouncements(announcements, resolveCallback) {
        announcements.forEach(announcement => {
            var div = document.createElement("div");
            div.innerText = announcement['title'];
            this.optionsNode.appendChild(div);
            div.addEventListener("click", () => resolveCallback(announcement['id']));
        });
    }

    chooseFromCourse() {
        this.optionsNode.replaceChildren();
        this.controlsNode.replaceChildren();
        return new Promise((resolve, reject) => {
            new APIRequest("get_announcements_in_course").sendAndFetch({
                id: this.courseID
            }).then(res => {
                this.displayAnnouncements(JSON.parse(res), resolve);
            });
        });
    }

    chooseFromUser() {
        this.optionsNode.replaceChildren();
        this.controlsNode.replaceChildren();
        return new Promise((resolve, reject) => {
            new APIRequest("get_announcements_by_user").fetch().then(res => {
                this.displayAnnouncements(JSON.parse(res), resolve);
            });
        });
    }

    chooseFromAll() {
        this.optionsNode.replaceChildren();
        this.controlsNode.replaceChildren();
        return new Promise((resolve, reject) => {
            var searchInput = document.createElement("input");
            searchInput.type = "text";
            this.controlsNode.appendChild(searchInput);
            var searchBtn = document.createElement("input");
            searchBtn.type = "button";
            searchBtn.value = "Search";
            searchBtn.addEventListener("click", () => {
                new APIRequest("suggest_announcements_by_name").sendAndFetch({
                    name: searchInput.value
                }).then(res => {
                    this.displayAnnouncements(JSON.parse(res), resolve);
                });
            });
            this.controlsNode.appendChild(searchBtn);
        });
    }



}