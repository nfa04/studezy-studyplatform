<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/calendar.php';

    $u = new User();
    $u->restoreFromSession(true);

    $evt = new CalendarEntry();
    $evt->fromID($_GET['i']);

    $cmanager = $evt->getCalendarManager();

?><!DOCTYPE html>
<html>
    <head>
        <title>"<?php echo $evt->getTitle(); ?>" Event | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>"<?php echo $evt->getTitle(); ?>" Event</h1>
        <h2>Event description</h2>
        <p><?php echo $evt->getDescription(); ?></p>
        <h2>Details</h2>
        <table>
            <tbody>
                <tr>
                    <td>Imported from</td>
                    <td><a href="view?i=<?php echo $cmanager->getID(); ?>"><?php echo $cmanager->getTitle(); ?></a></td>
                </tr>
                <tr>
                    <td>Event owner</td>
                    <td><a href="/account/view?i=<?php $owner = $evt->getOwner(); echo $owner->getID(); ?>"><?php echo $owner->getName(); ?></a></td>
                </tr>
                <tr>
                    <td>Scheduled</td>
                    <td><?php echo $evt->getDateTime(); ?></td>
                </tr>
                <tr>
                    <td>Until</td>
                    <td><?php echo $evt->getEndDateTime(); ?></td>
                </tr>
            </tbody>
        </table>
        <?php if($evt->hasWriteAccess($u)) { ?>
        <div class="danger_area">
            <a href="removeEvent?i=<?php echo $evt->getID(); ?>"><img src="/res/img/delete.svg" style="height:15px"> Remove entry</a>
        </div>
        <?php } ?>
    </body>
</html>