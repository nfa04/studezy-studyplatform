const defaultQuestion = 'word';
const defaultAnswer = 'definition';
const questionContainer = document.getElementById("question_container");
const answerContainer = document.getElementById("answer_container");
const checkBtn = document.getElementById("check_btn");
const successesUntilComplete = 5;
var answerInput = () => document.getElementById("answer_input");
var metaData = {};
var scoreProgess;
var scores;

function getWords(scoreDelta = null) {
    new APIRequest("learn_suggest_words").sendAndFetch({
        id: getIDParam(),
        scores: JSON.stringify(scoreDelta)
    }).then(res => {

        var suggestions = JSON.parse(res);
        var scores = {};
        var i = 0;
        
        function displayQuestion() {
            answerInput().focus();
            answerInput().value = "";
            questionContainer.innerText = suggestions[i][defaultQuestion];
        }

        function checkAnswer() {
            questionContainer.replaceChildren();
            function nextQuestion() {
                window.removeEventListener("keyup", nextQuestion, {once: true});
                answerContainer.style.display = "initial";
                i++;
                displayQuestion();
                answerInput().value = "";
            }

            function endOfSuggestions() {

                function nextBlock() {
                    questionContainer.removeEventListener("click", checkAnswer);
                    window.removeEventListener("keydown", nextBlock, {once: true});
                    answerContainer.style.display = "initial";
                    getWords(scores);
                }

                questionContainer.replaceChildren();
                var div = document.createElement("div");
                var img = document.createElement("img");
                img.src = "/res/img/satisfied.svg";
                div.appendChild(img);
                questionContainer.appendChild(div);
                var message = document.createTextNode(" You're doing great, continue studying!");
                div.appendChild(message);
                var continueBtn = document.createElement("input");
                continueBtn.type = "button";
                continueBtn.value = "Continue";
                questionContainer.appendChild(continueBtn);
                window.addEventListener("keydown", nextBlock, {once: true});
                continueBtn.addEventListener("click", nextBlock, {once: true})
            }

            console.log(suggestions[i][defaultAnswer], answerInput().value);
            if(suggestions[i][defaultAnswer].toLowerCase() == answerInput().value.toLowerCase()) {
                scores[suggestions[i]['word_id']] = 1;
                updateScoreProgress(scoreProgess + (1 / (metaData['wordCount'] * successesUntilComplete) * 100));
            }
            else scores[suggestions[i]['word_id']] = 0;

            answerContainer.style.display = "none";
            var correctionDiv = document.createElement("div");
            var correctionImg = document.createElement("img");
            correctionImg.src = "/res/img/" + (scores[suggestions[i]['word_id']] ? "check" : "sad") + ".svg";
            correctionDiv.appendChild(correctionImg);
            correctionDiv.appendChild(document.createTextNode(" That was " + (scores[suggestions[i]['word_id']] ? 'correct' : 'wrong') + "!"));
            questionContainer.appendChild(correctionDiv);
            var continueDiv = document.createElement("div");
            var continueBtn = document.createElement("input");
            continueBtn.type = "button";
            continueBtn.value = "Continue";

            if(!scores[suggestions[i]['word_id']]) {
                var correctAnswer = document.createElement("div");
                correctAnswer.innerText = 'The correct answer would have been: "' + suggestions[i][defaultAnswer] + '"';
                correctionDiv.appendChild(correctAnswer);
            }
            continueDiv.appendChild(continueBtn);
            correctionDiv.appendChild(continueDiv);

            if(i < (suggestions.length - 1)) {
                window.addEventListener("keyup", nextQuestion, {once: true});
                continueBtn.addEventListener("click", nextQuestion, {once: true});
            } else {
                checkBtn.removeEventListener("click", checkAnswer);
                window.addEventListener("keyup", endOfSuggestions, {once: true});
                continueBtn.addEventListener("click", () => {
                    window.removeEventListener("keyup", endOfSuggestions, {once: true});
                    endOfSuggestions();
                }, {once: true});             
            }
        }

        checkBtn.addEventListener("click", checkAnswer);

        displayQuestion();

    });
}

function updateScoreProgress(progress) {
    scoreProgess = progress;
    console.log("New score progress: " + progress);
    var percentageContainer = document.getElementById("score_percentage_range");
    var ctx = percentageContainer.getContext("2d");
    ctx.fillStyle = "black";
    ctx.fillRect(0,0, progress / 100 * percentageContainer.width, percentageContainer.height);
    document.getElementById("score_percentage_container").innerText = Math.round(progress).toString();
}

var metaData;

new APIRequest("get_vset_metadata").sendAndFetch({
    id: getIDParam(),
    includeUserData: 1
}).then(res => {
    metaData = JSON.parse(res);
    document.getElementById("set_name_container").innerText = metaData['name'];
    updateScoreProgress(metaData['scorePercentage']);
    getWords();
});