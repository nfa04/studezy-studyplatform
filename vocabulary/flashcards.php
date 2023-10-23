<?php
    
    require '../res/incl/classes/user.php';
    require '../res/incl/classes/vocabularyset.php';

    $u = new User();
    $u->restoreFromSession(true);

    $vset = new VocabularySet();
    $vset->fromID($_GET['i']);

?><!DOCTYPE html>
<html>
    <head>
        <title>Flashcards from "<?php echo $vset->getName(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
        <link rel="stylesheet" href="/res/css/flashcards.css">
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Flashcards "<?php echo $vset->getName(); ?>"</h1>
        <div>By <?php echo $vset->getUser()->getName(); ?></div>
            <div id="cardholder">
            <div id="card"></div>
            <div id="index_container"></div>
            <div id="controls">
                <img src="/res/img/arrow_back.svg" id="arrow_back"> 
                <img src="/res/img/turn.svg" id="turn"> 
                <img src="/res/img/arrow_forward.svg" id="arrow_forward">
            </div>
        </div>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/flashcards.js"></script>
    </body>
</html>