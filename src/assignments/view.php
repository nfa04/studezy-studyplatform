<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $a = new Assignment();
    $a->fromID($_GET['i']);

    header('Cross-Origin-Embedder-Policy: require-corp');
    header('Cross-Origin-Opener-Policy: same-origin');

?><!DOCTYPE html>
<html>
    <head>
        <title>Assignment "<?php echo $a->getTitle(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Assignment "<?php echo $a->getTitle(); ?>"</h1>
        <div>by <a href="/account/view?i=<?php $aOwner = $a->getOwner(); echo $aOwner->getID(); ?>"><?php echo $aOwner->getName(); ?></a></div>
        <?php if($a->hasWriteAccess($u)) { ?>
        <p>You have write access to this assignment. <a href="results?i=<?php echo $a->getID(); ?>">See results</a></p>
        <?php } ?>
        <p><?php echo $a->getDescription(); ?></p>
        <div>
            <ul>
            <?php
                $submitted = $a->getSubmissionsByUser($u);
                foreach($submitted AS $as) {
                    echo '<li><a href="'.$as->getFilePath().'">'.$as->getFileName().'</a></li>';
                }
            ?>
            </ul>
            <input type="hidden" name="aid" value="<?php echo $a->getID(); ?>">
            <div id="file_input_container">
                <div><input type="file" id="asset" class="fileInput"></div>
                <input type="button" value="Add file" onclick="addFile(this)">
            </div>
            <div>
                <span id="processing_file_name"></span>
                <span id="progress"></span>
                <span id="percent"></span>
            </div>
            <input type="button" value="Submit answer" onclick="submitAssignment()">
        </div>
        <script src="//cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.0/dist/ffmpeg.min.js"></script>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/fileOperations.js"></script>
        <script src="/res/js/assignmentSubmit.js"></script>
    </body>
</html>