<?php

    require __DIR__.'/assignmentsubmission.php';
    require __DIR__.'/assignmentfeedback.php';

    class Assignment {

        private $id;
        private $title;
        private $description;
        private $course;
        private $owner;
        private $created;
        private $due;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            @$this->id = $obj['id'];
            $this->title = $obj['title'];
            $this->description = $obj['description'];
            $this->course = $obj['course'];
            @$this->owner = $obj['owner'];
            @$this->created = $obj['created'];
            $this->due = $obj['due'];
        }

        public function fromID($id) {
            $sql = 'SELECT * FROM assignments WHERE id=:aid';
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

        public function getDescription() {
            return $this->description;
        }

        public function getCourse($currentUser) {
            $c = new Course();
            $c->fromID($this->course, $currentUser);
            return $c;
        }

        public function getOwner() {
            $u = new User();
            $u->fromUID($this->owner);
            return $u;
        }

        public function getCreationTime() {
            return $this->created;
        }

        public function getDueTime() {
            return $this->due;
        }

        public function isOverdue() {
            return (strtotime($this->due) < time() ? true : false);
        }

        public function create($currentUser) {
            if($this->getCourse($currentUser)->hasWriteAccess($currentUser)) {
                $this->owner = $currentUser->getID();
                $this->id = uniqid();
                $sql = 'INSERT INTO `assignments`(`id`, `title`, `description`, `course`, `owner`, `created`, `due`) VALUES (:id,:title,:description,:course,:owner,CURRENT_TIMESTAMP,:due)';
                $query = $this->pdo->prepare($sql);
                $query->execute(array(
                    'id' => $this->getID(),
                    'title' => $this->getTitle(),
                    'description' => $this->getDescription(),
                    'course' => $this->course,
                    'owner' => $this->owner,
                    'due' => $this->getDueTime()
                ));

                // Notify users subscribed to notifications
                $course = $this->getCourse($currentUser);
                $course->notifySubscribers(array(
                    'type' => 'cupdate',
                    'utype' => 'assignment',
                    'url' => '/assignments/view?i='.$this->getID(),
                    'name' => $course->getName()
                ));

                return true;
            }
            return false;
        }

        public function submit($assetID, $type, $fileName, $currentUser) {
            $sql = 'INSERT INTO `assignment_submissions`(`id`, `user`, `assignment`, `submitted`, `fileName`, `type`) VALUES (:id,:user,:assignment,CURRENT_TIMESTAMP,:fileName,:type)';
            $query = $this->pdo->prepare($sql);
            $data = array(
                'id' => $assetID,
                'user' => $currentUser->getID(),
                'assignment' => $this->getID(),
                'fileName' => $fileName,
                'type' => $type
            );
            $query->execute($data);
            $as = new AssignmentSubmission();
            $as->fromDataObject($data);
            return $as;
        }

        public function getSubmissionsByUser($user) {
            $sql = 'SELECT * FROM assignment_submissions WHERE user=:uid AND assignment=:aid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'uid' => $user->getID(),
                'aid' => $this->getID()
            ));
            $r = $query->fetchAll();
            return array_map(function($data) {
                $as = new AssignmentSubmission();
                $as->fromDataObject($data);
                return $as;
            }, $r);
        }

        public function hasSubmitted($user) {
            $sql = 'SELECT COUNT(*) FROM assignment_submissions WHERE user=:uid AND assignment=:aid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'uid' => $user->getID(),
                'aid' => $this->getID()
            ));
            return ($query->fetch()[0] == 0 ? false : true);
        }

        public function hasReadAccess($user) {
            return $this->getCourse($user)->hasReadAccess($user);
        }

        public function hasWriteAccess($user) {
            return $this->getCourse($user)->hasWriteAccess($user);
        }

        public function getSubmissionInfoByUser() {
            $sql = 'SELECT DISTINCT user, COUNT(user), (SELECT uname FROM users WHERE users.uid=user), MAX(submitted) FROM assignment_submissions WHERE assignment=:aid GROUP BY user';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'aid' => $this->getID()
            ));
            $r = $query->fetchAll();
            return $r;
        }

        public function timeIsOverdue($timestamp) {
            return ($timestamp > strtotime($this->due));
        }

        public function getUsersWithoutSubmission() {
            $sql = 'SELECT * FROM users WHERE uid=ANY(SELECT uid FROM subscriptions WHERE uid NOT IN (SELECT user FROM assignment_submissions WHERE assignment=:aid))';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'aid' => $this->getID()
            ));
            $r = $query->fetchAll();
            return array_map(function($data) {
                $u = new User();
                $u->fromDataObject($data);
                return $u;
            }, $r);
        }

        public function submitFeedback($feedback, $forUser, $currentUser) {
            if($this->hasWriteAccess($currentUser)) {
                $data = array(
                    'id' => uniqid(),
                    'content' => $feedback,
                    'recipient' => $forUser->getID(),
                    'sender' => $currentUser->getID(),
                    'assignment' => $this->getID()
                );
                $sql = 'INSERT INTO `assignment_feedback`(`id`, `content`, `recipient`, `sender`, `assignment`, `time`) VALUES (:id,:content,:recipient,:sender,:assignment,CURRENT_TIMESTAMP)';
                $query = $this->pdo->prepare($sql);
                $query->execute($data);
                $af = new AssignmentFeedback();
                $af->fromDataObject($data);
                return $af;
            }
            return false;
        }

        public function feedbackExistsForUser($user) {
            $sql = 'SELECT COUNT(*) FROM assignment_feedback WHERE assignment=:aid AND recipient=:ruid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'aid' => $this->getID(),
                'ruid' => $user->getID()
            ));
            return ($query->fetch()[0] == 0 ? false : true);
        }

        public function getFeedbackForUser($user) {
            $sql = 'SELECT * FROM assignment_feedback WHERE assignment=:aid AND recipient=:ruid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'aid' => $this->getID(),
                'ruid' => $user->getID()
            ));
            $r = $query->fetch();
            if(empty($r)) return false;
            $af = new AssignmentFeedback();
            $af->fromDataObject($r);
            return $af;
        }

    }

    function suggestAssignmentsByName($name) {
        $pdo = new PDO('mysql:host=localhost;dbname=flashcards', 'noel', 'Daesilo.1881');
        $sql = 'SELECT * FROM assignments WHERE title LIKE :name';
        $query = $pdo->prepare($sql);
        $query->execute(array(
            'name' => $name.'%'
        ));
        return $query->fetchAll();
    }

?>