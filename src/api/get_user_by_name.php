<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $lookupUser = new User();
        if($lookupUser->byName($_POST['uname'])) {
            echo json_encode(array(
                'userName' => $lookupUser->getName(),
                'userID' => $lookupUser->getID()
            ));
        } else echo '0';
    }

?>