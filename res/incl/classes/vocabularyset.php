<?php

    define('REPETITIONS_UNTIL_COMPLETE', 5);

    class VocabularySet {

        private $id;
        private $name;
        private $description;
        //private $words;
        private $course;
        private $user;

        function __construct() {
            // Establish a db connection
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->name = $obj['name'];
            $this->user = $obj['user'];
            $this->description = $obj['description'];
            $this->course = $obj['course'];
            //@$this->words = $obj['words'];
        }

        public function fromID($id) {
            $sql = 'SELECT * FROM vocabularysets WHERE id=:vsid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'vsid' => $id
            ));
            $this->fromDataObject($query->fetch());
        }

        public function getName() {
            return $this->name;
        }

        public function getDescription() {
            return $this->description;
        }

        public function getID()  {
            return $this->id;
        }

        public function getUser() {
            $u = new User();
            $u->fromUID($this->user);
            return $u;
        }

        public function getCourse($currentUser) {
            $c = new Course();
            $c->fromID($this->course, $currentUser);
            return $c;
        }

        public function suggestNextWords($currentUser) {
            $sql = 'SELECT vocabulary_words.word_id, vocabulary_words.word, vocabulary_words.definition, vocabulary_scores.score  FROM `vocabulary_words` LEFT JOIN vocabulary_scores ON vocabulary_words.word_id = vocabulary_scores.word_id AND vocabulary_scores.user = :uid AND vocabulary_scores.score < :ruc WHERE set_id = :vsid ORDER BY score ASC LIMIT 5;';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'uid' => $currentUser->getID(),
                'vsid' => $this->getID(),
                'ruc' => REPETITIONS_UNTIL_COMPLETE
            ));
            return array_map(function($res) {
                // Make sure words that don't have a score in the db already are set to 0
                if(!isset($res['score'])) $res['score'] = 0;
                return $res;
            }, $query->fetchAll());
        }

        public function getWords() {
            $sql = 'SELECT * FROM vocabulary_words WHERE set_id=:vsid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'vsid' => $this->getID()
            ));
            return $query->fetchAll();
        }

        public function applyScoreDelta($currentUser, $scoreDelta) {
            $sql = 'SELECT word_id FROM vocabulary_words WHERE set_id=:vsid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'vsid' => $this->getID()
            ));
            $words = $query->fetchAll();
            foreach($words AS $word) {
                if(in_array($word['word_id'], array_keys($scoreDelta))) {
                    $sql = 'UPDATE `vocabulary_scores` SET `score`= score + :delta WHERE word_id=:wid AND user=:uid; INSERT INTO `vocabulary_scores`(`word_id`, `user`, `score`) VALUES (:wid, :uid, :delta); INSERT INTO `vocabulary_scores`(`word_id`, `user`, `score`) VALUES (:wid, :uid, :delta) WHERE NOT EXISTS(SELECT * FROM `vocabulary_scores` WHERE `word_id`=:wid AND `user`=:uid)';
                    $query = $this->pdo->prepare($sql);
                    $query->execute(array(
                        'wid' => $word['word_id'],
                        'delta' => $scoreDelta[$word['word_id']],
                        'uid' => $currentUser->getID()
                    ));
                }
            }
           
        }

        public function countWords() {
            $sql = 'SELECT COUNT(*) FROM vocabulary_words WHERE set_id=:vsid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'vsid' => $this->getID()
            ));
            return $query->fetch()['COUNT(*)'];
        }

        public function getScorePercentage($user) {
            $sql = 'SELECT AVG(score) FROM `vocabulary_words` INNER JOIN vocabulary_scores ON vocabulary_words.word_id = vocabulary_scores.word_id AND vocabulary_scores.user = :uid WHERE set_id = :vsid;';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'uid' => $user->getID(),
                'vsid' => $this->getID()
            ));
            return ($query->fetch()['AVG(score)'] / REPETITIONS_UNTIL_COMPLETE * 100);
        }

        public function saveFromDataObject($currentUser, $obj) {
            // Check for write access
            if($this->hasWriteAccess($currentUser)) {
                foreach($obj AS $id=>$word) {
                    // Update all words except the new ones set under the id "new"
                    if($id != 'new') {
                        $sql = 'UPDATE `vocabulary_words` SET `word`=:word,`definition`=:definition WHERE word_id=:wid AND set_id=:sid';
                        $query = $this->pdo->prepare($sql);
                        $query->execute(array(
                            'word' => $word['word'],
                            'definition' => $word['definition'],
                            'wid' => $id,
                            'sid' => $this->getID()
                        ));
                    }
                }
    
                // Check for new words, add them if requested
                if(isset($obj['new'])) {
                    foreach($obj['new'] AS $word) {
                        $sql = 'INSERT INTO `vocabulary_words`(`word_id`, `set_id`, `word`, `definition`) VALUES (:wid,:sid,:word,:definition)';
                        $query = $this->pdo->prepare($sql);
                        $query->execute(array(
                            'wid' => uniqid(),
                            'sid' => $this->getID(),
                            'word' => $word['word'],
                            'definition' => $word['definition']
                        ));
                    }
                }
            }
        }

        public function getUserID() {
            return $this->user;
        }

        public function getCourseID() {
            return $this->course;
        }

        public function create() {
            $sql = 'INSERT INTO `vocabularysets`(`id`, `name`, `description`, `user`, `course`) VALUES (:id,:name,:description,:uid,:cid)';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'id' => $this->getID(),
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'uid' => $this->getUserID(),
                'cid' => $this->getCourseID()
            ));
        }

        public function setDescription($description) {
            $sql = 'UPDATE `vocabularysets` SET `description`=:desc WHERE id=:sid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'desc' => $description,
                'sid' => $this->getID()
            ));
        }

        public function setName($name) {
            $sql = 'UPDATE `vocabularysets` SET `name`=:name WHERE id=:sid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'name' => $name,
                'sid' => $this->getID()
            ));
        }

        public function hasWriteAccess($user) {
            // Return true if the user has write access to the course which the set is part of
            if($this->course !== null AND $this->getCourse($user)->hasWriteAccess($user)) return true;
            // Return true if the user is the owner of this course
            else if($this->getUser()->isUser($user)) return true;
            // return false otherwise
            return false;
        }


    }

?>