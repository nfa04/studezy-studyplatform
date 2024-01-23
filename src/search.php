<?php
    require 'res/incl/classes/user.php';

    $u = new User();
    $u->restoreFromSession(true);
?><!DOCTYPE html>
<html>
    <head>
        <title>"<?php echo $_GET['q'] ?>" Search Results | StudEzy</title>
        <?php require 'res/incl/head.php'; ?>
    </head>
    <body>
        <?php require 'res/incl/nav.php'; ?>
        <h1>Search results</h1>
        <p>Results for: "<?php echo $_GET['q']; ?>"</p>
        <div>
            <?php
                // Run a query to find courses that start with the user input
                $sql = 'SELECT * FROM courses WHERE name LIKE :partialName';
                $query = DB_CONNECTION->prepare($sql);
                $query->execute(array(
                    'partialName' => $_GET['q'].'%'
                ));
                $courses = $query->fetchAll();
                foreach($courses AS $course) {
                    echo '<div><h2><a href="/courses/view?i='.$course['id'].'">'.$course['name'].'</a></h2><div>'.$course['description'].'</div></div>';
                }
            ?>
        </div>
    </body>
</html>