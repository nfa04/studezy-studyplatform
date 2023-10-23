<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $c = new Course();
        $c->fromID($_POST['id'], $u);

        echo json_encode($c->getAssignmentsRawData());
    }

?>