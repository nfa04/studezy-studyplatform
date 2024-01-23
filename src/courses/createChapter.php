<?php
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $c = new Course();
    $c->fromID($_GET['i'], $u);

    $chap = $c->addEmptyChapter($_POST['name'], $u);

    if($chap !== false) header('Location: edit?i='.$chap->getID().'&cid='.$c->getID().'&type=chapter');
    else echo 'Exists';
?>