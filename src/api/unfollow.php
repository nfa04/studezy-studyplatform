<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $followed_user = new User();
        $followed_user->fromUID($_POST['uid']);

        $followed_user->unfollow($u);
    }

?>