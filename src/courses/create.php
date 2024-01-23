<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

?><!DOCTYPE html>
<html>
    <head>
        <title>Create a course | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Create a course</h1>
        <form action="creation" method="post">
            <input type="text" placeholder="Course name" name="name">
            <textarea placeholder="Description..." name="description"></textarea>
            <hr>
            <input type="checkbox" id="priv" name="private"> <label for="priv">Make course private <input type="text" name="passkey" placeholder="Access key"></label>
            <input type="submit" value="Create">
        </form>
    </body>
</html>