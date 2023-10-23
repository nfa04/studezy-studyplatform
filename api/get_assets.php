<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    if($u->restoreFromSession()) echo $u->getAssetsAsJSON(json_decode($_POST['fileTypes']));

?>