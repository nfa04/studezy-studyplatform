<?php
    require '../res/incl/classes/user.php';
    require_once '../res/incl/classes/survey.php';
    
    $u = new User();
    $u->restoreFromSession(true);

    $survey = new Survey();
    $survey->fromID($_GET['i']);

?><!DOCTYPE html>
<html>
    <head>
        <title>Survey "<?php echo $survey->getTitle(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Survey: "<?php echo $survey->getTitle(); ?>"</h1>
        <p>By <a href="/accounts/view?i=<?php $owner = $survey->getOwner(); echo $owner->getID() ?>"><?php echo $owner->getName(); ?></a> in "<a href="/courses/view?i=<?php $course = $survey->getCourse($u); echo $course->getID(); ?>"><?php echo $course->getName(); ?></a>"</p>
        <?php if($survey->hasWriteAccess($u)) {
            echo '<p>You have write access to this survey. You can <a href="results?i='.$survey->getID().'">View results</a> or <a href="edit?i='.$survey->getID().'">Edit</a>';
        } ?>
        <h2>Questions</h2>
        <form action="submit" method="post">
        <input type="hidden" name="sid" value="<?php echo $survey->getID(); ?>">
        <?php
            $questions = $survey->getQuestions();
            $i = 1;
            foreach($questions AS $question) {
                echo '<h3>'.$i.': '.$question->getContent().'</h3><fieldset>'.($question->getDescription() == '' ? '' : '<legend>'.$question->getDescription().'</legend>');
                switch($question->getType()) {
                    case 'text':
                        echo '<input type="text" name="answers['.$question->getID().']">';
                        break;
                    case 'mc-checkbox':
                        $options = $question->getOptions();
                        $oi = 0;
                        foreach($options AS $option) {
                            echo '<div><input type="checkbox" id="'.$question->getID().'_'.$oi.'" name="answers['.$question->getID().']['.$oi.']"><label for="'.$question->getID().'_'.$oi.'">'.$option.'</label></div>';
                            $oi++;
                        }
                        break;
                    case 'mc-radio':
                        $options = $question->getOptions();
                        $oi = 0;
                        foreach($options AS $option) {
                            echo '<div><input type="radio" id="'.$question->getID().'_'.$oi.'" name="answers['.$question->getID().']" value="'.$oi.'"><label for="'.$question->getID().'_'.$oi.'">'.$option.'</label></div>';
                            $oi++;
                        }
                        break;
                    case 'number':
                        echo '<input type="number" name="answers['.$question->getID().']">';
                        break;
                    case 'range':
                        $range = $question->getOptions();
                        echo '<input type="range" id="'.$question->getID().'" name="answers['.$question->getID().']" min="'.$range[0].'" max="'.$range[1].'"><br><label for="'.$question->getID().'">Input between '.$range[0].' & '.$range[1].'</label>';
                        break;
                    case 'date':
                        echo '<input type="date" name="answers['.$question->getID().']">';
                        break;
                    case 'time':
                        echo '<input type="time" name="answers['.$question->getID().']">';
                        break;
                    }
                echo '</fieldset>';
                $i++;
            }
        ?>
        <br>
        <div>
            <input type="submit">
        </div>
        </form>
    </body>
</html>