const mimeTypes = {
    "video": [
        "video/mp4",
        "video/quicktime",
        "video/webm",
        "video/x-msvideo"
    ],
    "audio": [
        "audio/mpeg",
        "audio/ogg",
        "video/ogg",
        "audio/webm"
    ],
    "image": [
        "image/png",
        "image/jpeg",
        "image/jpg"
    ]
}
function processFile(asset, progress_label, progress_percent) {
    return new Promise((resolve, reject) => {
        var fileReader = new FileReader();
        fileReader.addEventListener("loadend", async (e) => {
            console.log("Mime: " + asset.type);
            if(mimeTypes["image"].includes(asset.type)) {
                var canvas = document.createElement("canvas");
                var img = new Image();
                img.src = fileReader.result;
                img.addEventListener("load", () => {
                    canvas.width = Math.min(img.naturalWidth, 1000);
                    canvas.height = img.naturalHeight * (canvas.width / img.naturalWidth);
                    canvas.getContext("2d").drawImage(img, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob((blob) => {
                        resolve(blob);
                    }, "image/jpeg", 0.6);
                });
            }
            else if(mimeTypes['video'].includes(asset.type) || mimeTypes['audio'].includes(asset.type)) {
                console.log("Non-image detected");
                /*const progress_label = document.getElementById("progress_label");
                const progress_percent = document.getElementById("progess_percent");*/
                if(progress_label != null) progress_label.innerHTML = "Preparing...";
                const {
                    createFFmpeg,
                    fetchFile
                } = FFmpeg;
                const ffmpeg = createFFmpeg({
                    corePath: 'https://unpkg.com/@ffmpeg/core@0.11.0/dist/ffmpeg-core.js',
                });
                await ffmpeg.load();
                var uarray = await fetchFile(fileReader.result);
                ffmpeg.FS('writeFile', 'media', uarray);
                ffmpeg.setProgress(({ ratio }) => {
                    if(typeof progress_percent != "undefined") progress_percent.innerText = Math.round(ratio * 100) + "%";
                });
                if(typeof progress_label != "undefined") progress_label.innerHTML = "Preparing your media file...";
                if(mimeTypes["video"].includes(asset.type)) {
                    console.log("Video detected");
                    ffmpeg.run('-i', 'media', '-vf', "scale='min(1280,iw):-1'", '-crf', '30', '-b:a', '96k', 'output.mp4').then(() => {
                        console.log("Done");
                        if(progress_label != null) progress_label.innerHTML = "Uploading...";
                        var output = ffmpeg.FS('readFile', 'output.mp4');
                        resolve(new Blob([output], { type: "video/mp4" }));
                        ffmpeg.FS('unlink', 'output.mp4');
                        ffmpeg.FS('unlink', 'video');
                    }).catch(() => reject());
                }
                if(mimeTypes["audio"].includes(asset.type)) {
                    console.log("Audio detected");
                    ffmpeg.run('-i', 'media', '-b:a', '96k', 'output.ogg').then(() => {
                        var output = ffmpeg.FS('readFile', 'output.ogg');
                        resolve(new Blob([output], {type: "audio/ogg"}));
                    });
                }
            } else {
                var res = await fetch(fileReader.result);
                var blob = await res.blob();
                resolve(blob);
            }
        });
        fileReader.readAsDataURL(asset);
    });
}

function getConvertedType(mime) {
    if(mimeTypes['video'].includes(mime)) return "video/mp4";
    else if(mimeTypes['audio'].includes(mime)) return "audio/ogg";
    else if(mimeTypes['image'].includes(mime)) return "image/jpg";
    else return mime;
}