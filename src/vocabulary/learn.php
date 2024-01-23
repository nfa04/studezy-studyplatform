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
        <title>Learn "<?php echo $vset->getName(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
        <link rel="stylesheet" href="/res/css/studying.css">
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Learn "<?php echo $vset->getName(); ?>"</h1>
        <div>By "<?php echo $vset->getUser()->getName(); ?> in "<?php echo $vset->getCourse($u)->getName(); ?>"</div>
        <div id="flex_container">
            <div id="progress_bar">
                <div id="set_name_container"></div>
                    <div>
                        <hr>
                        <div>
                            Progress: <span id="score_percentage_container"></span>%
                        </div>
                        <div>
                            <canvas id="score_percentage_range" width="100" height="20"></canvas>
                        </div>
                    </div>
                </div>
            <div id="container">
                <div id="question_container"></div>
                <hr>
                <div id="answer_container">
                    <input type="text" placeholder="Answer here..." id="answer_input"><input type="button" value="Check" id="check_btn">
                </div>
            </div>
        </div>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/learn.js"></script>
    </body>
</html>