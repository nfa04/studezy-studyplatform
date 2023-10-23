<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/survey.php';

    $u = new User();

    if($u->restoreFromSession()) {

        $course = new Course();
        $course->fromID($_POST['cid'], $u);

        header('Location: edit?i='.$course->addSurvey($_POST['name'], $_POST['desc'], intval(isset($_POST['sa'])), $u)->getID());

    }

?>