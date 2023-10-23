<?php
    session_start(); 
    require 'res/incl/classes/user.php';

    $u = new User();
    $signed_in = $u->restoreFromSession();

?><!DOCTYPE html>
<html>
    <head>
        <title>StudEzy | Free Study Platform</title>
        <?php require 'res/incl/head.php'; ?>
        <?php if(!$signed_in) { ?><link rel="stylesheet" href="/res/css/landingpage.css"><?php } ?>
    </head>
    <body>
        <?php 
            if(!$signed_in) {
                // User is not logged in, show landing page
                require 'res/incl/subpages/welcome.php';
            }
            else require 'res/incl/subpages/dashboard.php';
        
        require 'res/incl/footer.php';
        ?>
    </body>
</html>