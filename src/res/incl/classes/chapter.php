<?php
    class Chapter {

        private $pdo;
        private $id;
        private $name;
        private $course;
        private $created;
        private $index;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->name = $obj['name'];
            $this->course = $obj['course'];
            @$this->created = $obj['created'];
            @$this->index = $obj['nr'];
        }

        public function fromID($id) {
            $sql = 'SELECT * FROM chapters WHERE id=:id';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'id' => $id
            ));
            $this->fromDataObject($query->fetch());
        }

        public function getID() {
            return $this->id;
        }

        public function getName() {
            return $this->name;
        }

        public function getCourse($user)   {
            $c = new Course();
            $c->fromID($this->course, $user);
            return $c;
        }

        public function getCreated() {
            return $this->created;
        }

        public function getIndex() {
            return $this->index;
        }

        public function getFilePath($currentUser) {
            return COURSE_DATA_DIRECTORY.$this->getCourse($currentUser)->getID().'-'.$this->id.'.html';
        }

        public function getParchmentFilePath($currentUser) {
            return COURSE_DATA_DIRECTORY.$this->getCourse($currentUser)->getID().'-'.$this->id.'.json';
        }

        public function getContent($user) {
            if($this->getCourse($user)->hasReadAccess($user)) return @file_get_contents($this->getFilePath($user));
            return false;
        }

        public function setContent($user, $content) {
            if($this->getCourse($user)->hasWriteAccess($user)) {
                //file_put_contents($this->getFilePath($user), $content);

                // Create a temporary file to upload the string in memory (required to get a filehandle for cURL)
                $fileHandle = fopen('php://memory', 'rw');
                fwrite($fileHandle, $content);
                rewind($fileHandle);

                // Write the file onto S3 object storage using REST API
                $ch = curl_init($this->getFilePath());
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_INFILE, $fileHandle);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                // Filesize is the same amount of bytes as strlen
                curl_setopt($ch, CURLOPT_INFILESIZE, strlen($content));
                if(curl_exec($ch) !== false) return true;
                return false;
            }
            return false;
        }

        public function getProgress($user) {
            $sql = 'SELECT * FROM chapter_progress WHERE chapter=:chid AND user=:uid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'chid' => $this->getID(),
                'uid' => $user->getID()
            ));
            $res = $query->fetch();
            $cp = new ChapterProgress();
            // Make sure the progress object is set up in a proper way even if there is no data in the database
            $cp->fromDataObject(($res == false ? array('chapter' => $this->getID(), 'user' => $user->getID(), 'stars' => 0, 'progress' => 0) : $res));
            return $cp;
        }

        public function remove($currentUser) {
            if($this->getCourse($currentUser)->hasWriteAccess($currentUser)) {
                $sql = 'DELETE FROM chapters WHERE id=:id';
                $query = $this->pdo->prepare($sql);
                $query->execute(array(
                    'id' => $this->id
                ));
                /*unlink($this->getFilePath($currentUser));
                unlink($this->getParchmentFilePath($currentUser));*/

                // Perform the delete requests for both potentially existing files via Rest API at AWS
                $curl_published = curl_init();
                curl_setopt($curl_published, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl_published, CURLOPT_URL, $this->getFilePath($currentUser));

                $curl_parchment = curl_init();
                curl_setopt($curl_published, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl_published, CURLOPT_URL, $this->getParchmentFilePath($currentUser));

                if(curl_exec($curl_published) !== false AND curl_exec($curl_parchment) !== false) return true;
                return false;
            }
            return false;
        }

        public function getOverallProgressInfo() {
            $sql = 'SELECT AVG(stars), AVG(progress) FROM chapter_progress WHERE chapter=:chid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'chid' => $this->getID()
            ));
            return $query->fetch();
        }

        public function getProgressLeaderBoard() {
            $sql = 'SELECT progress, stars, user FROM chapter_progress WHERE chapter=:chid ORDER BY progress DESC LIMIT 10';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'chid' => $this->getID()
            ));
            return array_map(function($res) {
                $u = new User();
                $u->fromUID($res['user']);
                $res['user'] = $u;
                return $res;
            }, $query->fetchAll());
        }

    }

    class ChapterProgress {

        private $progress;
        private $stars;
        private $chapter;
        private $user;

        public function fromDataObject($obj) {
            $this->progress = $obj['progress'] * 100; // Progress in the database is stored as a float from 0 to 1, so mulitply it by a 100 to get it in percent
            $this->stars = $obj['stars'];
            $this->chapter = $obj['chapter'];
            $this->user = $obj['user'];
        }

        public function getProgress() {
            return $this->progress;
        }

        public function getStars() {
            return $this->stars;
        }

        public function update($progressPercent, $stars) {
            $sql = 'DELETE FROM chapter_progress WHERE chapter=:chid AND user=:uid; INSERT INTO `chapter_progress` (`chapter`, `user`, `stars`, `progress`) VALUES (:chid, :uid, :stars, :progress);';
            $query = DB_CONNECTION->prepare($sql);
            $query->execute(array(
                'chid' => $this->chapter,
                'uid' => $this->user,
                'stars' => $stars,
                'progress' => $progressPercent / 100 // Stored in the database as float from 0 to 1
            ));
        }

    }
?>