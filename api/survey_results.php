<?php
    header('Content-Type: application/json');

    require '../res/incl/classes/user.php';
    require_once '../res/incl/classes/survey.php';

    $u = new User();
    if($u->restoreFromSession()) {
        $survey = new Survey();
        if($survey->fromID($_POST['sid']) AND $survey->hasWriteAccess($u)) {
            switch($_POST['mode']) {
                case 'all':
                    $arr = array();
                    $questions = $survey->getQuestions();
                    foreach($questions AS $question) {
                        $ans = $question->getAnswersCount();
                        foreach($ans AS $key=>$answer) {
                            $arr[$question->getID()][$key] = $answer;
                        }
                    }
                    echo json_encode($arr);
                    break;
            }
        }

    }
?>
