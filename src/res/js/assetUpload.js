async function sendAsset() {
    const asset = document.getElementById("asset").files[0];
    const nameInput = document.getElementById("name");
    const progress_label = document.getElementById("progress_label");
    const progress_percent = document.getElementById("progess_percent");
    var output = await processFile(asset, progress_label, progress_percent);
    var api = new APIRequest("store_asset");
    api.transmitBlob({cid: getIDParam(), 'name': nameInput.value}, output, (e) => {
        if(e.lengthComputable) {
            var percent = e.loaded / output.size * 100;
            document.getElementById("progress_bar").value = percent;
            document.getElementById("progess_percent").innerHTML = percent + "%";
        }
    }).then(() => {
        window.location.href = "editContents?i=" + getIDParam();
    }).catch(() => {
        progress_label.innerText = "FAILED!";
    });
}