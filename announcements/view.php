<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $a = new Announcement();
    $a->fromID($_GET['i']);

?><!DOCTYPE html>
<html>
    <head>
        <title>Announcement: "<?php echo $a->getTitle(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Announcement: "<?php echo $a->getTitle(); ?>"</h1>
        <div>by <a href="/account/view?i=<?php $aUser = $a->getUser(); echo $aUser->getID(); ?>"><?php echo $aUser->getName(); ?></a> in "<a href="/courses/view?i=<?php $aCourse =  $a->getCourse($u); echo $aCourse->getID(); ?>"><?php echo $aCourse->getName(); ?></a>"</div>
        <h2>Announcement</h2>
        <div>
            <h3><?php echo $a->getTitle(); ?></h3>
            <p><?php echo $a->getContent(); ?></p>
        </div>
        <h2>Comments</h2>
        <div id="comment_container">
            <?php
                $comments = $a->getComments();
                foreach($comments AS $comment) {
                    echo '<div>
                        <div><b>'.$comment->getUser()->getName().'</b> at '.$comment->getTime().'</div>
                        <div>'.$comment->getContent().'</div>
                    </div>';
                }
            ?>
            <form id="comment_adding_section">
                <textarea placeholder="Comment on this..." id="comment_textarea"></textarea> <input type="button" value="Comment" onclick="comment('announcement')">
            </form>
            <script src="/res/js/core.js"></script>
            <script src="/res/js/comment.js"></script>
        </div>
    </body>
</html>