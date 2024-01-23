<?php

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {

        require '../res/incl/classes/vocabularyset.php';

        $vset = new VocabularySet();
        $vset->fromDataObject(array(
            'id' => uniqid(),
            'name' => 'New vocabulary',
            'description' => '',
            'user' => $u->getID(),
            'course' => (isset($_GET['i']) ? $_GET['i'] : null)
        ));
        $vset->create();

        header('Location: edit?i='.$vset->getID());
    }

?>