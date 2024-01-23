<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/document.php';

    $u = new User();

    if($u->restoreFromSession()) {

        $doc = new Document();
        $doc->fromID($_POST['id']);

        if($_POST['action'] == 'update') {

            $user = new User();
            $user->fromUID($_POST['user']);

            $doc->updateWriteAccess($u, $user, ($_POST['writeAccess'] == 'true' ? 1 : 0));
        }

        else if($_POST['action'] == 'state-change') {
            $doc->setPrivacyPreference($u, ($_POST['private'] == 'true' ? 1 : 0));
        }

        else if($_POST['action'] == 'add') {

            $user = new User();
            $user->fromUID($_POST['user']);

            $doc->share($u, $user, ($_POST['writeAccess'] == 'true' ? 1 : 0));
        }

        else if($_POST['action'] == 'remove') {
            
            $user = new User();
            $user->fromUID($_POST['user']);

            $doc->removePermissions($u, $user);

        }

    }

?>