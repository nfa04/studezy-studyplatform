<?php
    require '../res/incl/classes/user.php';
    $u = new User();
    $u->restoreFromSession(true);

    $asset = new Asset();
    $asset->fromID($_GET['i']);

    if($asset->remove($u)) {
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Removed asset "<?php echo $asset->getName(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Asset "<?php echo $asset->getName(); ?>" removed</h1>
        <p>Your asset was removed successfully.</p>
        <div><a href="editContents?i=<?php echo $asset->getCourseID(); ?>"><input type="button" value="Back"></a></div>
    </body>
</html>
<?php } else  { ?>
<p>Error.</p>
<?php } ?>