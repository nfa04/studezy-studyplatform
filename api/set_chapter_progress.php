<?php

    require '../res/incl/classes/user.php';
    
    $u = new User();

    if($u->restoreFromSession()) {
        
        $c = new Chapter();
        $c->fromID($_POST['chid']);

        $c->getProgress($u)->update($_POST['progress'], $_POST['stars']);
    }

?>