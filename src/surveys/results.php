<?php
    require '../res/incl/classes/user.php';
    require_once '../res/incl/classes/survey.php';
    
    $u = new User();
    $u->restoreFromSession(true);

    $survey = new Survey();
    $survey->fromID($_GET['i']);

    if($survey->hasWriteAccess($u)) {
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Results of survey: "<?php echo $survey->getTitle(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Results of survey: "<?php echo $survey->getTitle(); ?>"</h1>
        <p>by <a href="/account/view?i=<?php $owner = $survey->getOwner(); echo $owner->getID(); ?>"><?php echo $owner->getName(); ?></a> in <a href="/courses/view?i=<?php $course = $survey->getCourse($u); echo $course->getID(); ?>"><?php echo $course->getName(); ?></a></p>
        <h2>Latest answers</h2>
        <table>
            <thead>
                <tr>
                    <td>#</td>
                    <td>User</td>
                    <td>Submitted</td>
                </tr>
            </thead>
            <tbody>
                <?php
                    $answerData = $survey->getAnswerSets();
                    $i = 0;
                    foreach($answerData AS $answerset) {
                        echo '<tr><td>'.$i.'</td><td><a href="result?s='.$survey->getID().'&i='.$answerset->getID().'">'.$answerset->getUser()->getName().'</a></td><td>'.$answerset->getSubmittedDate().'</td></tr>';
                        $i++;
                    }
                ?>
            </tbody>
        </table>
        <!-- <h2>View</h2>
        <div>
            <span>
                Filter by
            </span>
            <select name="result_mode" id="result_mode">
                <option value="all">
                    No filter
                </option>
                <option value="answer">
                    By answer
                </option>
            </select>
            <span id="question_selectors"> in 
                <select name="question_selected" id="question_selected">
                    <?php
                        $questions = $survey->getQuestions();
                        foreach($questions AS $question) {
                            echo '<option value="'.$question->getID().'">'.$question->getContent().'</option>';
                        }
                    ?>
                </select>
            </span>
            <span>
                look for
                <span id="query_selector"><input type="text" id="query_input"></span>
            </span>
            <span>
                <input type="button" onclick="loadData()" value="Go">
            </span>
        </div>-->
        <!--<h2>Results</h2>
        <div id="result_container">
            <?php
                foreach($questions AS $question) {
                    echo '<div id="question_'.$question->getID().'">
                        <h3>'.$question->getContent().'</h3>
                        <div style="overflow:auto" class="question_results" id="'.$question->getID().'">
                            <div style="float:left"><canvas></canvas><br>
                            <select>
                                <option value="0">Piechart</option>
                                <option value="1">Barchart</option>
                            </select></div>
                            <table>
                                <thead>
                                    <tr>
                                        <td>Answer</td>
                                        <td>Frequency</td>
                                    </tr>
                                </thead>
                                <tbody><tbody>
                            </table>
                            <div>
                                <a href="question?i='.$question->getID().'">See all answers</a>
                            </div>
                        </div>
                    </div>';
                }
            ?>
        </div>-->
        <script src="../res/js/core.js"></script>
        <script src="../res/js/graphs.js"></script>
        <script src="../res/js/surveyResults.js"></script>
    </body>
</html><?php } ?>