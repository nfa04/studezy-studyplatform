<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $c = new Chapter();
        $c->fromID($_POST['id']);
        $course = $c->getCourse($u);
        $owner = $course->getOwner();
        echo json_encode(array(
            'name' => $c->getName(),
            'ownerID' => $owner->getID(),
            'ownerName' => $owner->getName(),
            'courseID' => $course->getID(),
            'courseName' => $course->getName()
        ));
    }

?>