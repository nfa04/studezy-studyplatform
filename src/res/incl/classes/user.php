<?php
@session_start();

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

define('SERVER_CONFIG', json_decode(file_get_contents(__DIR__.'/../../../../.studezy-server-vars.json'), true));


// Define constants
define('ASSET_REMOTE_LOCATION_ROOT', SERVER_CONFIG['ASSET_REMOTE_LOCATION_ROOT']);
define('AWS_ROOT_LOCATION', SERVER_CONFIG['AWS_ROOT_LOCATION']);
define('ASSET_INTERNAL_TMP_LOCATION', SERVER_CONFIG['ASSET_INTERNAL_TMP_LOCATION']);

define('CHATSERVER_REMOTE_LOCATION', SERVER_CONFIG['CHATSERVER_REMOTE_LOCATION']);
define('DOCSERVER_REMOTE_LOCATION', SERVER_CONFIG['DOCSERVER_REMOTE_LOCATION']);

define('AWS_DOCSTORE_ROOT', SERVER_CONFIG['AWS_DOCSTORE_ROOT']);

// Stargate Credentials required to connect to Cassandra DB
define('STARGATE_API_DB_ID', SERVER_CONFIG['STARGATE']['STARGATE_API_DB_ID']);
define('STARGATE_API_DB_REGION', SERVER_CONFIG['STARGATE']['STARGATE_API_DB_REGION']);
define('STARGATE_API_DB_KEYSPACE', SERVER_CONFIG['STARGATE']['STARGATE_API_DB_KEYSPACE']);
define('STARGATE_API_DB_APPLICATION_TOKEN', SERVER_CONFIG['STARGATE']['STARGATE_API_DB_APPLICATION_TOKEN']);

// Globally define the mysql connection
define('DB_CONNECTION', new PDO('mysql:host='.SERVER_CONFIG['MYSQL']['HOST'].';dbname='.SERVER_CONFIG['MYSQL']['DB_NAME'], SERVER_CONFIG['MYSQL']['USER'], SERVER_CONFIG['MYSQL']['PASSWORD']));

require __DIR__.'/../../../vendor/autoload.php';
require __DIR__.'/course.php';

class User {

    private $id;
    private $name;
    private $passwordHash;
    private $email;
    private $created;
    private $emailVerified;
    private $pdo;
    private $lastSession;
    private $contactName; // Contact name can only be added manually as this is not a standard property and will vary depending on who's using the app
    private $description;
    private $calendar;
    
    function __construct() {
        // Establish a db connection
        $this->pdo = DB_CONNECTION;
    }
    
    private function addDataToObject($id, $name, $passwordHash, $email, $created) {
        $this->id = $id;
        $this->name = $name;
        $this->passwordHash = $passwordHash;
        $this->email = $email;
        $this->created = $created;
    }

    public function fromDataObject($obj) {
        // parses user data into object
        $this->id = $obj['uid'];
        $this->name = $obj['uname'];
        $this->passwordHash = $obj['password'];
        $this->email = $obj['mail'];
        $this->created = $obj['created'];
        @$this->emailVerified = $obj['email_verified'];
        @$this->lastSession = $obj['last_session'];
        $this->description = $obj['description'];
        $this->calendar = $obj['calendar_id'];
    }

    public function login($uname, $password) {
        $sql = 'SELECT * FROM users WHERE uname=:uname';
        $query = $this->pdo->prepare($sql);
        $query->execute(
            array('uname' => $uname)
        );
        $response = $query->fetch();
        if(password_verify($password, $response['password'])) {
            // User data correct, if mail has not yet been verified redirect to verification page and stop script execution (for security reasons)
            if(!$response['email_verified']) {
                header('Location: /account/confirmMail');
                die('You should be redirected...');
            }
            $this->fromDataObject($response);
            $_SESSION['uid'] = $response['uid'];
           return true;
        }
        return false;
    }

    public function fromUID($uid) {
        $sql = 'SELECT * FROM users WHERE uid=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(
            array('uid' => $uid)
        );
        $response = $query->fetch();
        if($response !== NULL) {
            // User was found
            $this->fromDataObject($response);
            return true;
        }
        return false;
    }

    public function restoreFromSession($redirect = false) {
        // Restores data of a logged in user from session
        if(isset($_SESSION['uid'])) {
            // found an existing session
            return $this->fromUID($_SESSION['uid']);
        } else if($redirect) {
            header('Location: /signin');
        }
        return false;
    }
    
    public function signup($user) {
        // Calling MySQL stored procedure to create user, will return 1 on success and 0 on failure
        $query = 'CALL create_user(:uid, :uname, :mail, :password, :code, :verification_code, :calendar_id, @p5);';
        $sql = $this->pdo->prepare($query);
        $sql->execute($user);
        $response = $sql->fetch()['success'];
        if($response) {
            // Original user object doesn't require 'created' and 'email_verified' so completing it here
            $user['created'] = date("m - d - Y", time());
            $user['email_verified'] = false;
            $this->fromDataObject($user);
            $_SESSION['uid'] = $user['uid'];
        }
        return $response;
    }

    public function getConfirmationCode() {
        $sql = 'SELECT code FROM confirmation_codes WHERE uid=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->id
        ));
        return $query->fetch()['code'];
    }

    public function getID() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getMail() {
        return $this->email;
    }

    public function getCreatedOn() {
        return $this->created;
    }

    public function getLastSession() {
        return $this->lastSession;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setMailVerified() {
        $sql = 'CALL set_mail_verified(:uid);';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->id
        ));
        return true;
    }

    public function logout() {

        // Destroy the session
        session_destroy();

        // Update last session in MYSQL-DB
        $sql = 'UPDATE users SET last_session=CURRENT_TIMESTAMP WHERE uid=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));

        // Create the stream options to delete the push subscriptions via Stargate API
        $opts = array(
            'http' => array(
                'method' => 'DELETE',
                'header' => "Accept: application/json\r\nContent-Type: application/json\r\nX-Cassandra-Token: ".STARGATE_API_DB_APPLICATION_TOKEN
            )
        );

        $context = stream_context_create($opts);

        // opening the stream with context to write to cassandra via stargate API
        if(file_get_contents('https://'.STARGATE_API_DB_ID.'-'.STARGATE_API_DB_REGION.'.apps.astra.datastax.com/api/rest/v2/keyspaces/studezy/push_subscriptions_by_uid/'.$this->getID(), false, $context) !== false) return true;
        
    }

    public function findCourses() {
        $sql = 'SELECT * FROM courses WHERE id=ANY(SELECT cid FROM subscriptions WHERE uid=:uid)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
                'uid' => $this->id
        ));
        $r = $query->fetchAll();
        $courses = array();
        foreach($r AS $course) {
            $cObj = new Course();
            $cObj->fromDataObject($course);
            $courses[] = $cObj;
        }
        return $courses;
    }

    public function isUser($user) {
        return $user->getID() == $this->id;
    }

    public function getCoursesBy() {
        $sql = 'SELECT * FROM courses WHERE owner=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        $r = $query->fetchAll();
        $courses = array();
        foreach($r AS $course) {
            $cObj = new Course();
            $cObj->fromDataObject($course);
            $courses[] = $cObj;
        }
        return $courses;
    }

    public function getAssetsBy() {
        $sql = 'SELECT * FROM assets WHERE owner=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        $r = $query->fetchAll();
        $assets = array();
        foreach($r AS $asset) {
            $aObj = new Asset();
            $aObj->fromDataObject($asset);
            $assets[] = $aObj;
        }
        return $assets;
    }

    public function getAssetsAsJSON($fileTypes = null) {
        if($fileTypes === null) {
            $sql = 'SELECT * FROM assets WHERE owner=:uid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'uid' => $this->getID()
            ));
        } else {
            $typeStr = implode('\',\'', array_map(function($mime) {
                return str_replace('\'', '', $mime); // Sanitize to make sure no SQL injection is possible
            }, $fileTypes));
            $sql = "SELECT * FROM assets WHERE owner=:uid AND type IN ('$typeStr')";
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'uid' => $this->getID()
            ));
        }
        $r = $query->fetchAll();
        return json_encode($r);
    }

    public function getFollowers() {
        $sql = 'SELECT * FROM users WHERE uid=ANY(SELECT follower FROM followings WHERE following=:uid)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        $r = $query->fetchAll();
        $users = array();
        foreach($r AS $user) {
            $uObj = new User();
            $uObj->fromDataObject($user);
            $users[] = $uObj;
        }
        return $users;
    }

    public function countFollowers() {
        $sql = 'SELECT COUNT(*) FROM followings WHERE following=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        return $query->fetch()[0];
    }

    public function countFollows() {
        $sql = 'SELECT COUNT(*) FROM followings WHERE follower=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        return $query->fetch()[0];
    }

    public function getFollows() {
        $sql = 'SELECT * FROM users WHERE uid=ANY(SELECT following FROM followings WHERE follower=:uid)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        $r = $query->fetchAll();
        $users = array();
        foreach($r AS $user) {
            $uObj = new User();
            $uObj->fromDataObject($user);
            $users[] = $uObj;
        }
        return $users;
    }

    public function follow($currentUser) {
        $sql = 'INSERT INTO `followings`(`follower`, `following`, `follow_date`) VALUES (:follower, :following, CURRENT_DATE)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'follower' => $currentUser->getID(),
            'following' => $this->getID()
        ));
    }

    public function unfollow($currentUser) {
        $sql = 'DELETE FROM followings WHERE follower=:follower AND following=:following';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'follower' => $currentUser->getID(),
            'following' => $this->getID()
        ));
    }

    public function isFollowing($user) {
        $sql = 'SELECT * FROM followings WHERE follower=:follower AND following=:following';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'follower' => $this->getID(),
            'following' => $user->getID()
        ));
        if(!empty($query->fetch())) return true;
        return false;
    }

    public function generateMessageToken() {

        // generate a token
        $token = bin2hex(random_bytes(64));

        // create a stream context to connect to Stargate API
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Accept: application/json\r\nContent-Type: application/json\r\nX-Cassandra-Token: ".STARGATE_API_DB_APPLICATION_TOKEN,
                'content' => json_encode(array(
                    'uid' => $this->getID(),
                    'mtoken' => $token,
                    'online' => true,
                    'uname' => $this->getName()
                ))
            )
        );

        $context = stream_context_create($opts);

        /*
        This is legacy sql:
        
        $sql = 'UPDATE `users` SET `message_token`=:token WHERE uid=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID(),
            'token' => $token
        ));*/

        // opening the stream with context to write to cassandra via stargate API
        if(file_get_contents('https://'.STARGATE_API_DB_ID.'-'.STARGATE_API_DB_REGION.'.apps.astra.datastax.com/api/rest/v2/keyspaces/studezy/users_by_id', false, $context) !== false) return $token;
        return false;
    }

    public function byName($userName) {
        $sql = 'SELECT * FROM users WHERE uname=:name';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'name' => $userName
        ));
        $r = $query->fetch();
        if($r !== false) {
            $this->fromDataObject($r);
            return true;
        }
        return false;
    }

    // Generates a one-time token to send a specific file
    public function getOneTimeMessageSendToken($fileID) {
        $token = bin2hex(random_bytes(64));

        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Accept: application/json\r\nContent-Type: application/json\r\nX-Cassandra-Token: ".STARGATE_API_DB_APPLICATION_TOKEN,
                'content' => json_encode(array(
                    'userid' => $this->getID(),
                    'ott' => $token,
                    'fileid' => $fileID
                ))
            )
        );

        $context = stream_context_create($opts);

        // opening the stream with context to write to cassandra via stargate API
        if(file_get_contents('https://'.STARGATE_API_DB_ID.'-'.STARGATE_API_DB_REGION.'.apps.astra.datastax.com/api/rest/v2/keyspaces/studezy/onetime_send_tokens_by_userid', false, $context) !== false) return $token;
        return false;
    }

    // fetch contacts from Cassandra via Stargate
    public function getContacts() {
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' => "Accept: application/json\r\nContent-Type: application/json\r\nX-Cassandra-Token: ".STARGATE_API_DB_APPLICATION_TOKEN,
            )
        );

        $context = stream_context_create($opts);

        // opening the stream with context to read from Cassandra via stargate API
        $contacts = json_decode(file_get_contents('https://'.STARGATE_API_DB_ID.'-'.STARGATE_API_DB_REGION.'.apps.astra.datastax.com/api/rest/v2/keyspaces/studezy/contacts_by_owner/'.$this->getID().'?fields=user_id,name', false, $context), true);
        return array_map(function($contact) {
            $cU = new User();
            $cU->fromUID($contact['user_id']);
            $cU->setContactName($contact['name']);
            return $cU;
        }, $contacts['data']);
    }

    public function setContactName($name) {
        $this->contactName = $name;
    }

    public function getContactName() {
        return $this->contactName;
    }

    public function generateDocToken() {
        $token = bin2hex(random_bytes(64));

        $sql = 'DELETE FROM doc_tokens WHERE uid=:uid; INSERT INTO `doc_tokens`(`uid`, `token`) VALUES (:uid,:token)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID(),
            'token' => $token
        ));

        return $token;
    }

    public function getSurveyRawData() {
        $sql = 'SELECT * FROM surveys WHERE owner=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        return $query->fetchAll();
    }

    public function getSurveys() {
        return array_map(function($survey) {
            $s = new Survey();
            $s->fromDataObject($survey);
            return $s;
        }, $this->getSurveyRawData());
    }

    public function getAssignmentsRawData() {
        $sql = 'SELECT * FROM assignments WHERE owner=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        return $query->fetchAll();
    }

    public function getAnnouncementsRawData() {
        $sql = 'SELECT * FROM announcements WHERE user=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        return $query->fetchAll();
    }

    public function getChatsRawData() {
        $sql = 'SELECT * FROM chats WHERE chat_id=ANY(SELECT chat_id FROM chat_memberships WHERE uid=:uid)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        return $query->fetchAll();
    }

    public function getDocuments() {
        $sql = 'SELECT * FROM documents WHERE owner=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        return array_map(function($data) {
            $doc = new Document();
            $doc->fromDataObject($data);
            return $doc;
        }, $query->fetchAll());
    }

    public function getSharedDocuments() {
        $sql = 'SELECT * FROM documents WHERE id=ANY(SELECT doc_id FROM document_permissions WHERE user=:uid)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        return array_map(function($data) {
            $doc = new Document();
            $doc->fromDataObject($data);
            return $doc;
        }, $query->fetchAll());
    }

    public function suggestCourses($limit = 10) {
        // suggests random courses, should be adapted later to serve matching courses
        $sql = 'SELECT * FROM courses WHERE private = 0 ORDER BY RAND() LIMIT 10';
        $query = $this->pdo->prepare($sql);
        $query->execute(/*array(
            'lim' => $limit
        )*/);
        return array_map(function($data) {
            $course = new Course();
            $course->fromDataObject($data);
            return $course;
        }, $query->fetchAll());
    }

    public function pushSubscribe($json_subscription_object) {
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Accept: application/json\r\nContent-Type: application/json\r\nX-Cassandra-Token: ".STARGATE_API_DB_APPLICATION_TOKEN,
                'content' => json_encode(array(
                    'uid' => $this->getID(),
                    'data' => $json_subscription_object
                ))
            )
        );

        $context = stream_context_create($opts);

        // opening the stream with context to write to cassandra via stargate API
        if(file_get_contents('https://'.STARGATE_API_DB_ID.'-'.STARGATE_API_DB_REGION.'.apps.astra.datastax.com/api/rest/v2/keyspaces/studezy/push_subscriptions_by_uid', false, $context) !== false) return true;
    }

    public function notify($notification_data) {

        // Create a stream context for access to Cassandra via Stargate API
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' => "Accept: application/json\r\nContent-Type: application/json\r\nX-Cassandra-Token: ".STARGATE_API_DB_APPLICATION_TOKEN,
            )
        );

        $context = stream_context_create($opts);

        $subscription_json = file_get_contents('https://'.STARGATE_API_DB_ID.'-'.STARGATE_API_DB_REGION.'.apps.astra.datastax.com/api/rest/v2/keyspaces/studezy/push_subscriptions_by_uid/'.$this->getID(), false, $context);

        if($subscription_json !== false) {

            $subdata = json_decode($subscription_json, true);

            if($subdata['count'] > 0) {

                $webPush = new WebPush([
                    "VAPID" => [
                        "subject" => "https://example.com",
                        "publicKey" => 'BHJwee-KAwDWYIRO7XreaAf-dldPVunEx-Z8LKEFgL1QKwxH_iYCADDMWY4BhPqsb6DE2OlCVn9vh9r9fwoHnrw',
                        "privateKey" => "Uzn4HwVu-UPsKsM9KymTX9tJyUQSMIAFo8K8vTPxqRQ"
                    ]
                ]);

                $subscription = Subscription::create(json_decode($subdata['data'][0]['data'], true));

                $webPush -> sendOneNotification(
                    $subscription,
                    json_encode($notification_data)
                );

            }
    
        }

    }

    public function sendMail($title, $html_content, $headers = '') {
        $headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
        $headers .= 'From: <no-reply@'.$_SERVER['SERVER_NAME'].'>' . "\r\n";
        mail($this->getMail(), $title, $html_content, $headers);
    }

    public function getTitles() {
        $sql = 'SELECT * FROM official_titles INNER JOIN title_issuers ON title_issuers.id=official_titles.issuer WHERE user=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        return $query->fetchAll();
    }

    // Return a CalendarManager object
    public function getCalendarManager() {
        $cm = new CalendarManager();
        $cm->fromID($this->calendar);
        return $cm;
    }

    public function getVocabularySets() {
        $sql = 'SELECT * FROM vocabularysets WHERE user=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $this->getID()
        ));
        $res = $query->fetchAll();
        return array_map(function($setData) {
            $set = new VocabularySet();
            $set->fromDataObject($setData);
            return $set;
        }, $res);
    }

}

function suggestUserByName($partialUsername) {
    $sql = 'SELECT uname, uid FROM users WHERE uname LIKE :pname;';
    $query = DB_CONNECTION->prepare($sql);
    $query->execute(array(
        'pname' => $partialUsername.'%'
    ));
    return $query->fetchAll();
}

?>