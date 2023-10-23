<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/document.php';

    $u = new User();

    if($u->restoreFromSession()) {

        $doc = new Document();
        $doc->fromID($_POST['id']);

        $doc->remove($u);
    }

?>