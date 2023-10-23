class SurveySelector {

    node;
    ownSurveyBtn;
    searchSurveyBtn;
    optionsNode;
    controlsNode;

    constructor(node) {

        node.className = "studezy_selector";

        this.node = node;

        var btnNode = document.createElement("div");
        btnNode.className = "studezy_selector_btns";

        this.ownSurveyBtn = document.createElement("input");
        this.ownSurveyBtn.type = "button";
        this.ownSurveyBtn.value = "From own surveys";

        this.searchSurveyBtn = document.createElement("input");
        this.searchSurveyBtn.type = "button";
        this.searchSurveyBtn.value = "From all surveys";

        btnNode.appendChild(this.ownSurveyBtn);
        btnNode.appendChild(this.searchSurveyBtn);
        node.appendChild(btnNode);

        this.controlsNode = document.createElement("div");
        node.appendChild(this.controlsNode);

        this.optionsNode = document.createElement("div");
        node.appendChild(this.optionsNode);
    }

    displaySurveys(surveys, resolveCallback) {
        surveys.forEach(survey => {
            var a = document.createElement("a");
            a.href = "javascript:;";
            a.innerText = survey['title'];
            a.addEventListener("click", () => {
                resolveCallback(survey['id']);
            });
            this.optionsNode.appendChild(a);
        });
    }

    chooseFromOwnSurveys() {
        this.optionsNode.replaceChildren();
        this.controlsNode.replaceChildren();
        return new Promise((resolve, reject) => {
            new APIRequest("get_own_surveys").fetch().then(res => {
                var surveys = JSON.parse(res);
                this.displaySurveys(surveys, resolve);
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
                new APIRequest("suggest_surveys_by_name").sendAndFetch({
                    'name': searchInput.value
                }).then(res => {
                    var surveys = JSON.parse(res);
                    this.displaySurveys(surveys, resolve);
                });
            });
            this.controlsNode.appendChild(searchBtn);
        });
    }

}