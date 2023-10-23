<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $c = new Course();
    $c->fromID($_GET['i'], $u); 

    if(!$c->checkAccessKey($_POST['ak'])) {
        header('Location: accessDenied?i='.$_GET['i']);
        die('You will be redirected...');
    } else {
        $c->subscribe($u);
    }

?><!DOCTYPE html>
<html>
    <head>
        <title>Successfully subscribed | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Successfully subscribed</h1>
        <img src="../res/img/check.png" style="height:200px;">
        <p>You successfully subscribed to "<?php echo $c->getName(); ?>" by "<?php echo $c->getOwner()->getName(); ?>".</p>
        <div>
            <a href="view?i=<?php echo $_GET['i']; ?>"><input type="button" value="Continue" class="button-primary"></a>
        </div>
    </body>
</html>