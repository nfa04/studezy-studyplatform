<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        echo json_encode(array(
            'userID' => $u->getID(),
            'token' => $u->generateDocToken(),
            'userName' => $u->getName(),
            'serverLocation' => SERVER_CONFIG['DOCSERVER_REMOTE_LOCATION']
        ));
    }

?>