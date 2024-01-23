<?php

class SurveyAnswerSet {

    private $pdo;
    private $id;
    private $survey;
    private $submitted;
    private $user;

    function __construct() {
        $this->pdo = DB_CONNECTION;
    }

    public function fromDataObject($obj) {
        $this->id = $obj['id'];
        $this->survey = $obj['survey'];
        $this->submitted = $obj['submitted'];
        $this->user = $obj['user'];
    }

    public function fromID($id) {
        $sql = 'SELECT * FROM survey_answerset WHERE id=:id';
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

    public function getSurvey() {
        $survey = new Survey();
        $survey->fromID($this->survey);
        return $survey;
    }

    public function getSubmittedDate() {
        return $this->submitted;
    }
    
    public function getUser() {
        $user = new User();
        $user->fromUID($this->user);
        return $user;
    }
    
    public function getAnswers() {
        $sql = 'SELECT * FROM survey_answers WHERE answerset_id=:aid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'aid' => $this->getID()
        ));
        return $query->fetchAll();
    }

}

?>