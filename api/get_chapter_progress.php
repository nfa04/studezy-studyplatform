<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $chapter = new Chapter();
        $chapter->fromID($_POST['chid']);

        $prog = $chapter->getProgress($u);
        echo json_encode(array(
            'progress' => $prog->getProgress(),
            'stars' => $prog->getStars()
        ));
    }

?>