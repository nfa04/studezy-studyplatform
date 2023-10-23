<?php

    class Chat {

        private $id;
        private $name;
        private $createdAt;
        private $members;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            $this->id = $obj['chat_id'];
            $this->name = $obj['name'];
            $this->createdAt = $obj['created'];
            @$this->members = $obj['members']; // Members are optional, they don't need to be loaded for all usecases
        }

        public function fromID($id) {
            $sql = 'SELECT * FROM chats WHERE chat_id=:chid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'chid' => $id
            ));
            $this->fromDataObject($query->fetch());
        }

        public function getID() {
            return $this->id;
        }

        public function getName() {
            return $this->name;
        }

        public function getCreationDate() {
            return $this->createdAt;
        }

        public function countMembers() {
            $sql = 'SELECT COUNT(*) FROM chat_memberships WHERE chat_id=:chid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'chid' => $this->getID()
            ));
            return $query->fetch()['COUNT(*)'];
        }

    }

?>