<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $chap = new Chapter();
    $chap->fromID($_GET['i']);

    if(!$chap->remove($u)) die();

?><!DOCTYPE html>
<html>
    <head>
        <title>Dropped chapter "<?php echo $chap->getName(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Deleted chapter "<?php echo $chap->getName(); ?>"</h1>
        <p>The chapter was deleted successfully!</p>
        <div>
            <a href="editContents?i=<?php echo $chap->getCourse($u)->getID(); ?>">
                <input type="button" value="Back">
            </a>
        </div>
    </body>
</html>