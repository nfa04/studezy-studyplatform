<?php 

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/vocabularyset.php';
    
    $u = new User();
    $u->restoreFromSession(true);

    $vset = new VocabularySet();
    $vset->fromID($_GET['i']);

    $words = $vset->getWords();

?><!DOCTYPE html>
<html>
    <head>
        <title>Edit vocabulary set | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
        <link rel="stylesheet" href="/res/css/vset.css">
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Edit vocabulary set</h1>
        <form id="vocabulary_container" method="post" action="save">
            <div>
                <input type="text" placeholder="Set title" value="<?php echo $vset->getName(); ?>" name="name">
            </div>
            <div>Description
                <input type="text" placeholder="Description" name="description" value="<?php echo $vset->getDescription(); ?>">
            </div>
            <?php
                foreach($words AS $word) {
                    echo '<div class="word_container">
                        <input type="text" placeholder="Word" name="v['.$word['word_id'].'][word]" value="'.$word['word'].'">
                        <input type="text" placeholder="Definition" name="v['.$word['word_id'].'][definition]" value="'.$word['definition'].'">
                    </div>';
                } 
            ?>
            <div class="word_container add_container" id="add_container">
            <a href="javascript:;" onclick="addWord()">
                <img src="/res/img/plus.svg"> Add word
            </a>
            </div>
            <input type="submit" value="Save">
            <input type="hidden" name="sid" value="<?php echo $_GET['i']; ?>">
        </form>
        <script src="/res/js/vocabularyCreation.js"></script>
    </body>
</html>