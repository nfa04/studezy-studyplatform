<?php

    require __DIR__.'/surveyquestion.php';
    require __DIR__.'/surveyanswerset.php';

    class Survey {

        private $pdo;
        private $id;
        private $title;
        private $description;
        private $course;
        private $owner;
        private $show_answers;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->title = $obj['title'];
            $this->description = $obj['description'];
            $this->course = $obj['course'];
            $this->owner = $obj['owner'];
            $this->show_answers = $obj['show_answers'];
        }

        public function fromID($id) {
            $sql = 'SELECT * FROM surveys WHERE id=:id';
            $query = $this->pdo->prepare($sql);
            $query->execute(
                array('id' => $id)
            );
            $response = $query->fetch();
            if($response !== NULL) {
                $this->fromDataObject($response);
                return true;
            }
            return false;
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
            $course = new Course();
            $course->fromID($this->course, $currentUser);
            return $course;
        }

        public function getOwner() {
            $owner = new User();
            $owner->fromUID($this->owner);
            return $owner;
        }

        public function showsAnswers() {
            return $this->show_answers;
        }

        public function getQuestions() {
            $sql = 'SELECT * FROM survey_questions WHERE survey=:sid ORDER BY nr ASC';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'sid' => $this->getID()
            ));
            $r = $query->fetchAll();
            $questions = array();
            foreach($r AS $rec) {
                $q = new SurveyQuestion();
                $q->fromDataObject($rec);
                $questions[] = $q;
            }
            return $questions;
        }

        public function hasReadAccess($user) {
            return $this->getCourse($user)->hasReadAccess($user);
        }

        public function hasWriteAccess($user) {
            return $this->getCourse($user)->hasWriteAccess($user);
        }

        public function fill($answers, $currentUser) {
            if($this->hasReadAccess($currentUser)) {
                $questions = $this->getQuestions();
                $id = uniqid();
                $sql = 'INSERT INTO `survey_answerset`(`id`, `survey`, `submitted`, `user`) VALUES (:id,:sid,CURRENT_TIMESTAMP,:uid)';
                $query = $this->pdo->prepare($sql);
                $query->execute(array(
                    'id' => $id,
                    'sid' => $this->getID(),
                    'uid' => $currentUser->getID()
                ));
                foreach($questions AS $question) {
                    if($question->isValidAnswer($answers[$question->getID()])) {
                        $sql = 'INSERT INTO `survey_answers`(`answerset_id`, `question`, `content`) VALUES (:id, :question, :content)';
                        $query = $this->pdo->prepare($sql);
                        $query->execute(array(
                            'id' => $id,
                            'question' => $question->getID(),
                            'content' => $answers[$question->getID()]
                        ));
                    }
                }
                return true;
            }
            return false;
        }

        public function getQuestionIDs() {
            $sql = 'SELECT id FROM survey_questions WHERE survey=:sid ORDER BY nr ASC';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'sid' => $this->getID()
            ));
            $r = $query->fetchAll();
            return array_map(function($obj) {
                return $obj['id'];
            }, $r);
        }

        public function storeQuestionsFromDataObject($obj, $currentUser) {
            if($this->hasWriteAccess($currentUser)) {
                $i = 0;
                $ids = $this->getQuestionIDs();
                foreach($obj AS $id=>$info) {
                    if(!in_array($id, $ids)) {
                        // Insert new questions
                        $sql = 'INSERT INTO `survey_questions`(`id`, `survey`, `content`, `description`, `type`, `options`, `nr`) VALUES (:id,:survey,:content,:description,:type,:options,:nr)';
                        $query = $this->pdo->prepare($sql);
                        $query->execute(array(
                            'id' => $id,
                            'survey' => $this->getID(),
                            'content' => $info['content'],
                            'description' => $info['description'],
                            'type' => array_flip(SURVEY_QUESTIONTYPES)[$info['type']],
                            'options' => (isset($info['options']) ? json_encode($info['options']) : '[]'),
                            'nr' => $i
                        ));
                    } else {
                        // Update existing questions
                        $sql = 'UPDATE `survey_questions` SET `content`=:content,`description`=:description,`type`=:type,`options`=:options,`nr`=:nr WHERE id=:id';
                        $query = $this->pdo->prepare($sql);
                        $query->execute(array(
                            'id' => $id,
                            'content' => $info['content'],
                            'description' => $info['description'],
                            'type' => array_flip(SURVEY_QUESTIONTYPES)[$info['type']],
                            'options' => (isset($info['options']) ? json_encode($info['options']) : '[]'),
                            'nr' => $i
                        ));
                    }
                    $i++;
                }
                $newQuestionsIDs = array_keys($obj);
                foreach($ids AS $id) {
                    if(!in_array($id, $newQuestionsIDs)) {
                        // Remove question
                        $sql = 'DELETE FROM survey_answers WHERE question=:id; DELETE FROM survey_questions WHERE id=:id';
                        $query = $this->pdo->prepare($sql);
                        $query->execute(array(
                            'id' => $id
                        ));
                    }
                }
                return true;
            }
            return false;
        }

        public function getAnswers() {
            $sql = 'SELECT * FROM survey_answers WHERE question=ANY(SELECT id FROM survey_questions WHERE survey=:sid)';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'sid' => $this->getID()
            ));
            return $query->fetchAll();
        }

        public function getAnswerSets() {
            $sql = 'SELECT * FROM survey_answerset WHERE survey=:sid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'sid' => $this->getID()
            ));
            $r = $query->fetchAll();
            $r = array_map(function($arr) {
                $as = new SurveyAnswerSet();
                $as->fromDataObject($arr);
                return $as;
            }, $r);
            return $r;
        }

        public function create($currentUser) {
            if($this->getCourse($currentUser)->hasWriteAccess($currentUser)) {
                $data = array(
                    'id' => $this->getID(),
                    'owner' => $currentUser->getID(),
                    'title' => $this->getTitle(),
                    'description' => $this->getDescription(),
                    'course' => $this->course,
                    'show_answers' => $this->showsAnswers()
                );
                $sql = 'INSERT INTO `surveys`(`id`, `owner`, `title`, `description`, `course`, `show_answers`) VALUES (:id,:owner,:title,:description,:course,:show_answers)';
                $query = $this->pdo->prepare($sql);
                $query->execute($data);

                // Notify users subscribed to notifications
                $course = $this->getCourse($currentUser);
                $course->notifySubscribers(array(
                    'type' => 'cupdate',
                    'utype' => 'survey',
                    'url' => '/surveys/view?i='.$this->getID(),
                    'name' => $course->getName()
                ));

                return true;
            }
        }

    }

    function suggestSurveysByName($name) {
        $pdo = new PDO('mysql:host=localhost;dbname=flashcards', 'noel', 'Daesilo.1881');
        $sql = 'SELECT * FROM surveys WHERE title LIKE :name';
        $query = $pdo->prepare($sql);
        $query->execute(array(
            'name' => $name.'%'
        ));
        return $query->fetchAll();
    }

?>