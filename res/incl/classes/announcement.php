<?php

    require __DIR__.'/announcementcomment.php';

    class Announcement {

        private $id;
        private $title;
        private $content;
        private $user;
        private $course;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->title = $obj['title'];
            $this->content = $obj['content'];
            $this->user = $obj['user'];
            $this->course = $obj['course'];
        }

        public function fromID($id) {
            $sql = 'SELECT * FROM announcements WHERE id=:aid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'aid' => $id
            ));
            $this->fromDataObject($query->fetch());
        }

        public function getID() {
            return $this->id;
        }

        public function getTitle() {
            return $this->title;
        }

        public function getContent() {
            return $this->content;
        }

        public function getUser() {
            $user = new User();
            $user->fromUID($this->user);
            return $user;
        }

        public function getCourse($currentUser) {
            $course = new Course();
            $course->fromID($this->course, $currentUser);
            return $course;
        }

        public function create($currentUser) {
            $sql = 'INSERT INTO `announcements`(`id`, `title`, `content`, `user`, `course`) VALUES (:aid,:title,:content,:user,:course)';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'aid' => $this->id,
                'title' => $this->title,
                'content' => $this->content,
                'user' => $this->user,
                'course' => $this->course
            ));

            // Notify users subscribed to notifications
            $course = $this->getCourse($currentUser);
            $course->notifySubscribers(array(
                'type' => 'cupdate',
                'utype' => 'announcement',
                'url' => '/announcements/view?i='.$this->getID(),
                'name' => $course->getName()
            ));
        }

        public function edit() {
            $sql = 'UPDATE `announcements` SET `title`=:title,`content`=:content,`user`=:user,`course`=:course WHERE id=:aid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'aid' => $this->id,
                'title' => $this->title,
                'content' => $this->content,
                'user' => $this->user,
                'course' => $this->course
            ));
        }

        public function getComments() {
            $sql = 'SELECT * FROM announcement_comments WHERE announcement=:aid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'aid' => $this->getID()
            ));
            $r = $query->fetchAll();
            return array_map(function($data) {
                $c = new AnnouncementComment();
                $c->fromDataObject($data);
                return $c;
            }, $r);
        }

        public function countComments() {
            $sql = 'SELECT COUNT(*) FROM announcement_comments WHERE announcement=:aid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'aid' => $this->getID()
            ));
            return $query->fetch()[0];
        }

        public function addComment($content, $currentUser) {
            $c = new AnnouncementComment();
            $c->fromDataObject(array(
                'id' => uniqid(),
                'content' => $content,
                'user' => $currentUser->getID(),
                'announcement' => $this->getID()
            ));
            $c->create();
            return $c;
        }

    }

    function suggestAnnouncementsByName($name) {
        $pdo = new PDO('mysql:host=localhost;dbname=flashcards', 'noel', 'Daesilo.1881');
        $sql = 'SELECT * FROM announcements WHERE title LIKE :name';
        $query = $pdo->prepare($sql);
        $query->execute(array(
            'name' => $name.'%'
        ));
        return $query->fetchAll();
    }

?>