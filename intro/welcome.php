<?php
    require '../res/incl/classes/user.php';
    $u = new User();
    $u->restoreFromSession(true);
?><!DOCTYPE html>
<html>
    <head>
        <title>Welcome to Free Flashcards & more</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Welcome!</h1>
        <p>We're happy to welcome you to an all new studying experience! Let's start with a short intro!</p>
        <div class="button-field">
            <a href="/"><input type="button" class="button-primary" value="Continue"></a>
            <a href="/"><input type="button" class="button-secondary" value="Skip"></a>
        </div>
    </body>
</html>