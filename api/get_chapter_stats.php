<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $chapter = new Chapter();
    $chapter->fromID($_POST['id']);

    $cp = $chapter->getOverallProgressInfo();

    echo json_encode(array(
        'progress' => ($cp['AVG(progress)'] == null ? 0 : $cp['AVG(progress)']),
        'stars' => ($cp['AVG(stars)'] == null ? 0 : $cp['AVG(stars)'])
    ));
    
?>