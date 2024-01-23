<?php
    session_start();

    require '../res/incl/classes/user.php';

    // Strip everything but alphanumeric inputs for usernames
    // Sanitize email
    // Passwords allow all characters and hash it

    $user = array(
        'uid' => uniqid(),
        'uname' => $_POST['un'],
        'mail' => filter_input(INPUT_POST, 'em', FILTER_SANITIZE_EMAIL),
        'password' => password_hash($_POST['pw'], PASSWORD_DEFAULT),
        'code' => htmlspecialchars($_POST['ic']),
        'verification_code' => bin2hex(random_bytes(3)),
        'calendar_id' => uniqid()
    );

    // Create a new empty user object and sign up
    $u = new User();

    if($u->signup($user)) {
        require '../res/incl/email_templates/confirm_mail.php';
        require '../res/incl/subpages/confirm_mail.php';
    }
    else {
        header('Location: /signup?e=1');
    }
?>