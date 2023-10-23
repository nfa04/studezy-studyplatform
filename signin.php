<!DOCTYPE html>
<html>
    <head>
        <title>Sign in | Free Flashcards & more</title>
        <?php require 'res/incl/head.php'; ?>
    </head>
    <body>
        <?php require 'res/incl/nav_public.php'; ?>
        <h1>Sign in</h1>
        <form action="account/validateLogin" method="post">
            <div>
                Username:<br><input type="text" name="uname" placeholder="Username">
            </div>
            <div>
                Password:<br><input type="password" name="pw" placeholder="Password">
            </div>
            <input type="submit" value="Sign in">
        </form>
    </body>
</html>