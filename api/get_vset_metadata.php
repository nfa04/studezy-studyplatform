<?php

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {

        require '../res/incl/classes/vocabularyset.php';

        $vset = new VocabularySet();
        $vset->fromID($_POST['id']);

        if(!$_POST['includeUserData'] OR !isset($_POST['includeUserData'])) echo json_encode(array(
            'name' => $vset->getName(),
            'description' => $vset->getDescription()
        ));
        else {
            echo json_encode(array(
                'name' => $vset->getName(),
                'description' => $vset->getDescription(),
                'wordCount' => $vset->countWords(),
                'scorePercentage' => $vset->getScorePercentage($u)
            ));
        }

    }

?>