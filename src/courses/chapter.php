<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $chap = new Chapter();
    $chap->fromID($_GET['i']);
    $c = $chap->getCourse($u);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>"<?php echo $chap->getName(); ?>" in "<?php echo $c->getName(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
        <link href="https://cdn.quilljs.com/1.3.6/quill.core.css" rel="stylesheet">
        <?php require '../res/incl/fonts.php'; ?>
        <link href="/res/lib/star-rating/css/star-rating.min.css" rel="stylesheet"/>
        <link href="/res/css/editorfonts.css" rel="stylesheet"/>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1><?php echo $chap->getName(); ?></h1>
        <p>from <a href="view?i=<?php echo $c->getID(); ?>">"<?php echo $c->getName(); ?>"</a> by <a href="/account/view?i=<?php $owner = $c->getOwner(); echo $owner->getID(); ?>"><?php echo $owner->getName(); ?></a></p>
        <div class="options_bar">
        <?php if($c->hasWriteAccess($u)) { ?>
            <a href="edit?i=<?php echo $chap->getID(); ?>&cid=<?php echo $c->getID(); ?>&type=chapter"><img src="/res/img/edit.svg"></a>
            <a href="statistics/chapter?i=<?php echo $chap->getID(); ?>" title="See statistics"><img src="/res/img/statistics.svg"></a>
        <?php } ?>
            <a href="javascript:;" onclick="sendChapter(getIDParam())"><img src="/res/img/send.svg"></a>
        </div>
        <hr>
        <div class="ql-editor">
        <?php
            $content = $chap->getContent($u);
            if($content !== false) echo $content;
            else echo 'This chapter doesn\'t have any published versions yet.';
        ?>
        </div>
        <div class="media_item progress_feedback">
            <h3>How well do you understand this topic?</h3>
            <div id="star_rating_description">Please rate your progress</div>
            <div style="margin-left:auto;margin-right:auto;width:fit-content">
                <select class="star-rating" id="star-rating" style="margin-left:500px !important">
                    <option value="">Select a rating</option>
                    <option value="5">Excellent</option>
                    <option value="4">Very Good</option>
                    <option value="3">Average</option>
                    <option value="2">Poor</option>
                    <option value="1">Terrible</option>
                </select>
        </div>
        </div>
        <div class="progress_bar" id="progress_bar">
            <div>Progress: <span id="progress_output"></span>%
            <div><progress id="progress_visualization" value="10" max="100"></div>
        </div>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/chatSelector.js"></script>
        <script src="/res/js/share.js"></script>
        <script src="/res/lib/star-rating/js/star-rating.min.js"></script>
        <script src="/res/js/chapterProgress.js"></script>
    </body>
</html>