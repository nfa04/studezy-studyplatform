<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/vocabularyset.php';

    $u = new User();
    $u->restoreFromSession(true);

    $vset = new VocabularySet();
    $vset->fromID($_POST['sid']);

    $vset->setDescription($_POST['description']);
    $vset->setName($_POST['name']);

    $vset->saveFromDataObject($u, $_POST['v']);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Vocabulary saved | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Saved your set!</h1>
        <p>We successfully saved your changes to the vocabulary set.</p>
        <div>
            <a href="view?i=<?php echo $_POST['sid']; ?>"><input type="button" value="Back"></a>
        </div>
    </body>
</html>