<?php

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {
        $u->pushSubscribe($_POST['data']);
    }

?>