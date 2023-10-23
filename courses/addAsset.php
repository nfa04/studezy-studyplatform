<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    header('Cross-Origin-Embedder-Policy: require-corp');
    header('Cross-Origin-Opener-Policy: same-origin');

?><!DOCTYPE html>
<html>
    <head>
        <title>Add asset | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php
            require '../res/incl/nav.php';
        ?>
        <h1>Add asset</h1>
        <p>Please choose a file to upload.</p>
        <form>
            <div><input type="text" id="name" placeholder="Asset name"></div>
            <div>
                <input type="file" id="asset">
                <input type="button" value="Upload" onclick="sendAsset()">
            </div>
            <div>
                <label for="progress_bar" id="progress_label">Processing:</label>
                <span id="progess_percent"></span>
                <progress id="progress_bar" value="0" max="100"></progress>
            </div>
        </form>
        <script src="//cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.0/dist/ffmpeg.min.js"></script>
        <script src="../res/js/core.js"></script>
        <script src="../res/js/fileOperations.js"></script>
        <script src="/res/js/assetUpload.js"></script>
    </body>
</html>