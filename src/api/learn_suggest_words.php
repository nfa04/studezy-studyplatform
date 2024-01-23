<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/vocabularyset.php';

    $u = new User();

    if($u->restoreFromSession()) {

        $vset = new VocabularySet();
        $vset->fromID($_POST['id']);

        $scores = json_decode($_POST['scores'], true);

        if($scores !== null) $vset->applyScoreDelta($u, $scores);

        echo json_encode($vset->suggestNextWords($u));
    }

?>