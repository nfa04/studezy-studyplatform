<?php

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {
        $c = new Course();
        $c->fromDataObject(array(
            'id' => uniqid(),
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'owner' => $u->getID(),
            'private' => isset($_POST['private']),
            'access_key' => password_hash($_POST['passkey'], PASSWORD_DEFAULT)
        ));
        $c->create();

        header('Location: view?i='.$c->getID());
    }

?>