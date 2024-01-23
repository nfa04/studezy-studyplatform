<?php
    require '../res/incl/classes/user.php';
    $u = new User();
    $u->restoreFromSession(true);

    $info_user = new User();
    $info_user->fromUID($_GET['i']);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>People followed by "<?php echo $info_user->getName();  ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>People followed by "<?php echo $info_user->getName();  ?>"</h1>
        <p>Following <?php
            $follows = $info_user->getFollows();
            echo count($follows);
        ?> people</p>
        <div>
            <?php
                foreach($follows AS $follower) {
                    echo '<div><a href="/account/view?i='.$follower->getID().'">'.$follower->getName().'</a></div>';
                }
            ?>
        </div>
    </body>
</html>