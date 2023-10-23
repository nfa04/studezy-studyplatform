<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $c = new Course();
    $c->fromID($_GET['cid'], $u);

    if($c->hasWriteAccess($u)) {

?><!DOCTYPE html>
<html>
    <head>
        <title>Create a new assignment | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Create a new assignment</h1>
        <p>Please enter the details of this assignment below.</p>
        <form action="save" method="post">
            <input type="hidden" name="cid" value="<?php echo $c->getID(); ?>">
            <input type="text" name="title" placeholder="Assignment title"><br>
            <textarea name="description" placeholder="Assignment description"></textarea><br>
            <div>Assignment due: <input type="date" name="date"></div>
            <div>
                <input type="submit" value="Create assignment">
            </div>
        </form>
    </body>
</html>
<?php } ?>