<?php
    require '../res/incl/classes/user.php';
    require_once '../res/incl/mime2ext.php';
    $u = new User();
    $u->restoreFromSession(true);

    $info_user = new User();
    $info_user->fromUID($_GET['i']);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>"<?php echo $info_user->getName(); ?>" on StudEzy | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1><?php echo $info_user->getName(); ?> <a href="#diploma"><span class="badges"><?php 

            $titles = $info_user->getTitles();

            foreach($titles AS $key=>$title) {
                // Make sure every title is only displayed once as they are returned twice from the db
                if(gettype($key) == 'integer') {
                    switch($title['type']) {
                        case 0:
                            echo '<img src="/res/img/admin_badge.svg" title="Admin at StudEzy">';
                            break;
                        case 1:
                            echo '<img src="/res/img/teacher_badge.svg" title="Verified teacher: '.$title['title_name'].'">';
                            break;
                        case 2:
                            echo '<img src="/res/img/doctor_badge.svg" title="Verified doctor: '.$title['title_name'].'">';
                            break;
                        case 3:
                            echo '<img src="/res/img/professor_badge.svg" title="Verified professor: '.$title['title_name'].'">';
                            break;
                    }
                }
            }

            ?></a></span></h1>
        <div>On StudEzy since <?php  echo $info_user->getCreatedOn(); ?></div>
        <div class="subscriber_banner">
            <div>
                <div class="banner_large"><?php echo $info_user->countFollowers(); ?></div>
                <div class="banner_small"><a href="followers?i=<?php echo $info_user->getID(); ?>">Followers</a></div>
            </div>
            <div>
                <div class="banner_large"><?php echo $info_user->countFollows(); ?></div>
                <div class="banner_small"><a href="follows?i=<?php echo $info_user->getID(); ?>">Following </a></div>
            </div> 
        </div>
        <p><?php echo $info_user->getDescription(); ?></p>
        <div>
            <input type="button" value="<?php $isFollowing = $u->isFollowing($info_user); echo ($isFollowing ? 'Unfollow' : 'Follow')?>" onclick="<?php echo ($isFollowing ? 'un': '') ?>followUser()" id="subscribe_btn">
            <a href="/messages/chat?cc=<?php echo $info_user->getID(); ?>"><input type="button" value="Message"></a>
        </div>
        <?php if($info_user->isFollowing($u)) echo '<p>Is following you.</p>' ?>
        <h2>Courses</h2>
        <div>
            <?php
                $coursesBy = $info_user->getCoursesBy();
                foreach($coursesBy AS $course) {
                    echo '<div><a href="/courses/view?i='.$course->getID().'">'.$course->getName().'</a></div>';
                }
                if(empty($coursesBy)) echo '<p>This user doesn\'t have any courses yet.</p>';
            ?>
        </div>
        <h2>Assets</h2>
        <?php
            $assets = $info_user->getAssetsBy();
            if(empty($assets)) echo '<p>This user doesn\'t have any assets yet.</p>';
            else {
        ?>
        <table>
            <thead>
                <tr>
                    <td>Name</td>
                    <td>Course</td>
                    <td>Type</td>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($assets AS $asset) {
                        $course = $asset->getCourse($u);
                        if($course->hasReadAccess($u)) {
                            echo '<tr><td><a href="/assets/'.$asset->getID().'.'.mime2ext($asset->getType()).'">'.$asset->getName().'</a></td><td><a href="/courses/view?i='.$course->getID().'">'.$course->getName().'</a></td><td>'.$asset->getType().'</td></tr>';
                        }
                    }
                ?>
            </tbody>
        </table>
        <?php } ?>
        <h2 id="diploma">Diploma</h2>
        <?php
            if(empty($titles)) echo '<p>This user hasn\'t uploaded any diploma yet.</p>';
            foreach($titles AS $title) {
                if(gettype($key) == 'integer') {
                    switch($title['type']) {
                        case 0:
                            echo '<div class="diploma"><img src="/res/img/admin_badge.svg" title="Admin at StudEzy"><b>Admin at StudEzy</b><p>This user is an admin at StudEzy.</p></div>';
                            break;
                        case 1:
                            echo '<div class="diploma"><img src="/res/img/teacher_badge.svg" title="Verified teacher: '.$title['title_name'].'"><b>'.$title['title_name'].'</b><p>This user holds the title "'.$title['title_name'].'", issued by '.$title['name'].'.</p><p>Verified by StudEzy.</p></div>';
                            break;
                        case 2:
                            echo '<div class="diploma"><img src="/res/img/doctor_badge.svg" title="Verified doctor: '.$title['title_name'].'"><b>'.$title['title_name'].'</b><p>This user holds the title "'.$title['title_name'].'", issued by '.$title['name'].'.</p><p>Verified by StudEzy.</p></div>';
                            break;
                        case 3:
                            echo '<div class="diploma"><img src="/res/img/professor_badge.svg" title="Verified professor: '.$title['title_name'].'"><b>'.$title['title_name'].'</b><p>This user holds the title "'.$title['title_name'].'", issued by '.$title['name'].'.</p><p>Verified by StudEzy.</p></div>';
                            break;
                    }
                }
            }
            if($info_user->isUser($u)) {
        ?>
        <p>You can request diploma verification by sending the details to <a href="mailto:diploma-verification@studezy.com">diploma-verification@studezy.com</a>
        <?php } ?>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/userFunctions.js"></script>
    </body>
</html>