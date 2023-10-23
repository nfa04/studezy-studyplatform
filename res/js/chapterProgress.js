const progressOutput = document.getElementById("progress_output");
const progressVisualization = document.getElementById("progress_visualization");
const contentContainer = document.querySelector(".ql-editor");
const starRatingSelector = document.querySelector(".star-rating");

var readProgress = 0;
var stars = new StarRating('.star-rating');

function updateProgress(successCallback = function() {}) {
    var api = new APIRequest("set_chapter_progress");
    api.sendAndFetch({
        chid: getIDParam(),
        stars: parseInt(stars.widgets[0]['indexActive'] + 1),
        progress: Math.round(parseInt(readProgress))
    }).then((res) => {
        if(res !== null) {
            successCallback();
        }
    });
}

document.getElementById("star-rating").addEventListener("change", updateProgress.bind(this, () => {
    document.getElementById("star_rating_description").innerText = "Thanks for your feedback!";
}));

var progressCheckAPI = new APIRequest("get_chapter_progress");
var timeout;    
progressCheckAPI.sendAndFetch({
    chid: getIDParam()
}).then(res => {
    if(res !== null) {

        var data = JSON.parse(res);
        
        progressOutput.innerText = data['progress'];
        progressVisualization.value = data['progress'];
        readProgress = data['progress'];

        // Display status
        window.scrollTo({
            top: (data['progress'] / 100 * contentContainer.getBoundingClientRect().height) - contentContainer.getBoundingClientRect().top - window.scrollY,
            left: 0,
            behavior: "smooth",
        });
        starRatingSelector.value = data['stars'].toString();
        stars.rebuild();
          

        document.addEventListener("scroll", () => {
            var boundingRect = contentContainer.getBoundingClientRect();

            readProgress = Math.max(0, Math.min(100, -Math.round((boundingRect.top - window.scrollY) / (boundingRect.height - boundingRect.top) * 100)));
            
            progressOutput.innerText = readProgress;
            progressVisualization.value = readProgress;

            // Make sure status is not being updated if the user is still scrolling
            clearTimeout(timeout);
            timeout = setTimeout(updateProgress, 1000);
        });
    }
});