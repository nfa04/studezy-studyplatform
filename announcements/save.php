<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $course = new Course();
    $course->fromID($_POST['cid'], $u);

    $a = $course->addAnnouncement($_POST['title'], $_POST['content'], $u)

?><!DOCTYPE html>
<html>
    <head>
        <title>Announcement saved | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Announcement saved</h1>
        <p>Your announcement was saved successfully.</p>
        <div>
            <a href="/announcements/view?i=<?php echo $a->getID(); ?>"><input type="button" value="View"></a> <a href="/courses/view?i=<?php echo $course->getID(); ?>"><input type="button" value="Back to course"></a>
        </div>
    </body>
</html>