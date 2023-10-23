<?php
require '../res/incl/classes/user.php';
$u = new User();
$u->restoreFromSession(true);

$chap = new Chapter();
$chap->fromID($_GET['i']);

header('Access-Control-Allow-Origin: '.DOCSERVER_REMOTE_LOCATION);

if($chap->getCourse($u)->hasWriteAccess($u)) {

?><!DOCTYPE html>
<html>
    <head>
        <title>Editor | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
        <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/monokai-sublime.min.css" rel="stylesheet">
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <?php require '../res/incl/fonts.php'; ?>
        <link href="/res/css/editorfonts.css" rel="stylesheet">
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <form>
            <div class="editor-items">
                <input type="text" name="chapter-name" id="chapter_name" placeholder="Chapter name" value="<?php echo $chap->getName(); ?>">
                <input type="button" id="publish_btn" value="Publish changes">
            </div>
            <div id="content" contenteditable="true"><?php // echo $chap->getContent($u); ?></div>
        </form>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/fileOperations.js"></script>
        <script src="/res/js/fileSelector.js"></script>
        <script src="/res/js/surveySelector.js"></script>
        <script src="/res/js/assignmentSelector.js"></script>
        <script src="/res/js/announcementSelector.js"></script>
        <script src="/res/js/chatSelector.js"></script>
        <script src="/res/lib/socket.io/socket.io.js"></script>
        <script src="/res/js/editor.js"></script>
    </body>
</html><?php } ?>