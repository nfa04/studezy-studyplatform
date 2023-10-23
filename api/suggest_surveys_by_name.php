<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/survey.php';

    $u = new User();

    if($u->restoreFromSession()) {
        echo json_encode(suggestSurveysByName($_POST['name']));
    }
?>