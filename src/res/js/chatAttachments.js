var fs = new FileSelector(document.getElementById("fInput_container"));
fs.assetBtn.addEventListener("click", () => {
    fs.chooseFromAssets().then((fileRepresentation) => {
        var symApi = new APIRequest("chat_file_from_asset");
        symApi.sendAndFetch({
            "assetID": fileRepresentation.name
        }).then(res => {
            var data = JSON.parse(res);
            window.location.href = "chat?scf=" + data['fileID'] + "&ott=" + data['ott'] + "&chid=" + getIDParam() + "&t=" + getConvertedType(fileRepresentation.type);
        });
    });
});
fs.localBtn.addEventListener("click", () => {
    fs.chooseFromLocalFS().then((file) => {
        processFile(file, fs.progressLabel, fs.progessPercent).then((blob) => {
            var fileAPI = new APIRequest("store_chat_file");
            fileAPI.transmitBlob({
                chatID: getIDParam()
            }, blob).then((res) => {
                var data = JSON.parse(res);
                //window.location.href = "chat?scf=" + data['fileID'] + "&ott=" + data['ott'] + "&chid=" + getIDParam() + "&t=" + getConvertedType(file.type);
            });
        });
    });
});