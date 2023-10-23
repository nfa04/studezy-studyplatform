<?php

    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Create a survey | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Create a survey</h1>
        <form action="creation" method="post">
            <input type="text" name="name" placeholder="Survey name">
            <textarea name="desc" placeholder="Description"></textarea>
            <input type="hidden" name="cid" value="<?php echo $_GET['i']; ?>">
            <!--<input type="checkbox" id="show_answers" name="sa"> <label for="show_answers">Show answers</label>-->
            <input type="submit" value="Create">
        </form>
    </body>
</html>