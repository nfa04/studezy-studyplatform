<?php

    // This file is almost identical to my.php but doesn't assume a calendar
    
    require '../res/incl/classes/user.php';
    require '../res/incl/classes/calendar.php';

    $u = new User();
    $u->restoreFromSession(true);

    $cmanager = new CalendarManager();
    $cmanager->fromID($_GET['i']);

    define('MONTHS', array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'));

?><!DOCTYPE html>
<html>
    <head>
        <title>Calendar "<?php echo $cmanager->getTitle(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
        <link rel="stylesheet" href="/res/css/calendar.css">
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Calendar "<?php echo $cmanager->getTitle(); ?>"</h1>
        <p><?php echo $cmanager->getDescription(); ?></p>
        <details>
            <summary>Show subscriptions</summary>
            <div>
                <?php
                    $subs = $cmanager->getSubscriptions();
                    foreach($subs AS $sub) {
                        $parent = $sub->getParentCalendarManager();
                        echo '<div><a href="view?i=', $parent->getID(), '&m=', $_GET['m'],'">', $parent->getTitle() ,'</a></div>';
                    }
                ?>
            </div>
        </details>
        <div class="calendar_mode_selectors">
            <?php
                    switch($_GET['m']) {
                        case 'm':
                            $dformat = 'Y-m';
                            break;
                        case 'd':
                            $dformat = 'Y-m-d';
                            break;
                        case 'y':
                            $dformat = 'Y';
                            break;
                    }
            ?>
            <a href="?i=<?php echo $_GET['i']; ?>&m=<?php echo $_GET['m']; ?>&t=<?php $t = explode('-', (isset($_GET['t']) ? $_GET['t'] : date($dformat)));  $t[count($t) - 1]--; echo implode('-', $t); ?>"><img src="/res/img/arrow_back.svg"></a>
            <a href="?i=<?php echo $_GET['i']; ?>&m=<?php echo $_GET['m']; ?>&t=<?php $t = explode('-', (isset($_GET['t']) ? $_GET['t'] : date($dformat)));  $t[count($t) - 1]++; echo implode('-', $t); ?>"><img src="/res/img/arrow_forward.svg"></a>
            <a href="?m=d&i=<?php echo $_GET['i']; ?>"<?php echo ($_GET['m'] == 'd' ? ' style="background-color:var(--theme-main-color);color:white"': '') ?>>Day</a>
            <a href="?m=m&i=<?php echo $_GET['i']; ?>"<?php echo ($_GET['m'] == 'm' ? ' style="background-color:var(--theme-main-color);color:white"': '') ?>>Month</a>
            <a href="?m=y&i=<?php echo $_GET['i']; ?>"<?php echo ($_GET['m'] == 'y' ? ' style="background-color:var(--theme-main-color);color:white"': '') ?>>Year</a>
        </div>
        <h3><?php echo (isset($_GET['t']) ? $_GET['t'] : date($dformat)); ?></h3>
        <table>
            <tbody>
                <?php
                    switch($_GET['m']) {
                        case 'm':

                            // Calculate first and last day of the month
                            $firstday = (isset($_GET['t']) ? $_GET['t'].'-01' : date('Y-m-01'));
                            $date = new DateTime($firstday);
                            $date->modify('last day of this month');
                            $lastday = $date->format('Y-m-d');
                            $length = round((strtotime($lastday) - strtotime($firstday)) / 86400);

                            // Find all entries for this month
                            $entries = $cmanager->getEntries($firstday, $lastday, true, false);

                            $dcounter = 0;
                            for($i = 0; $i < ceil($length / 7); $i++) {
                                echo '<tr>';
                                
                                for($c = 0; $c < 7; $c++) {
                                    if($dcounter <= $length) {
                                        echo '<td>
                                                <div class="date_indicator">'.($dcounter + 1).'.</div>';
                                        $events = array_filter($entries, function($entry) use ($firstday, $dcounter) {
                                            return date("d.m.Y", strtotime($entry->getDateTime())) == date("d.m.Y", strtotime($firstday) + $dcounter * 86400);
                                        });

                                        foreach($events AS $event) {
                                            $owner = $event->getOwner();
                                            echo '<div><a href="event?i='.$event->getID().'">'.date("H:i", strtotime($event->getDateTime())).': '.$event->getTitle().($u->isUser($owner) ? '' : ' ('.$owner->getName().')').'</a></div>';
                                        }

                                        echo '</td>';
                                    }
                                    $dcounter++;
                                }

                                echo '</tr>';
                            }
                            
                            break;
                        case 'd':
                            // Find entries of selected day
                            $today = (isset($_GET['t']) ? $_GET['t'] : date("Y-m-d"));
                            $entries = $cmanager->getEntries($today, $today.' 23:59:00', true, false);
                            
                            echo '<tr><td><div class="date_indicator">'.$today.'</div></td></tr>';

                            foreach($entries AS $entry) {
                                echo '<tr><td><a href="event?i='.$entry->getID().'">'.date("H:i", strtotime($entry->getDateTime())).': '.$entry->getTitle().'</a></td></tr>';
                            }
                            break;
                        case 'y':
                            $year = (isset($_GET['t']) ? $_GET['t'] : date("Y"));
                            $entries = $cmanager->getEntries($year.'-01-01 00:00', $year.'-12-31 00:00', true, false);

                            for($i = 0; $i < 3; $i++) {
                                echo '<tr>';
                                for($c = 0; $c < 4; $c++) {
                                    $month = $i * 4 + $c + 1;
                                    $events = array_filter($entries, function($entry) use ($month) {
                                        return date("m", strtotime($entry->getDateTime())) == $month;
                                    });
                                    echo '<td><div class="date_indicator">', MONTHS[$month - 1], '</div>';
                                    if(count($events) > 4) echo '<a href="view?i=', $cmanager->getID(), '&m=m&t=', $year, '-', $month, '">', count($events), ' events... (see more)</a>';
                                    else foreach($events AS $event) {
                                        echo '<div>'.date("d. H:i", strtotime($event->getDateTime())).': '.$event->getTitle().'</div>';
                                    }
                                    echo '</td>';
                                }
                                echo '</tr>';
                            }
                            break;
                    }
                ?>
            </tbody>
        </table>
        <?php if(isset($_GET['cid'])) { 
            $course = new Course();
            $course->fromID($_GET['cid'], $u);
            if($course->hasWriteAccess($u) AND $course->getCalendarManager()->getID() == $cmanager->getID()) {
        ?>
        <div class="calendar_mode_selectors">
            <a href="createEvent?i=<?php echo $cmanager->getID(); ?>"><img src="/res/img/plus.svg"> New calendar entry</a>
        </div>
        <?php } } ?>
    </body>
</html>