<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        
        echo json_encode(array_map(function($user) {
            return array(
                'uid' => $user->getID(),
                'uname' => $user->getName(),
                'cname' => $user->getContactName()
            );
        }, $u->getContacts()));

    }

?>