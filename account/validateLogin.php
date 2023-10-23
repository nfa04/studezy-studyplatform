<?php
    session_start();

    require '../res/incl/classes/user.php';

    $u = new User();
    if($u->login($_POST['uname'], $_POST['pw'])) {
        header('Location: /');
    } else {
        header('Location: /signin');
    }
?>