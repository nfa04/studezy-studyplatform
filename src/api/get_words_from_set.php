<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/vocabularyset.php';

    $u = new User();

    if($u->restoreFromSession()) {

        $vset = new VocabularySet();
        $vset->fromID($_POST['id']);

        echo json_encode($vset->getWords());
    }

?>