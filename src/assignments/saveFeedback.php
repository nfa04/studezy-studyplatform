<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $a = new Assignment();
    $a->fromID($_POST['aid']);

    if($a->hasWriteAccess($u)) {

        $feedbackFor = new User();
        $feedbackFor->fromUID($_POST['uid']);

        $a->submitFeedback($_POST['feedback'], $feedbackFor, $u);
?><!DOCTYPE html>
<html>
    <head>
        <title>Feedback saved | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Feedback saved</h1>
        <p>Your feedback was saved successfully.</p>
    </body>
</html><?php } ?>