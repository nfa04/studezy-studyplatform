<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $c = new Course();
    $c->fromID($_POST['cid'], $u);

    if($c->addAssignment($_POST['title'], $_POST['description'], $_POST['date'], $u)) {

?><!DOCTYPE html>
<html>
    <head>
        <title>Assignment created | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Assignment created</h1>
        <p>Your assignment was successfully created.</p>
        <div>
            <a href="/courses/view?i=<?php echo $c->getID(); ?>"><input type="button" value="Back to course"></a>
        </div>
    </body>
</html><?php } ?>