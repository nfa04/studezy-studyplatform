<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/chat.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $chat = new Chat();
        $chat->fromID($_POST['id']);

        echo json_encode(array(
            'id' => $chat->getID(),
            'name' => $chat->getName(),
            'memberCount' => $chat->countMembers()
        ));
    }

?>