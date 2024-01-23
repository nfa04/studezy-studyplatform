<?php

define('ASSET_DIRECTORY', AWS_ROOT_LOCATION);

    class Asset {

        private $pdo;
        private $id;
        private $name;
        private $type;
        private $index;
        private $owner;
        private $course;
        private $last_edited;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->name = $obj['name'];
            $this->type = $obj['type'];
            $this->index = $obj['i'];
            $this->owner = $obj['owner'];
            $this->course = $obj['course'];
            $this->last_edited = $obj['last_edited'];
        }

        public function fromID($id) {
            $sql = 'SELECT * FROM assets WHERE id=:id';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'id' => $id
            ));
            $this->fromDataObject($query->fetch());
        }

        public function getName() {
            return $this->name;
        }

        public function getID() {
            return $this->id;
        }

        public function getType() {
            return $this->type;
        }

        public function getIndex() {
            return $this->index;
        }

        public function getOwner() {
            $owner = new User();
            $owner->fromUID($this->owner);
            return $owner;
        }

        public function getOwnerID() {
            return $this->owner;
        }

        public function getCourse($currentUser) {
            $course = new Course();
            $course->fromID($this->course, $currentUser);
            return $course;
        }

        public function getCourseID() {
            return $this->course;
        }

        public function getLastEdited() {
            return $this->last_edited;
        }

        public function getFullFilePath() {
            return ASSET_DIRECTORY.$this->getID().'.'.mime2ext($this->getType());
        }
        
        public function remove($currentUser) {
            require_once __DIR__.'/../mime2ext.php';
            $course = $this->getCourse($currentUser);
            if($course->hasWriteAccess($currentUser) OR ($course === NULL AND $this->getOwner()->isUser($currentUser))) {
                $sql = 'DELETE FROM assets WHERE id=:id';
                $query = $this->pdo->prepare($sql);
                $query->execute(array(
                    'id' => $this->getID()
                ));

                // The following code is legacy code when using a local filesystem, StudEzy now uses AWS S3 for file storage
                // unlink($this->getFullFilePath());

                // Perform the delete request via Rest API at AWS
                $curl_handle = curl_init();

                curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl_handle, CURLOPT_URL, $this->getFullFilePath());

                if(curl_exec($curl_handle) !== false) return true;
                return false;
            } 
            return false;
        }

    }
?>