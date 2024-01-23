<!DOCTYPE html>
<html>
    <head>
        <title>Wrong verification code!</title>
        <?php require __DIR__.'/../head.php'; ?>
    </head>
    <body>
        <?php require __DIR__.'/../nav_public.php'; ?>
        <h1>Wrong verification code!</h1>
        <p>Your verification code was wrong. Please try again.</p>
        <form action="mailConfirmed" method="post">
            <input type="text" name="ccode" placeholder="Confirmation code">
            <input type="submit">
        </form>
    </body>
</html>