<?php
    require 'res/incl/nav.php';
?><h1>Welcome back, <?php echo $u->getName(); ?>!</h1>
<div>Create</div>
<div class="action_bar">
    <div><a href="/courses/create"><img src="/res/img/plus.svg"> Course</a></div>
    <div><a href="/documents/create"><img src="/res/img/document.svg"> Document</a></div>
    <div><a href="/vocabulary/create"><img src="/res/img/flashcards.svg"> Flashcards</a></div>
    <div><a href="/messages/chat"><img src="/res/img/chat.svg"> Chat</a></div>
</div>
<h2>Your courses</h2>
<div>
    <div class="course_list">
        <?php
            $courses = $u->findCourses();
            foreach($courses AS $c) {
                echo '<div class="course_item"><a href="/courses/view?i='.$c->getID().'"><div><h3>'.$c->getName().'</h3><div>'.$c->getDescription().'</div><div>by '.$c->getOwner()->getName().'</div></div></a></div>';
            }
        ?>
    </div>
</div>
<h2>Popular</h2>
<div class="course_list">
    <?php
        $courses = getPopularCourses();
        foreach($courses AS $c) {
            echo '<div class="course_item"><a href="/courses/view?i='.$c->getID().'"><div><h3>'.$c->getName().'</h3><div>'.$c->getDescription().'</div><div>by '.$c->getOwner()->getName().'</div></div></a></div>';
        }
    ?>
</div>
<h2>Discover</h2>
<div class="course_list">
    <?php
        $courses = $u->suggestCourses();
        foreach($courses AS $c) {
            echo '<div class="course_item"><a href="/courses/view?i='.$c->getID().'"><div><h3>'.$c->getName().'</h3><div>'.$c->getDescription().'</div><div>by '.$c->getOwner()->getName().'</div></div></a></div>';
        }
    ?>
</div>
<script src="/res/js/core.js"></script>
<script src="/res/js/main.js"></script>