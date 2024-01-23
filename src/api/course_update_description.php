<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $c = new Course();
        $c->fromID($_POST['cid'], $u);
        $c->setDescription($_POST['description'], $u);
    }
?>