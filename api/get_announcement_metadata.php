<?php

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {

        $a = new Announcement();
        $a->fromID($_POST['id']);

        echo json_encode(array(
            'name' => $a->getTitle(),
            'description' => $a->getContent(),
            'owner' => $a->getUser()->getName()
        ));

    }

?>