<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        echo json_encode($u->getSurveyRawData());
    }

?>