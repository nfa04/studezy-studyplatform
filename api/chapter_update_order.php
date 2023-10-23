<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $course = new Course();
    $course->fromID($_POST['cid'], $u);

    echo $course->changeOrder($u, $_POST['order']);
?>