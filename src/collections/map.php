<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    header('Access-Control-Allow-Origin: https://localhost:3004');

?><!DOCTYPE html>
<html>
    <head>
        <?php require '../res/incl/head.php'; ?>
        <link rel="stylesheet" href="/res/css/mindmap.css">
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <div id="listBar">
            <div>
                <img src="/res/img/plus.svg" id="add_node_btn">
                <img src="/res/img/delete.svg" id="remove_node_btn">
            </div>
            <div id="tree_container"></div>
        </div>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/mindmap.js"></script>
        <script src="http://localhost:3004/socket.io/socket.io.js"></script>
        <script src="/res/js/mindmapEditor.js"></script>
    </body>
</html>