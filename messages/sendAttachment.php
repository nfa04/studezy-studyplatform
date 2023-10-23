<?php


    // Headers required to use SharedArrayBuffer
    header('Cross-Origin-Embedder-Policy: require-corp');
    header('Cross-Origin-Opener-Policy: same-origin');


    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

?><!DOCTYPE html>
<html>
    <head>
        <title>Choose attachment | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Choose an attachment</h1>
        <div id="fInput_container"></div>
        <script src="/res/js/core.js"></script>
        <script src="//cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.0/dist/ffmpeg.min.js"></script>
        <script src="/res/js/fileOperations.js"></script>
        <script src="/res/js/fileSelector.js"></script>
        <script src="/res/js/chatAttachments.js"></script>
    </body>
</html>