<?php
    require '../res/incl/classes/user.php';
    require_once '../res/incl/mime2ext.php';

    $u = new User();
    $u->restoreFromSession(true);

    $a = new Assignment();
    $a->fromID($_GET['i']);
    $lookupUser = new User();
    $lookupUser->fromUID($_GET['u']);
    $s = $a->getSubmissionsByUser($lookupUser);

    $c = $a->getCourse($u);

    if($a->hasWriteAccess($u)) {
?><!DOCTYPE html>
<html>
    <head>
        <title>Submission by "<?php echo $lookupUser->getName(); ?>" for "<?php echo $a->getTitle(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Submission by "<?php echo $lookupUser->getName(); ?>"</h1>
        <p>Submitted to assignment: "<?php echo $a->getTitle(); ?>" in course: "<?php echo $c->getName(); ?>"</p>
        <div>
            <p><?php echo $lookupUser->getName(); ?> submitted <?php echo count($s); ?> files:</p>
            <ul>
                <?php
                    foreach($s AS $submission) {
                        echo '<li><a href="'.$submission->getFilePath().'">'.$submission->getFileName().'</a></li>';
                    }
                ?>
            </ul>
        </div>
        <?php $feedback = $a->getFeedbackForUser($lookupUser); 
        if(!$feedback) { ?>
        <form action="saveFeedback" method="post">
            <input type="hidden" name="aid" value="<?php echo $a->getID(); ?>">
            <input type="hidden" name="uid" value="<?php echo $lookupUser->getID(); ?>">
            <textarea name="feedback" placeholder="Enter feedback..."></textarea><br>
            <input type="submit" value="Submit feedback">
        </form>
        <?php } else { ?>
            <h2>Feedback</h2>
            <div>by <?php echo $feedback->getSender()->getName(); ?> at <?php echo $feedback->getSubmissionTime(); ?></div>
            <p><?php echo $feedback->getContent(); ?></p>
        <?php } ?>
    </body>
</html><?php } ?>