<?php
    require 'res/incl/classes/user.php';
    $u = new User();
    $u->restoreFromSession(true);
    $u->logout();
    header('Location: /');
?>