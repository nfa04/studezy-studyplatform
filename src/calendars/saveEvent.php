<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/calendar.php';

    $u = new User();
    $u->restoreFromSession(true);

    // At the moment we only allow users to edit their own calendars, will change...
    $cmanager = new CalendarManager();
    $cmanager->fromID($_POST['calendar_id']);

    $cmanager->addEntry($_POST['title'], $_POST['description'], $u, $_POST['date_start'].' '.$_POST['time_start'], $_POST['date_end'].' '.$_POST['time_end'], (isset($_POST['private']) ? 1 : 0));

?><!DOCTYPE html>
<html>
    <head>
        <title>Event created | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Event created</h1>
        <p>Your event was created successfully</p>
        <div>
            <a href="javascript:;" onclick="window.history.go(-2)">
                <input type="button" value="Continue">
            </a>
        </div>
    </body>
</html>