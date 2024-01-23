<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/calendar.php';

    $u = new User();
    $u->restoreFromSession(true);

    $event = new CalendarEntry();
    $event->fromID($_GET['i']);
    $event->remove($u);

?><!DOCTYPE html>
<html>
    <head>
        <title>Entry removed | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Event removed</h1>
        <p>Your event was removed successfully.</p>
        <div>
            <a href="javascript:;" onclick="window.history.go(-2)">
                <input type="button" value="Continue">
            </a>
        </div>
    </body>
</html>