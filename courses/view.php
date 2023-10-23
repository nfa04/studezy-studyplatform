<?php
    require '../res/incl/classes/user.php';
    require '../res/incl/classes/calendar.php';
    require '../res/incl/classes/survey.php';
    require_once '../res/incl/mime2ext.php';

    $u = new User();
    $u->restoreFromSession(true);

    $c = new Course();
    $c->fromID($_GET['i'], $u);
    if(!$c->hasReadAccess($u)) {
        header('Location: accessDenied?i='.$_GET['i']);
        die('You will be redirected...');
    };
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $c->getName(); ?> | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1><?php echo $c->getName(); ?></h1>
        <div>Created by <a href="/account/view?i=<?php $cOwn = $c->getOwner(); echo $cOwn->getID(); ?>"><?php echo $cOwn->getName(); ?></a></div>
        <div class="options_bar">
            <?php if($c->hasWriteAccess($u)) { ?>
            <img src="/res/img/person.svg" title="You own this course"><a href="editContents?i=<?php echo $_GET['i']; ?>" title="Edit"><img src="../res/img/edit.svg"></a>
            <a href="statistics/overview?i=<?php echo $_GET['i']; ?>" title="See statistics"><img src="/res/img/statistics.svg"></a>
            <?php } if($c->isPrivate()) { ?>
                <img src="/res/img/lock.svg">
            <?php } ?>
                <a href="/calendars/view?i=<?php echo $c->getCalendarManager()->getID(); ?>&m=m<?php if($c->hasWriteAccess($u)) echo '&cid='.$c->getID(); ?>"><img src="/res/img/calendar.svg"></a>
        </div>
        <div class="subscriber_banner">
            <div>
                <div class="banner_large">
                    <?php echo $c->countChapters(); ?>
                </div>
                <div class="banner_small">
                    Chapters
                </div>
            </div>
            <div>
                <div class="banner_large">
                    <?php echo $c->countSubscribers(); ?>
                </div>
                <div class="banner_small">
                    Subscribers
                </div>
            </div>
        </div>
        <p><?php echo $c->getDescription(); ?></p>
        <div>
            <input type="button" value="<?php $subscribed = $c->isSubscriber($u); echo ($subscribed ? 'Unsubscribe' : 'Subscribe'); ?>" onclick="<?php echo ($subscribed ? 'unsubscribe' : 'subscribe') ?>(this)">
            <br>
            <details>
                <summary>Course settings</summary>
                <div class="switch_container">
                    <label class="switch">
                        <input type="checkbox" id="notifications_active"<?php echo ($c->hasPushSubscribed($u) ? ' checked' : ''); ?>>
                        <span class="slider round"></span>
                    </label>
                    <label for="notifications_active">
                        <img src="/res/img/bell.svg"> Get push-notified on updates to this course
                    </label>
                </div>

                <div class="switch_container">
                    <label class="switch">
                        <input type="checkbox" id="email_active"<?php echo ($c->hasEmailSubscribed($u) ? ' checked' : ''); ?>>
                        <span class="slider round"></span>
                    </label>
                    <label for="email_active">
                        <img src="/res/img/mail.svg"> Get notified on updates to this course via email
                    </label>
                </div>

                <div class="switch_container">
                    <label class="switch">
                        <input type="checkbox" id="calendar_subscription"<?php echo ($c->hasCalendarSubscribed($u) ? ' checked' : ''); ?>>
                        <span class="slider round"></span>
                    </label>
                    <label for="calendar_subscription">
                        <img src="/res/img/calendar.svg"> Subscribe to the calendar of this course
                    </label>
                </div>
            </details>
        </div>
        <hr>
        <h2>What's new?</h2>
        <div class="media_item_list">
            <?php
                $new = $c->getNewContents();
                foreach($new AS $content) {
                    echo '<div class="media_item"><a href="chapter?i='.$content->getID().'">'.$content->getName().'</a></div>';
                }
                if(empty($new)) echo '<p class="media_item">There is nothing new yet.</p>';
            ?>
        </div>
        <h2>Contents in this course</h2>
        <div class="menubar_picker" id="menubar_picker">
            <span class="menubar_selected">Chapters</span>
            <span>Announcements</span>
            <span>Assignments</span>
            <span>Surveys</span>
            <span>Assets</span>
        </div>
        <div id="announcements">
        <h2>Announcements</h2>
        <div class="media_item_list">
            <?php
                $announcements = $c->getAnnouncements();
                foreach($announcements AS $announcement) {
                    $announcementUser = $announcement->getUser();
                    echo '<div class="media_item">
                        <img src="/res/img/announcement.svg">
                        <h3>'.$announcement->getTitle().'</h3>
                        <div>by <a href="/account/view?i='.$announcementUser->getID().'">'.$announcementUser->getName().'</a></div>
                        <p>'.$announcement->getContent().'</p>
                        <p><a href="/announcements/view?i='.$announcement->getID().'">View '.$announcement->countComments().' comments</a></p>
                    </div>';
                }
            ?>
            <?php if($c->hasWriteAccess($u)) echo '<div><a href="/announcements/create?cid='.$c->getID().'"><img src="/res/img/plus.svg" height="20"> Add announcement</a></div>'; ?>
        </div>
        </div>
        <div id="assignments">
        <h2>Assignments</h2>
        <div class="media_item_list">
            <?php
                $assignments = $c->getAssignments();
                foreach($assignments AS $assignment) {
                    $aOwner = $assignment->getOwner();
                    echo '<div class="media_item">
                        <img src="/res/img/assignment.svg">
                        <h3><a href="/assignments/view?i='.$assignment->getID().'">'.$assignment->getTitle().'</a></h3><div>'.
                        ($assignment->isOverdue() ? '<span style="color:red">Overdue</span> ' : '').($assignment->hasSubmitted($u) ? '<span style="color:green">Submitted</span>': '').'</div>
                        <div>by <a href="/account/view?i='.$aOwner->getID().'">'.$aOwner->getName().'</a></div>
                        <div>'.substr($assignment->getDescription(), 0, 50).'...</div>
                        <div>Created: '.$assignment->getCreationTime().', due: '.$assignment->getDueTime().'</div>
                    </div>';
                }
                if($c->hasWriteAccess($u)) {
                    echo '<div><a href="/assignments/create?cid='.$c->getID().'"><img src="/res/img/plus.svg" height="20"> Add assignment</a></div>';
                }
            ?>
        </div>
        </div>
        <div id="contents">
        <h2>Chapters</h2>
        <div>
            <ul>
            <?php
                $contents = $c->getContents();
                foreach($contents AS $chapter) {
                    echo '<li><a href="chapter?i='.$chapter->getID().'">'.$chapter->getName().'</a></li>';
                }
                if(empty($contents)) echo 'There are no contents in this course.';
            ?>
            </ul>
        </div>
        </div>
        <div id="surveys">
            <h2>Surveys</h2>
            <div class="media_item_list">
                <?php
                    $surveys = $c->getSurveys();
                    foreach($surveys AS $survey) {
                        echo '<div class="media_item">
                            <img src="/res/img/survey.svg">
                            <h3><a href="/surveys/view?i='.$survey->getID().'">'.$survey->getTitle().'</a></h3>
                            <div>'.$survey->getDescription().'</div>
                        </div>';
                    }
                ?>
            </div>
            <div><a href="/surveys/create?i=<?php echo $c->getID(); ?>"><img src="/res/img/plus.svg" height="20"> Add a new survey</a></div>
        </div>
        <div id="assets">
        <h2>Assets</h2>
        <div>
            <table>
                <thead>
                    <tr>
                        <td>Name</td>
                        <td>Type</td>
                        <td>Owner</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $assets = $c->getAssets();
                        foreach($assets AS $asset) {
                            $owner = $asset->getOwner();
                            echo '<tr><td><a href="'.ASSET_REMOTE_LOCATION_ROOT.$asset->getID().'.'.mime2ext($asset->getType()).'">'.$asset->getName().'</a></td><td>'.$asset->getType().'</td><td><a href="/account/view?i='.$owner->getID().'">'.$owner->getName().'</a></td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
        </div>
        <?php require '../res/incl/footer.php'; ?>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/courseFunctions.js"></script>
        <script src="/res/js/menubar.js"></script>
        <script>
            new MenuBar(document.getElementById("menubar_picker"), [
                    document.getElementById("contents"),
                    document.getElementById("announcements"),
                    document.getElementById("assignments"),
                    document.getElementById("surveys"),
                    document.getElementById("assets")
                ]);
        </script>
    </body>
</html>