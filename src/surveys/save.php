<?php
    require '../res/incl/classes/user.php';
    require_once '../res/incl/classes/survey.php';

    $u = new User();
    $u->restoreFromSession(true);

    $survey = new Survey();
    $survey->fromID($_POST['survey_id']);

    if($survey->storeQuestionsFromDataObject($_POST['questions'], $u)) {

?><!DOCTYPE html>
<html>
    <head>
        <title>Saved survey | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Your survey was saved successfully.</h1>
        <p>Your survey "<?php echo $survey->getTitle(); ?>" was saved successfully.</p>
        <div>
            <a href="javascript:;" onclick="history.go(-2)"><input type="button" value="Continue"></a>
        </div>
    </body>
</html><?php } ?>