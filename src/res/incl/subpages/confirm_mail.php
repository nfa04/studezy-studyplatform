<!DOCTYPE html>
<html>
    <head>
        <?php require __DIR__.'/../head.php'; ?>
        <title>Confirm your email!</title>
    </head>
    <body>
        <?php require __DIR__.'/../nav_public.php'; ?>
        <h1>Confirm your email!</h1>
        <p>Please confirm your email in order to use the service.</p>
        <form action="mailConfirmed" method="POST">
            <input type="text" name="ccode" placeholder="Confirmation code">
            <input type="submit">
        </form>
    </body>
</html>