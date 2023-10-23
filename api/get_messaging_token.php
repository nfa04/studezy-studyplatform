<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    if($u->restoreFromSession()) {
        echo json_encode(array(
            'userID' => $u->getID(),
            'token' => $u->generateMessageToken(),
            'userName' => $u->getName(),
            'serverLocation' => SERVER_CONFIG['CHATSERVER_REMOTE_LOCATION']
        ));
    }

?>