<?php

    require_once dirname(__DIR__, 1).'/mime2ext.php';

    class AssignmentSubmission {

        private $id;
        private $user;
        private $assignment;
        private $time;
        private $fileName;
        private $type;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->user = $obj['user'];
            $this->assignment = $obj['assignment'];
            $this->time = $obj['submitted'];
            $this->fileName = $obj['fileName'];
            $this->type = $obj['type'];
        }

        public function getID() {
            return $this->id;
        }

        public function getUser() {
            $u = new User();
            $u->fromUID($this->user);
            return $u;
        }

        public function getAssignment() {
            $a = new Assignment();
            $a->fromID($this->assignment);
            return $a;
        }

        public function getSubmissionTime() {
            return $this->time;
        }

        public function getFileName() {
            return $this->fileName;
        }

        public function getFilePath() {
            return ASSET_REMOTE_LOCATION_ROOT.$this->getID().'.'.mime2ext($this->type);
        }

    }

?>