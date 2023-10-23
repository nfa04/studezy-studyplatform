<nav id="nav">
    <span id="site_name"><a href="/">StudEzy</a></span>
    <span>
        <form action="/search" method="get">
            <input type="text" name="q" id="sinput" placeholder="Search">
            <input type="submit" value="Search" height="15">
        </form>
    </span>
    <span>
        <a href="/"><img src="/res/img/home.svg"> Start</a>
        <a href="/account/view?i=<?php echo $u->getID(); // Not very clever, but it works... ?>"><img src="/res/img/person.svg"> Account</a>
        <a href="/documents/my"><img src="/res/img/document.svg"> Documents</a>
        <a href="/vocabulary/my"><img src="/res/img/flashcards.svg"> Vocabulary</a>
        <a href="/messages/chat"><img src="/res/img/chat.svg"> Messages</a>
        <a href="/calendars/my?m=m"><img src="/res/img/calendar.svg"> Calendar</a>
        <a href="/signout"><img src="/res/img/logout.svg"> Sign out</a>
    </span>
</nav>