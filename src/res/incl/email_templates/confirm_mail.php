<?php
$mail_msg = '<!DOCTYPE html>
<html>
    <body>
        <h1>Welcome to a new studying experience!</h1>
        <p>Welcome to an all new studying experience. To activate your account please activate your account by typing this code: <b>'.$user['verification_code'].'</b></p>
    </body>
</html>';
$mail_subject = 'Welcome to a new studying experience!';
$mail_headers =         
"From: Account verification <no-reply@".$_SERVER['SERVER_NAME'].">\r\nContent-type: text/html";

mail($user['mail'], $mail_subject, $mail_msg, $mail_headers);

?>