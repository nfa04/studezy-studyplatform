<?php
    require '../res/incl/classes/user.php';
    require '../res/incl/classes/survey.php';

    $u = new User();
    $u->restoreFromSession(true);

    $survey = new Survey();
    $survey->fromID($_GET['i']);

?><!DOCTYPE html>
<html>
    <head>
        <title>Edit "<?php echo $survey->getTitle(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Edit Survey: "<?php echo $survey->getTitle(); ?>"</h1>
        <form id="questions" action="save" method="post">
            <input type="hidden" value="<?php echo $_GET['i']; ?>" name="survey_id">
            <?php
                $questions = $survey->getQuestions();
                foreach($questions AS $question) {
                    $type = $question->getType();
                    echo '<div id="question_'.$question->getID().'" class="question">
                        <div><input type="text" value="'.$question->getContent().'" name="questions['.$question->getID().'][content]"><br><textarea name="questions['.$question->getID().'][description]">'.$question->getDescription().'</textarea></div>
                        <div>
                            <select name="questions['.$question->getID().'][type]" class="question_options">
                                <option value="text"'.($type == 'text' ? ' selected' : '').'>Text input</option>
                                <option value="mc-checkbox"'.($type == 'mc-checkbox' ? ' selected' : '').'>Multiple-choice: choose multiple</option>
                                <option value="mc-radio"'.($type == 'mc-radio' ? ' selected' : '').'>Multiple choice: choose one</option>
                                <option value="number"'.($type == 'number' ? ' selected' : '').'>Number</option>
                                <option value="range"'.($type == 'range' ? ' selected' : '').'>Range (between to numbers)</option>
                                <option value="date"'.($type == 'date' ? ' selected' : '').'>Date</option>
                                <option value="time"'.($type == 'time' ? ' selected' : '').'>Time</option>
                            </select>
                        </div>
                        <div class="question_args">';
                        $options = $question->getOptions();
                        if(!empty($options)) {
                            if($question->getType() == 'mc-checkbox' OR $question->getType() == 'mc-radio') {
                                echo '<ul>';
                                $i = 0;
                                foreach($options AS $option) {
                                    echo '<li class="question_option"><input type="text" name="questions['.$question->getID().'][options]['.$i.']" value="'.$option.'"></li>';
                                    $i++;
                                }
                                echo '</ul><input type="button" class="question_addoption" value="Add option">';
                            }
                            else if($question->getType() == 'range') {
                                echo 'From <input type="number" name="questions['.$question->getID().'][options][0]" value="'.$options[0].'"> to <input type="number" name="questions['.$question->getID().'][options][1]" value="'.$options[1].'">';
                            }
                        }
                        echo '</div>
                    </div>';
                }
            ?>
            <div id="survey_controls"><input type="button" value="Add question" onclick="addQuestion()"><input type="submit" value="Save & close"></div>
        </form>
        <script src="../res/js/surveyEditor.js"></script>
    </body>
</html>