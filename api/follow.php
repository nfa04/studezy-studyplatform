<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $follow = new User();
        $follow->fromUID($_POST['uid']);
        $follow->follow($u);
    }

?>