class APIRequest {

    #method;
    #permanentParams;

    constructor(api_method) {
        this.method = api_method;
        this.permanentParams = {};
    }

    addPermanentParam(name, data) {
         this.permanentParams[name] = data;
    }

    trigger() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "/api/" + this.method);
        xhr.send();
    }

    sendAndTrigger(params = {}) {
        var p = new URLSearchParams({...this.permanentParams, ...params});
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "/api/" + this.method);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(p.toString());
    }

    fetch() {
        return new Promise(async (res, rej) => {
            var fetch = await window.fetch("/api/" + this.method);
            res(await fetch.text());
        });
    }

    sendAndFetch(params = {}) {
        var p = new URLSearchParams({...this.permanentParams, ...params});
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "/api/" + this.method);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(p.toString());
        return new Promise((resolve, reject) => {
            xhr.addEventListener("load", () => {
                if(xhr.status == 200) resolve(xhr.response);
                else reject("API error");
            })
        });
    }

    transmitBlob(params, blob, progressCallback = undefined) {
        var formData = new FormData();
        formData.append("file", blob);
        Object.keys(params).forEach(key => {
            formData.append(key, params[key])
        });
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "/api/" + this.method);
        xhr.upload.addEventListener("progress", progressCallback);
        var prom = new Promise((resolve, reject) => {
            xhr.addEventListener("loadend", () => {
                if(xhr.status == 200) resolve(xhr.response);
                else reject("API error");
            });
        });
        xhr.send(formData);
        return prom;
    }

}

function getIDParam() {
    return new URLSearchParams(window.location.search).get("i");
}

function getGETParam(param) {
    return new URLSearchParams(window.location.search).get(param);
}

class Popup {

    parentNode;

    constructor(childNode, closeOption = true, margin = "20%") {
        this.parentNode = document.createElement("div");
        this.parentNode.className = "popup";
        this.parentNode.appendChild(childNode);
        document.body.appendChild(this.parentNode);
        if(closeOption) {
            var closeBtn = document.createElement("input");
            closeBtn.type = "button";
            closeBtn.value = "Close";
            closeBtn.addEventListener("click", this.close.bind(this));
            this.parentNode.appendChild(closeBtn);
        }
        this.parentNode.style.top = margin;
        this.parentNode.style.bottom = margin;
        this.parentNode.style.left = margin;
        this.parentNode.style.right = margin;
    }

    hide() {
        this.parentNode.style.display = "none";
    }

    show() {
        this.parentNode.style.display = "initial";
    }

    close() {
        this.parentNode.remove();
    }

}

class InAppNotification {

    node;
    optionContainer;

    constructor(title, message, timeout = null) {
        this.node = document.createElement("div");
        this.node.className = "inapp_notification";
        var titleDiv = document.createElement("div");
        titleDiv.innerText = title;
        this.node.appendChild(titleDiv);
        var messageDiv = document.createElement("div");
        messageDiv.innerText = message;
        this.node.appendChild(messageDiv);

        document.body.appendChild(this.node);
        if(timeout !== null) setTimeout(() => {
            this.node.remove();
        }, timeout * 1000);
    }

    setOptions(options, timeout = null) {
        return new Promise((resolve, reject) => {
            this.optionContainer = document.createElement("div");
            options.forEach(key => {
                var btn = document.createElement("input");
                btn.type = "button";
                btn.value = key;
                btn.addEventListener("click", () => { resolve(options.indexOf(key)) });
                this.optionContainer.appendChild(btn);
                if(timeout !== null) setTimeout(reject, timeout * 1000);
            });
            this.node.appendChild(this.optionContainer);
        });
    }

    kill() {
        this.node.remove();
    }

}