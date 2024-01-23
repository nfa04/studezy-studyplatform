async function submitAssignment() {
    var fileInputs = document.getElementsByClassName("fileInput");
    for(i = 0; i < fileInputs.length; i++) {
        var fileName = fileInputs[i].value.replace("C:\\fakepath\\", "");
        document.getElementById("processing_file_name").innerText = fileName + ": ";
        var output = await processFile(fileInputs[i].files[0], document.getElementById("progress"), document.getElementById("percent"));
        var api = new APIRequest("assignment_submit");
        api.transmitBlob({
            "assignment_id": getIDParam(),
            "file_name": fileName
        }, output, (e) => {
            if(e.lengthComputable) {
                var percent = Math.round(e.loaded / output.size * 100);
                document.getElementById("percent").innerText = percent + "%";
                document.getElementById("progress").innerText = "Uploading...";
            }
        }).then(() => {
            window.location.href = "submissionSuccess";
        }).catch(() => {
            document.getElementById("progress").innerText = "Error!";
        });
    }
}
function addFile(btn) {
    console.log(btn);
    var container = document.getElementById("file_input_container");
    var input = document.createElement("input");
    input.type = "file";
    input.className = "fileInput";
    var div = document.createElement("div");
    div.appendChild(input);
    container.insertBefore(div, btn);
}