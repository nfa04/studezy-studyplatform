<?php

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {

        require '../res/incl/classes/survey.php';

        $survey = new Survey();
        $survey->fromID($_POST['id']);

        echo json_encode(array(
            'name' => $survey->getTitle(),
            'description' => $survey->getDescription(),
            'owner' => $survey->getOwner()->getName()
        ));

    }

?>