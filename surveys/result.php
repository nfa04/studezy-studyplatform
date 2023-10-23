<?php
    require '../res/incl/classes/user.php';
    require_once '../res/incl/classes/survey.php';

    $u = new User();
    $u->restoreFromSession(true);

    $survey = new Survey();
    $survey->fromID($_GET['s']);

    $as = new SurveyAnswerSet();
    $as->fromID($_GET['i']);
    $answers = $as->getAnswers();

    $questions = $survey->getQuestions();

?><!DOCTYPE html>
<html>
    <head>
        <title>Result | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Result</h1>
        <p>Answer by <a href="/account/view?i=<?php $user = $as->getUser(); echo $user->getID(); ?>"><?php echo $user->getName(); ?></a>, submitted on <?php echo $as->getSubmittedDate(); ?></p>
        <h2>Answers</h2>
        <?php
            $i = 0;
            foreach($questions AS $question) {
                echo '<div>
                <h3>'.$question->getContent().'</h3>';
                if($question->getType() == 'mc-checkbox' OR $question->getType() == 'mc-radio') {
                    $options = $question->getOptions();
                    echo '<fieldset>';
                    $index = 0;
                    foreach($options AS $option) {
                        $random_id = uniqid();
                        echo '<div><input type="checkbox" id="'.$random_id.'"'.(intval($answers[$i]['content']) == $index ? ' checked ' : '').'disabled><label for="'.$random_id.'">'.$option.'</label></div>';
                        $index++;
                    }
                    echo '</fieldset>';
                } else {
                    echo '<p><b>Answer:</b> '.$answers[$i]['content'].'</p>';
                }
                echo ' </div>';
                $i++;
            }
        ?>  
    </body>
</html>