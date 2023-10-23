<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        
        switch($_POST['comment_type']) {
            case 'announcement':

                $announcement = new Announcement();
                $announcement->fromID($_POST['comment_on']);
                $announcement->addComment($_POST['content'], $u);

                break;
        }

    }

?>