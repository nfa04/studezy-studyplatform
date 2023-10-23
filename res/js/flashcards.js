const cardDiv = document.getElementById("card");
const indexContainer = document.getElementById("index_container");

function displayWord(word) {
    cardDiv.innerText = word;
}

new APIRequest("get_words_from_set").sendAndFetch({
    id: getIDParam()
}).then(res => {

    var words = JSON.parse(res);

    var frontSideOnTop = true;

    var i = 0;

    indexContainer.innerText = "1 / " + words.length.toString();

    function turn() {
        frontSideOnTop = !frontSideOnTop;
        displayWord(words[i][(frontSideOnTop ? 'word' : 'definition')]);
    }

    function left() {
        if(i > 0) {
            i--;
            displayWord(words[i][(frontSideOnTop ? 'word' : 'definition')]);
            indexContainer.innerText = (i + 1).toString() + " / " + words.length.toString();
        }
    }

    function right() {
        if(i < (words.length - 1)) {
            i++;
            displayWord(words[i][(frontSideOnTop ? 'word' : 'definition')]);
            indexContainer.innerText = (i + 1).toString() + " / " + words.length.toString();
        }
    }

    cardDiv.innerText = words[i]['word'];
    
    document.getElementById("arrow_back").addEventListener("click", left);
    document.getElementById("arrow_forward").addEventListener("click", right);

    window.addEventListener("keydown", event => {
        console.log(event.keyCode);
        switch(event.keyCode) {
            case 37:
                left();
                break;
            case 39:
                right();
                break;
            case 32:
                turn();
                break;
        }
    });

    cardDiv.addEventListener("click", turn);
    document.getElementById("turn").addEventListener("click", turn);

});