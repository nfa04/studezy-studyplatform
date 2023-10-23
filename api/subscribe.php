<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {

        $course = new Course();
        $course->fromID($_POST['cid'], $u);

        $course->subscribe($u);

    }

?>