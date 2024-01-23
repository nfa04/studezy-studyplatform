<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $c = new Course();
    $c->fromID($_GET['i'], $u);
?><!DOCTYPE html>
<html>
    <head>
        <title>Access denied | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Access denied</h1>
        <p>You don't have access to "<?php echo $c->getName(); ?>" by "<?php echo $c->getOwner()->getName(); ?>" yet. If you'd like to access this course, you may access it using an access key.</p>
        <form action="subscribe?i=<?php echo $_GET['i']; ?>" method="post">
            <input type="text" name="ak">
            <input type="submit">
        </form>
    </body>
</html>