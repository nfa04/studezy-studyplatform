<?php
    
    require '../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $course = new Course();
    $course->fromID($_GET['cid'], $u);

?><!DOCTYPE html>
<html>
    <head>
        <title>Create an announcement | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Create a new announcement</h1>
        <div>in "<a href="/courses/view?i=<?php echo $course->getID(); ?>"><?php echo $course->getName(); ?></a>"</div><br>
        <form method="post" action="save">
            <input type="hidden" name="cid" value="<?php echo $course->getID(); ?>">
            <input type="text" placeholder="Title..." name="title"><br>
            <textarea placeholder="Announcement content..." name="content"></textarea><br>
            <input type="submit" value="Announce!">
        </form>
    </body>
</html>