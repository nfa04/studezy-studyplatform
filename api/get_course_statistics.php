<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $course = new Course();
        $course->fromID($_POST['id'], $u);

        $chaps = $course->getContents();
        
        echo json_encode(array_map(function($chap) {
            $cp = $chap->getOverallProgressInfo();
            return array(
                'id' => $chap->getID(),
                'progress' => ($cp['AVG(progress)'] == null ? 0 : $cp['AVG(progress)'] * 100),
                'stars' => ($cp['AVG(stars)'] == null ? 0 : $cp['AVG(stars)'])
            );
        }, $chaps));
    }
?>