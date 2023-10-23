<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/calendar.php';

    $u = new User();
    $u->restoreFromSession(true);
    
    $cmanager = new CalendarManager();
    $cmanager->fromID($_GET['i']);

?><!DOCTYPE html>
<html>
    <head>
        <title>Create an event | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Create an event</h1>
        <div>in "<?php echo $cmanager->getTitle(); ?>"</div>
        <form action="saveEvent" method="post">
            <input type="text" placeholder="Event title" name="title">
            <textarea name="description" placeholder="Event description..."></textarea>
            Event start: <input type="date" name="date_start"> at <input type="time" name="time_start"><br>
            Event end: <input type="date" name="date_end"> at <input type="time" name="time_end"><br>
            <input type="checkbox" id="private_checker" name="private" checked><label for="private_checker"> Make this event private (only you will be able to see it)</label>
            <input type="hidden" name="calendar_id" value="<?php echo $_GET['i']; ?>">
            <input type="submit" value="Create">
        </form>
    </body>
</html>