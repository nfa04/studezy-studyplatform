<?php

    class AssignmentFeedback {

        private $id;
        private $content;
        private $recipient;
        private $sender;
        private $assignment;
        private $time;

        public function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->content = $obj['content'];
            $this->recipient = $obj['recipient'];
            $this->sender = $obj['sender'];
            $this->assignment = $obj['assignment'];
            @$this->time = $obj['time'];
        }

        public function getID() {
            return $this->id;
        }

        public function getContent() {
            return $this->content;
        }

        public function getRecipient() {
            $u = new User();
            $u->fromUID($this->recipient);
            return $u;
        }

        public function getRecipientID() {
            return $this->recipient;
        }

        public function getSender() {
            $u = new User();
            $u->fromUID($this->sender);
            return $u;
        }

        public function getSenderID() {
            return $this->sender;
        }

        public function getAssignment() {
            $a = new Assignment();
            $a->fromID($this->assignment);
            return $a;
        }

        public function getAssignmentID() {
            return $this->assignment;
        }

        public function getSubmissionTime() {
            return $this->time;
        }

    }

?>