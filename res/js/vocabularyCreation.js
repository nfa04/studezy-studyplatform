const vocabularyContainer = document.getElementById("vocabulary_container");
var addCounter = 0;

function addWord() {
    var container = document.createElement("div");
    container.className = "word_container";

    var word = document.createElement("input");
    word.type = "text";
    word.placeholder = "Word";
    word.name = "v[new][" + addCounter + "][word]";

    var definition = document.createElement("input");
    definition.type = "text";
    definition.placeholder = "Definition";
    definition.name = "v[new][" + addCounter + "][definition]";

    container.appendChild(word);
    container.appendChild(definition);
    vocabularyContainer.insertBefore(container, document.getElementById("add_container"));

    addCounter++;
}