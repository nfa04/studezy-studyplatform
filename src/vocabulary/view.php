<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/vocabularyset.php';

    $u = new User();
    $u->restoreFromSession(true);

    $vset = new VocabularySet();
    $vset->fromID($_GET['i']);

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Vocabularyset "<?php echo $vset->getName(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
        <link rel="stylesheet" href="/res/css/vset.css">
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Vocabularyset "<?php echo $vset->getName(); ?>"</h1>
        <?php
            if($vset->hasWriteAccess($u)) {
        ?>
            <div style="float:right"><a href="edit?i=<?php echo $_GET['i']; ?>"><img src="/res/img/edit.svg" height="20"> Edit</a></div>
        <?php } ?>
        <div>By <?php echo $vset->getUser()->getName(); ?></div>
        <p><?php echo $vset->getDescription(); ?></p>
        <div class="action_bar">
            <div>
                <a href="flashcards?i=<?php echo $vset->getID(); ?>">
                    <img src="/res/img/flashcards.svg">
                    Flashcards
                </a>
            </div>
            <div>
                <a href="learn?i=<?php echo $vset->getID(); ?>">
                <img src="/res/img/learn.svg">
                Learn
                </a>
            </div>
        </div>
        <h2>Words</h2>
        <div>
            <?php
                $words = $vset->getWords();
                foreach($words AS $word) {
                    echo '<div class="word_container"><div class="word_word">'.$word['word'].'</div><div class="word_definition">'.$word['definition'].'</div></div>';
                }
            ?>
        </div>
    </body>
</html>