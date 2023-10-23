<?php

define('COURSE_DATA_DIRECTORY', AWS_DOCSTORE_ROOT.'chapter-');

require __DIR__.'/chapter.php';
require __DIR__.'/asset.php';
require __DIR__.'/announcement.php';
require __DIR__.'/assignment.php';

class Course {
    private $pdo;
    private $id;
    private $name;
    private $description;
    private $private;
    private $ownerID;
    private $access_key;
    private $subscriptionCount;
    private $contents;
    private $lastOpened;
    private $calendarID;

    function __construct() {
        $this->pdo = DB_CONNECTION;
    }

    public function fromDataObject($obj) {
        $this->id = $obj['id'];
        $this->name = $obj['name'];
        $this->description = $obj['description'];
        $this->private = $obj['private'];
        $this->access_key = $obj['access_key'];
        $this->ownerID = $obj['owner'];
        @$this->subscriptionCount = $obj['subscribers'];
        @$this->lastOpened = $obj['last_opened'];
        @$this->calendarID = $obj['calendar_id'];
    }

    public function fromID($id, $currentUser) {
        $sql = 'CALL get_course(:id, :uid, true, @p2, @p3, @p4, @p5, @p6, @p7, @p8, @p9, @p10);';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'id' => $id,
            'uid' => $currentUser->getID()
        ));
        $r = $query->fetch();
        $this->fromDataObject($r);
    }

    public function getID() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }    
    
    public function getDataDirectory() {
        return COURSE_DATA_DIRECTORY.$this->id;
    }

    public function getOwner() {
        $u = new User();
        $u->fromUID($this->ownerID);
        return $u;
    }

    public function isPrivate() {
        return $this->private;
    }

    public function countSubscribers() {
        return $this->subscriptionCount;
    }

    public function checkAccessKey($key) {
        return password_verify($key, $this->access_key);
    }

    public function getCalendarManager() {
        $ca = new CalendarManager();
        $ca->fromID($this->calendarID);
        return $ca;
    }

    public function hasReadAccess($user) {
        if(!$this->isPrivate()) return true;
        else if($this->getOwner()->isUser($user)) return true;
        else {
            $sql = 'SELECT * FROM subscriptions WHERE uid=:uid AND cid=:cid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'uid' => $user->getID(),
                'cid' => $this->id
            ));
            $r = $query->fetch();
            if($r !== false) return true;
        }
        return false;
    }

    public function hasWriteAccess($user) {
        if($user->isUser($this->getOwner())) return true;
        return false;
    }

    public function subscribe($user) {
        $sql = 'CALL subscribe(:uid, :cid, @p3);';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $user->getID(),
            'cid' => $this->id
        ));
        return $query->fetch()['success'];
    }

    public function unsubscribe($user) {
        $sql = 'DELETE FROM subscriptions WHERE cid=:cid AND uid=:uid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'cid' => $this->getID(),
            'uid' => $user->getID()
        ));
    }

    public function getContents() {
        if($this->contents === NULL) {
            $sql = 'SELECT * FROM chapters WHERE course=:id ORDER BY nr ASC';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'id' => $this->id
            ));
            $response = $query->fetchAll();
            $contents = array();
            foreach($response AS $chap) {
                $c = new Chapter();
                $c->fromDataObject($chap);
                $contents[] = $c;
            }
            $this->contents = $contents;
        }
        return $this->contents;
    }

    public function getNewContents() {
        $contents = $this->getContents();
        $new = array();
        foreach($contents AS $content) {
            if(strtotime($content->getCreated()) > strtotime($this->lastOpened)) {
                $new[] = $content;
            }
        }
        return $new;
    }

    public function touch($user) {
        $sql = 'UPDATE subscriptions SET last_used=CURRENT_TIMESTAMP WHERE uid=:uid AND cid=:cid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $user->getID(),
            'cid' => $this->id
        ));
    }

    public function getAssets() {
        $sql = 'SELECT * FROM assets WHERE course=:id ORDER BY i DESC';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'id' => $this->id
        ));
        $assets = [];
        $data = $query->fetchAll();
        foreach($data AS $asset) {
            $a = new Asset();
            $a->fromDataObject($asset);
            $assets[] = $a;
        }
        return $assets;
    }

    public function changeOrder($currentUser, $newOrder) {
        if($this->hasWriteAccess($currentUser)) {
            $sql = 'CALL change_chapter_order(:id, :order);';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'id' => $this->getID(),
                'order' => $newOrder
            ));
            return true;
        }
        return false;
    }

    function addEmptyChapter($name, $currentUser) {
        if($this->hasWriteAccess($currentUser)) {
            $id = uniqid();
            $data = array(
                'id' => $id,
                'name' => $name,
                'course' => $this->getID(),
            );
            $sql = 'CALL add_chapter(:id, :name, :course, @success);';
            $query = $this->pdo->prepare($sql);
            $query->execute($data);
            if($query->fetch()['success']) {
                $query->closeCursor();
                $chap = new Chapter();
                $chap->fromDataObject($data);
                /*@mkdir($this->getDataDirectory());
                $filePath = $chap->getFilePath($currentUser);
                fopen($filePath, 'w');
                chmod($filePath, 0666);
                $parchmentPath = $chap->getParchmentFilePath($currentUser);
                file_put_contents($parchmentPath, '{}');
                chmod($parchmentPath, 0666);*/

                // Create a temporary file to upload the string in memory (required to get a filehandle for cURL)
                $fileHandle = fopen('php://temp/maxmemory:2', 'rw');
                fputs($fileHandle, '{}');
                rewind($fileHandle);

                // Write the file onto S3 object storage using REST API
                $ch = curl_init($chap->getParchmentFilePath($currentUser));
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_INFILE, $fileHandle);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_INFILESIZE, 2);
                if(curl_exec($ch) !== false) return $chap;
                return false;
            } else return false;
        }
    }

    function addAsset($asset) {
        $sql = 'CALL add_asset(:id, :name, :type, :owner, :course, @p);';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'id' => $asset->getID(),
            'name' => $asset->getName(),
            'type' => $asset->getType(),
            'owner' => $asset->getOwnerID(),
            'course' => $asset->getCourseID()
        ));

        return $query->fetch()['success'];
    }

    function getAnnouncementsRawData() {
        $sql = 'SELECT * FROM announcements WHERE course=:cid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'cid' => $this->id
        ));
        return $query->fetchAll();
    }

    function getAnnouncements() {
        return array_map(function($a) {
            $announcement = new Announcement();
            $announcement->fromDataObject($a);
            return $announcement;
        }, $this->getAnnouncementsRawData());
    }

    function addAnnouncement($title, $content, $currentUser) {
        $a = new Announcement();
        $a->fromDataObject(array(
            'id' => uniqid(),
            'title' => $title,
            'content' => $content,
            'user' => $currentUser->getID(),
            'course' => $this->getID()
        ));
        $a->create($currentUser);
        return $a;
    }

    function setDescription($description, $currentUser) {
        if($this->hasWriteAccess($currentUser)) {
            $sql = 'UPDATE `courses` SET `description`=:description WHERE id=:cid';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(
                'description' => $description,
                'cid' => $this->getID()
            ));
            return true;
        }
        return false;
    }

    public function getAssignmentsRawData() {
        $sql = 'SELECT * FROM assignments WHERE course=:cid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'cid' => $this->getID()
        ));
        return $query->fetchAll();
    }

    public function getAssignments() {
        return array_map(function($data) {
            $a = new Assignment();
            $a->fromDataObject($data);
            return $a;
        }, $this->getAssignmentsRawData());
    }

    public function addAssignment($title, $description, $due, $currentUser) {
        $a = new Assignment();
        $a->fromDataObject(array(
            'title' => $title,
            'description' => $description,
            'course' => $this->getID(),
            'due' => $due
        ));
        return $a->create($currentUser);
    }

    public function isSubscriber($user) {
        $sql = 'SELECT COUNT(*) FROM subscriptions WHERE cid=:cid AND uid=:uid;';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'cid' => $this->getID(),
            'uid' => $user->getID()
        ));
        return $query->fetch()['COUNT(*)'];
    }

    public function notifySubscribers($notification_data) {

        // Notify users subscribed to web-push notifications
        $sql = 'SELECT * FROM users WHERE uid=ANY(SELECT uid FROM subscriptions WHERE cid=:cid AND notify=1)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'cid' => $this->getID()
        ));
        foreach($query->fetchAll() AS $userdata) {
            $user = new User();
            $user->fromDataObject($userdata);
            $user->notify($notification_data);
        }

        // Notify users subscribed to email notification service
        $sql = 'SELECT * FROM users WHERE uid=ANY(SELECT uid FROM subscriptions WHERE cid=:cid AND email_notify=1)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'cid' => $this->getID()
        ));
        $html_content = str_replace('STYLE_PLACEHOLDER', file_get_contents(__DIR__.'/../../css/main.css'), 
            str_replace('TARGET_URL', 'https://'.$_SERVER['SERVER_NAME'].$notification_data['url'],
                str_replace('UPDATE_TYPE', $notification_data['utype'], 
                    file_get_contents(__DIR__.'/../email_templates/course_update.htm')
                )
            )
        );
        foreach($query->fetchAll() AS $userdata) {
            $user = new User();
            $user->fromDataObject($userdata);
            $user->sendMail('Course "'.$this->getName().'" updated', $html_content);
        }

    }

    public function addSurvey($title, $description, $show_answers, $currentUser) {
        $survey = new Survey();
        $survey->fromDataObject(array(
            'id' => uniqid(),
            'owner' => $currentUser->getID(),
            'title' => $title,
            'description' => $description,
            'course' => $this->getID(),
            'show_answers' => $show_answers
        ));
        $survey->create($currentUser);
        return $survey;
    }

    public function getSurveys() {
        $sql = 'SELECT * FROM `surveys` WHERE course=:cid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'cid' => $this->getID()
        ));
        return array_map(function($sdata) {
            $survey = new Survey();
            $survey->fromDataObject($sdata);
            return $survey;
        }, $query->fetchAll());
    }

    public function hasPushSubscribed($user) {
        if(!$this->isSubscriber($user)) return false;
        $sql = 'SELECT notify FROM subscriptions WHERE uid=:uid AND cid=:cid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $user->getID(),
            'cid' => $this->getID()
        ));
        return $query->fetch()['notify'];
    }

    public function setNotification($user, $subscribed) {
        $sql = 'UPDATE `subscriptions` SET `notify`=:subscribed WHERE uid=:uid AND cid=:cid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'subscribed' => $subscribed,
            'uid' => $user->getID(),
            'cid' => $this->getID()
        ));
        return true;
    }

    public function hasEmailSubscribed($user) {
        if(!$this->isSubscriber($user)) return false;
        $sql = 'SELECT email_notify FROM subscriptions WHERE uid=:uid AND cid=:cid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'uid' => $user->getID(),
            'cid' => $this->getID()
        ));
        return $query->fetch()['email_notify'];
    }

    public function setEmailSubscription($user, $subscribed) {
        $sql = 'UPDATE `subscriptions` SET `email_notify`=:subscribed WHERE uid=:uid AND cid=:cid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'subscribed' => $subscribed,
            'uid' => $user->getID(),
            'cid' => $this->getID()
        ));
        return true;
    }

    public function countChapters() {
        $sql = 'SELECT COUNT(*) FROM chapters WHERE course=:cid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'cid' => $this->getID()
        ));
        return $query->fetch()['COUNT(*)'];
    }

    public function getOwnerID() {
        return $this->ownerID;
    }

    public function create() {
        $sql = 'INSERT INTO `courses`(`id`, `name`, `description`, `owner`, `private`, `access_key`, `calendar_id`) VALUES (:id,:name,:description,:owner,:private,:access_key,:caid); INSERT INTO `calendars`(`id`, `title`, `description`) VALUES (:caid,:catitle,\'\')';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'id' => $this->getID(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'owner' => $this->getOwnerID(),
            'private' => $this->isPrivate(),
            'access_key' => $this->access_key,
            'caid' => uniqid(),
            'catitle' => $this->getName().' Course Calendar'
        ));
    }

    public function setCalendarSubscription($user, $subscribed) {
        if($subscribed) $sql = 'INSERT INTO `calendar_subscriptions`(`child`, `parent`, `created`) VALUES (:ccaid,:pcaid,CURRENT_TIMESTAMP)';
        else $sql = 'DELETE FROM calendar_subscriptions WHERE child=:ccaid AND parent=:pcaid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'ccaid' => $user->getCalendarManager()->getID(),
            'pcaid' => $this->calendarID
        ));
    }

    public function hasCalendarSubscribed($user) {
        $sql = 'SELECT parent FROM calendar_subscriptions WHERE child=:ccaid AND parent=:pcaid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'ccaid' => $user->getCalendarManager()->getID(),
            'pcaid' => $this->calendarID
        ));
        if($query->fetch() !== false) return true;
        return false;
    }

}

function getPopularCourses() {
    // Query finds 10 courses with the highest count of new subscriptions in the last 30 days
    $sql = 'SELECT * FROM (SELECT cid, COUNT(subscriptions.cid) FROM `subscriptions` WHERE DATEDIFF(CURRENT_DATE, subscriptions.joined) < 30 GROUP BY cid ORDER BY COUNT(subscriptions.cid)) AS tmp_tbl INNER JOIN courses ON courses.id = tmp_tbl.cid LIMIT 10;';
    $query = DB_CONNECTION->prepare($sql);
    $query->execute();
    return array_map(function($data) {
        $course = new Course();
        $course->fromDataObject($data);
        return $course;
    }, $query->fetchAll());
}

?>
