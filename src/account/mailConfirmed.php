<?php
    session_start();

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {
        // User is logged in
        if($u->getConfirmationCode() == $_POST['ccode']) {
            // Code was correct
            $u->setMailVerified();
            require '../res/incl/subpages/mail_confirmed.php';
        } else {
            // User is logged in, but confirmation code is wrong
            require '../res/incl/subpages/wrong_ccode.php';
        }
    } else {
        // No valid session found, redirect to login page
        header('Location: /login');
    }

?>