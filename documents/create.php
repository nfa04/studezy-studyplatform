<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/document.php';

    $u = new User();
    if($u->restoreFromSession()) {
        $doc = new Document();
        $doc->createEmpty($u);
        header('Location: edit?i='.$doc->getID());
    }

?>