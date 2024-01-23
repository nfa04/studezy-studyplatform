<?php
    require '../res/incl/classes/user.php';
    require_once '../res/incl/classes/survey.php';

    $u = new User();
    $u->restoreFromSession(true);

    $survey = new Survey();
    $survey->fromID($_POST['sid']);

    $answers = array();

    $questions = $survey->getQuestions();

    foreach($questions AS $question) {
        if($question->getType() == 'mc-checkbox') {
            $max = count($question->getOptions());
            $arr = array();
            for($i = 0; $i < $max; $i++) {
                if(isset($_POST['answers'][$question->getID()][$i])) $arr[] = $i;
            }
            $answers[$question->getID()] = json_encode($arr);
        }
        else $answers[$question->getID()] = (isset($_POST['answers'][$question->getID()]) ? $_POST['answers'][$question->getID()] : 0);
    }

    if($survey->fill($answers, $u)) {

?><!DOCTYPE html>
<html>
    <head>
        <title>Survey submitted | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Survey "<?php echo $survey->getTitle(); ?>" submitted</h1>
        <p>You successfully submitted the survey "<?php echo $survey->getTitle(); ?>" by <?php echo $survey->getOwner()->getName(); ?>.</p>
        <div>
            <a href="javascript:history.go(-2);">
                <input type="button" value="Continue">
        </div>
    </body>
</html><?php } ?>