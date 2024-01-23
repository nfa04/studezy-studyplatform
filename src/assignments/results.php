<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $a = new Assignment();
    $a->fromID($_GET['i']);

    if($a->hasWriteAccess($u)) {
?><!DOCTYPE html>
<html>
    <head>
        <title>Assignment results | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Results of: "<?php echo $a->getTitle(); ?>"</h1>
        <h2>Submitted</h2>
        <div>
            <?php
            $submitted = $a->getSubmissionInfoByUser();
            foreach($submitted AS $s) {
                echo '<div>
                    <div><b><a href="result?i='.$a->getID().'&u='.$s[0].'">'.$s[2].'</a></b></div>
                    <div>'.($a->timeIsOverdue(strtotime($s[3])) ? '<span style="color:red">Late</span>': '<span style="color:green">On time</span>').' Submitted '.$s[1].' files</div>
                </div>';
            }
            ?>
        </div>
        <h2>Not submitted</h2>
        <div>
            <?php
                $ns = $a->getUsersWithoutSubmission();
                foreach($ns AS $user) {
                    echo '<div>
                        <b><a href="/account/view?i='.$user->getID().'">'.$user->getName().'</a></b>
                    </div>';
                }
            ?>
        </div>
    </body>
</html><?php } ?>