<?php
    require '../res/incl/classes/user.php';
    require '../res/incl/classes/calendar.php';
    $u = new User();
    if($u->restoreFromSession()) {
        $course = new Course();
        $course->fromID($_POST['cid'], $u);

        $course->setCalendarSubscription($u, ($_POST['state'] == 'true' ? 1 : 0));
    }
?>