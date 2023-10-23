<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/document.php';

    $u = new User();
    $u->restoreFromSession(true);

?><!DOCTYPE html>
<html>
    <head>
        <title>My Documents</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Documents</h1>
        <h2>My Documents</h2>
        <div style="text-align:right"><a href="create"><img src="/res/img/plus.svg" height="15px"> Create a new document</a></div>
        <?php 
            $my = $u->getDocuments();
            foreach($my AS $doc) {
                echo '<div class="doc_list_item"><img src="/res/img/document.svg"><span><a href="edit?i='.$doc->getID().'">'.$doc->getName().'</a></span><span><a href="/account/view?i='.$u->getID().'">'.$u->getName().'</a></span><span>'.$doc->getLastEditTime().'</span><a href="details?i='.$doc->getID().'"><img src="/res/img/info.svg"></a><img src="/res/img/delete.svg" onclick="confirmDocRemoval(\''.$doc->getID().'\', this);"></div>';
            }
            ?>
        <h2>Shared with me</h2>
        <?php 
            $shared = $u->getSharedDocuments();
            foreach($shared AS $doc) {
                $owner = $doc->getOwner();
                echo '<div class="doc_list_item"><img src="/res/img/document.svg"><span><a href="edit?i='.$doc->getID().'">'.$doc->getName().'</a></span><span><a href="/account/view?i='.$owner->getID().'">'.$owner->getName().'</a></span><span>'.$doc->getLastEditTime().'</span><a href="details?i='.$doc->getID().'"><img src="/res/img/info.svg"></a><img src="/res/img/delete.svg" onclick="confirmDocRemoval(\''.$doc->getID().'\', this);"></div>';
            }
        ?>
        <style>
            .doc_list_item, .doc_list_item a {
                vertical-align: middle;
                overflow: auto;
                line-height: 65px;
                padding: 10px;
                display: flex;
                align-items: center;
                justify-content: space-around;
                border-radius: 5px;
            }
            .doc_list_item:nth-child(odd) {
                background-color: #EBEBEB;
            }
            .doc_list_item * {
                margin-left: 20px;
                margin-right: 20px;
            }
            .doc_list_item img {
                height: 45px;
                width: auto;
            }
        </style>
        <script>
            function confirmDocRemoval(id, node) {
                if(window.confirm("Would you like to irreversibly remove this document?")) {
                    removeDocument(id);
                    node.parentNode.remove();
                }
            }
        </script>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/cloudStoreFunctions.js"></script>
    </body>
</html>