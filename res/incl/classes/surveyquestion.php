<?php

    define('SURVEY_QUESTIONTYPES', array(
        'text',
        'mc-checkbox',
        'mc-radio',
        'number',
        'range',
        'date',
        'time'
    ));

    class SurveyQuestion {

        private $pdo;
        private $id;
        private $content;
        private $description;
        private $type;
        private $survey;
        private $options;

        function __construct() {
            $this->pdo = DB_CONNECTION;
        }

        public function fromDataObject($obj) {
            $this->id = $obj['id'];
            $this->content = $obj['content'];
            $this->description = $obj['description'];
            $this->type = $obj['type'];
            $this->survey = $obj['survey'];
            $this->options = @json_decode($obj['options'], true);
        }

        public function getID() {
            return $this->id;
        }

        public function getContent() {
            return $this->content;
        }

        public function getDescription() {
            return $this->description;
        }

        public function getType() {
            return SURVEY_QUESTIONTYPES[$this->type];
        }

        public function getSurvey() {
            $survey = new Survey();
            $survey->fromID($this->survey);
            return $survey;
        }

        public function getOptions() {
            return $this->options;
        }

        public function isValidAnswer($answer) {
            $questionType = $this->getType();
            $split = explode('-', $answer);
            if($questionType == 'text' OR (($questionType == 'mc-checkbox' OR $questionType == 'mc-radio') AND intval($answer) <= count($this->getOptions())) OR ($questionType == 'date' AND checkdate($split[1], $split[2], $split[0])) OR (($questionType == 'number' OR $questionType == 'range') AND ctype_digit($answer)) OR ($questionType == 'time'))return true;
            return false;
        }

        public function getAnswersCount() {
            $sql = 'SELECT content, COUNT(content) FROM survey_answers WHERE question=:qid GROUP BY content ORDER BY COUNT(content) DESC LIMIT 25;';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'qid' => $this->getID()
            ));
            $r = $query->fetchAll();
            $arr = array();
            foreach($r AS $ans) {
                $arr[(($this->getType() == 'mc-checkbox' OR $this->getType() == 'mc-radio') ? $this->getOptions()[$ans['content']] : $ans['content'])] = $ans[1];
            }
            return $arr;
        }

    }
?>