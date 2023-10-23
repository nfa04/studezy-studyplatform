<?php
    require '../res/incl/classes/user.php';
    $u = new User();
    $u->restoreFromSession(true);
?><!DOCTYPE html>
<html>
    <head>
        <title>Successfully submitted assignment | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Successfully submitted assignment</h1>
        <p>You successfully submitted your assignment.</p>
        <div>
            <input type="button" onclick="window.history.back()" value="Back to assignment">
        </div>
    </body>
</html>