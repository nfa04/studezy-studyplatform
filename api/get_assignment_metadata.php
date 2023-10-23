<?php

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {

        $a = new Assignment();
        $a->fromID($_POST['id']);

        echo json_encode(array(
            'name' => $a->getTitle(),
            'description' => $a->getDescription(),
            'owner' => $a->getOwner()->getName()
        ));

    }

?>