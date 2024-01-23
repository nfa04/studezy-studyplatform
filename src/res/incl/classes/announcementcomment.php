<?php

    class AnnouncementComment {

        private $pdo;
        private $id;
        private $content;
        private $announcement;
        private $user;
        private $time;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->content = $obj['content'];
            $this->announcement = $obj['announcement'];
            $this->user = $obj['user'];
            $this->time = $obj['time'];
        }

        function getID() {
            return $this->id;
        }

        function getContent() {
            return $this->content;
        }

        function getAnnouncement() {
            $a = new Announcement();
            $a->fromID($this->announcement);
            return $a;
        }

        function getUser() {
            $u = new User();
            $u->fromUID($this->user);
            return $u;
        }

        function getTime() {
            return $this->time;
        }

        function create() {
            $sql = 'INSERT INTO `announcement_comments`(`id`, `content`, `announcement`, `user`, `time`) VALUES (:coid, :content, :aid, :uid, CURRENT_TIMESTAMP)';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'coid' => $this->getID(),
                'content' => $this->getContent(),
                'aid' => $this->announcement,
                'uid' => $this->user
            ));
        }

    }

?>