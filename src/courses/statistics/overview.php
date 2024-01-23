<?php

    require '../../res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);

    $c = new Course();
    $c->fromID($_GET['i'], $u);

?><!DOCTYPE html>
<html>
    <head>
        <title>Course Statistics | StudEzy</title>
        <?php require '../../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../../res/incl/nav.php'; ?>
        <h1>Course statistics</h1>
        <div>Statistics on: "<a href="/courses/view?i=<?php echo $c->getID(); ?>"><?php echo $c->getName(); ?></a>"</div>
        <div>
             <?php

                $chapters = $c->getContents();
                
                foreach($chapters AS $chap) {
                    echo '<div style="display:flex">
                            <div style="display:inline-block;margin:40px">
                                <canvas id="'.$chap->getID().'_progress" width="300" height="100"></canvas><br>
                                <canvas id="'.$chap->getID().'_stars" width="300" height="100"></canvas>
                            </div>
                            <div style="display:inline-block;flex-grow:1">
                                <h3>'.$chap->getName().' - Leaderboard</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <td>User</td>
                                            <td>Progress [%]</td>
                                            <td>Stars</td>
                                        </tr>
                                    </thead>
                                    <tbody>';
                                    $leaderBoard = $chap->getProgressLeaderBoard();
                                    foreach($leaderBoard AS $dataset) {
                                        echo '<tr>
                                            <td><a href="/account/view?i=',$dataset['user']->getID(),'">',$dataset['user']->getName(),'</a></td>
                                            <td>',$dataset['progress'] * 100,'</td>
                                            <td>',$dataset['stars'],'</td>
                                            </tr>';
                                    }
                                echo '</tbody></table>
                            </div>
                        </div>';
                }

             ?>
        </div>
        <script src="/res/js/core.js"></script>
        <script src="/res/js/graphs.js"></script>
        <script src="/res/js/courseStatistics.js"></script>
    </body>
</html>