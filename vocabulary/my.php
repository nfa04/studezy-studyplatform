<?php
    require '../res/incl/classes/user.php';
    require '../res/incl/classes/vocabularyset.php';

    $u = new User();
    $u->restoreFromSession(true);

?><!DOCTYPE html>
<html>
    <head>
        <title>Your Vocabularysets | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Your Vocabularysets</h1>
        <table>
        <?php
            $sets = $u->getVocabularySets();
            foreach($sets AS $set) {
                echo '<tr><td><img src="/res/img/flashcards.svg"></td><td><a href="view?i='.$set->getID().'">'.$set->getName().'</a></td></tr>';
            }
        ?>
        </table>
    </body>
</html>