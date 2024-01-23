<?php

    define('DOCUMENT_DIRECTORY', AWS_DOCSTORE_ROOT);

    class Document {
        
        private $id;
        private $name;
        private $last_edited;
        private $owner;
        private $course;
        private $private;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->name = $obj['name'];
            $this->last_edited = $obj['last_edited'];
            $this->owner = $obj['owner'];
            $this->course = $obj['course'];
            $this->private = $obj['private'];
        }

        public function fromID($id) {
            $sql = 'SELECT * FROM documents WHERE id=:did';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'did' => $id
            ));
            $this->fromDataObject($query->fetch());
        }

        public function createEmpty($currentUser) {
            $sql = 'INSERT INTO `documents`(`id`, `name`, `description`, `last_edited`, `owner`, `course`, `private`) VALUES (:id,:name,:description,CURRENT_TIMESTAMP,:owner,NULL,1)';
            $query = $this->pdo->prepare($sql);
            $data = array(
                'id' => uniqid(),
                'name' => 'New document',
                'description' => '',
                'owner' => $currentUser->getID()
            );
            $query->execute($data);
            $this->fromDataObject($data);
            // file_put_contents($this->getJSONFilePath(), '{}');

            // Create an empty file on S3 object storage via REST API
            // Create a temporary file to upload the string in memory (required to get a filehandle for cURL)
            $fileHandle = fopen('php://temp/maxmemory:2', 'rw');
            fputs($fileHandle, '{}');
            rewind($fileHandle);

            // Write the file onto S3 object storage using REST API
            $ch = curl_init($this->getJSONFilePath());
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, $fileHandle);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_INFILESIZE, 2);
            if(curl_exec($ch) !== false) return $this;
            return false;
        }

        public function getID() {
            return $this->id;
        }

        public function getName() {
            return $this->name;
        }

        public function getLastEditTime() {
            return $this->last_edited;
        }

        public function getOwner() {
            $u = new User();
            $u->fromUID($this->owner);
            return $u;
        }

        public function getCourse() {
            $c = new Course();
            $c->fromID($this->course);
            return $c;
        }

        public function isPrivate() {
            return $this->private;
        }

        public function getCoAuthors($include_readonly = false) {
            $sql = 'SELECT * FROM document_permissions WHERE doc_id=:did';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'did' => $this->getID()
            ));
            return array_filter(array_map(function($permission) use ($include_readonly) {
                if(!$this->isPrivate() OR ($permission['write_access'] OR $include_readonly)) {
                    $u = new User();
                    $u->fromUID($permission['user']);
                    return $u;
                }
            }, $query->fetchAll()), static function($var){return $var !== null;});
        }

        public function hasWriteAccess($user) {
            if($user->isUser($this->getOwner())) return true;
            $sql = 'SELECT COUNT(*) FROM document_permissions WHERE doc_id=:did AND user=:uid AND write_access=true';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'did' => $this->getID(),
                'uid' => $user->getID()
            ));
            return $query->fetch()['COUNT(*)'];
        }

        public function hasReadAccess($user) {
            if($user->isUser($this->getOwner()) OR !$this->isPrivate()) return true;
            $sql = 'SELECT COUNT(*) FROM document_permissions WHERE doc_id=:did AND user=:uid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'did' => $this->getID(),
                'uid' => $user->getID()
            ));
            return $query->fetch()['COUNT(*)'];
        }

        public function getJSONFilePath() {
            return DOCUMENT_DIRECTORY.$this->getID().'.json';
        }

        public function getFileSize() {
            return filesize($this->getJSONFilePath()).' byte(s)';
        }

        public function updateWriteAccess($currentUser, $user, $write_access) {
            if($this->hasWriteAccess($currentUser)) {
                $sql = 'UPDATE `document_permissions` SET `write_access`=:wa WHERE doc_id=:did AND user=:uid';
                $query = $this->pdo->prepare($sql);
                $query->execute(array(
                    'wa' => $write_access,
                    'did' => $this->getID(),
                    'uid' => $user->getID()
                ));
                return true;
            }
            return false;
        }

        public function setPrivacyPreference($currentUser, $private) {
            if($this->hasWriteAccess($currentUser)) {
                $sql = 'UPDATE documents SET private=:ps WHERE id=:did';
                $query = $this->pdo->prepare($sql);
                $query->execute(array(
                    'ps' => $private,
                    'did' => $this->getID()
                ));
            }
        }

        public function share($currentUser, $user, $write_access) {
            if($this->hasWriteAccess($currentUser)) {
                $sql = 'INSERT INTO `document_permissions`(`doc_id`, `user`, `write_access`) VALUES (:did, :uid, :wa)';
                $query = $this->pdo->prepare($sql);
                $query->execute(array(
                    'did' => $this->getID(),
                    'uid' => $user->getID(),
                    'wa' => $write_access
                ));
            }
        }

        public function removePermissions($currentUser, $user) {
            if($this->hasWriteAccess($currentUser)) {
                $sql = 'DELETE FROM document_permissions WHERE doc_id=:did AND user=:uid';
                $query = $this->pdo->prepare($sql);
                $query->execute(array(
                    'did' => $this->getID(),
                    'uid' => $user->getID()
                ));
            }
        }

        public function remove($currentUser) {
            if($this->hasWriteAccess($currentUser)) {
                $sql = 'DELETE FROM document_permissions WHERE doc_id=:did; DELETE FROM documents WHERE id=:did;';
                $query = $this->pdo->prepare($sql);
                $query->execute(array(
                    'did' => $this->getID()
                ));
                //unlink($this->getJSONFilePath());
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_URL, $this->getJSONFilePath());
                if(curl_exec($ch) !== false) return true;
            }
        }

    }

?>