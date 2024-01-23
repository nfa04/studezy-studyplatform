class EditableQuestion {

    node;
    type;
    typeNode;
    argsNode;
    optionCount;
    id;

    constructor(htmlNode) {
        this.node = htmlNode;
        this.optionCount = htmlNode.getElementsByClassName("question_option").length;
        this.typeNode = this.node.getElementsByClassName("question_options")[0];
        this.typeNode.addEventListener("change", this.changeType.bind(this));
        this.argsNode = this.node.getElementsByClassName("question_args")[0];
        this.id = htmlNode.id.replace("question_", "");
        var btn = htmlNode.getElementsByClassName("question_addoption")[0];
        if(typeof btn !==  "undefined") btn.addEventListener("click", this.addOption.bind(this));
    }

    showArgumentInput() {
        this.argsNode.style.display = "initial";
    }

    hideArgumentInput() {
        this.argsNode.style.display = "none";
    }

    addOption() {
        var li = document.createElement("li");
        var textField = document.createElement("input");
        textField.type = "text";
        textField.name = "questions[" + this.id + "][options][" + this.optionCount + "]";
        li.appendChild(textField);
        this.argsNode.getElementsByTagName("ul")[0].appendChild(li);
        this.optionCount++;
    }

    getType() {
        return this.typeNode.value;
    }

    changeType() {
        var type = this.getType();
        this.optionCount = 0;
        this.argsNode.replaceChildren();
        if(type == "mc-checkbox" || type == "mc-radio") {
            var ul = document.createElement("ul");
            var li = document.createElement("li");
            var textField = document.createElement("input");
            textField.type = "text";
            textField.name = "questions[" + this.id + "][options][" + this.optionCount + "]";
            var btn = document.createElement("input");
            btn.type = "button";
            btn.value = "Add option";
            btn.addEventListener("click", this.addOption.bind(this));
            li.appendChild(textField);
            ul.appendChild(li);
            this.argsNode.appendChild(ul);
            this.argsNode.appendChild(btn);
            this.showArgumentInput();
            this.optionCount = 1;
        } else if(type == "range") {
            var textNodeFrom = document.createTextNode("From ");
            var inputFrom = document.createElement("input");
            inputFrom.type = "number";
            inputFrom.name = "questions[" + this.id + "][options][0]";
            var inputTo = document.createElement("input");
            inputTo.type = "number";
            inputTo.name = "questions[" + this.id + "][options][1]";
            var textNodeTo = document.createTextNode(" to ");
            this.argsNode.appendChild(textNodeFrom);
            this.argsNode.appendChild(inputFrom);
            this.argsNode.appendChild(textNodeTo);
            this.argsNode.appendChild(inputTo);
        }
        else this.hideArgumentInput();
    }
    
    changeTypeTo(type) {
        this.typeNode.value = type;
        this.changeType();
    }

}

var questions = document.getElementsByClassName("question");
for(i = 0; i < questions.length; i++) {
    new EditableQuestion(questions[i]);
}

function addQuestion() {
    var id = Math.round(Math.random() * 1000000000000);
    var questionNode = document.getElementsByClassName("question")[0].cloneNode(true);
    var inputs = questionNode.querySelectorAll("input, select, textarea");
    for(i = 0; i < inputs.length; i++) {
        inputs[i].name = inputs[i].name.replace(questionNode.id.replace("question_", ""), id.toString());
        if(inputs[i].tagName == "INPUT") inputs[i].value = "";
        else if(inputs[i].tagName == "TEXTAREA") inputs[i].innerText = "";
    }
    questionNode.id = "question_" + id;
    var controls = document.getElementById("survey_controls");
    controls.parentNode.insertBefore(questionNode, controls);
    new EditableQuestion(questionNode).changeTypeTo("text");
}