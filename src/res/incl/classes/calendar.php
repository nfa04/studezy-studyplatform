<?php

class CalendarManager {

    private $subscriptions;
    private $entries;
    private $id;
    private $title;
    private $description;

    function __construct() {
        // Establish a db connection
        $this->pdo = DB_CONNECTION;
    }

    public function fromID($id) {
        $sql = 'SELECT * FROM `calendars` WHERE id=:caid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'caid' => $id
        ));
        $this->fromDataObject($query->fetch());
    }

    public function fromDataObject($obj) {
        $this->title = $obj['title'];
        $this->description = $obj['description'];
        $this->id = $obj['id'];
    }

    /*public function importFromSubscriptions() {
        foreach($this->getSubscriptions() AS $subs) {
            foreach($subs AS $sub) {
                $this->entries = array_merge($this->entries, $sub->getCalendarManager()->getEntries());
            }
        }
    }*/

    public function getID() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }
    
    public function getDescription() {
        return $this->description;
    }

    // Queries for and returns all entries to this calendar. If required includes entries from subscriptions
    public function getEntries($dateFrom = '1970-01-01 00:00:00', $dateUntil = '3000-01-01 00:00:00', $includeSubs = true, $includePrivate = true) {
        $sql = ($includePrivate ? 'SELECT * FROM calendar_entries WHERE calendar_id=:caid AND (time BETWEEN :dfrom AND :duntil)' : 'SELECT * FROM calendar_entries WHERE calendar_id=:caid AND (time BETWEEN :dfrom AND :duntil) AND private=0');
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'caid' => $this->id,
            'dfrom' => $dateFrom,
            'duntil' => $dateUntil
        ));
        $entries = $query->fetchAll();
        $entryObjs = array_map(function($entObj) {
            $entry = new CalendarEntry();
            $entry->fromDataObject($entObj);
            return $entry;
        }, $entries);
        if($includeSubs) {
            $includeList = array();
            function fetchAndAddParent($child, &$includeList, &$entryObj, $dateFrom, $dateUntil) {
                // Now query all subscriptions (as dependencies) and recursively add them
                $subs = $child->getSubscriptions();
                foreach($subs AS $sub) {
                    // Check if the dependency was already added
                    if(!in_array($sub->getParentID(), $includeList)) {
                        $pcm = $sub->getParentCalendarManager();
                        $pentries = $pcm->getEntries($dateFrom, $dateUntil, false, false);
                        if($pentries !== false) $entryObj = array_merge($entryObj, $pentries);
                        fetchAndAddParent($pcm, $includeList, $entryObj, $dateFrom, $dateUntil);
                        $includeList[] = $sub->getParentID();
                    }
                }
            }
            fetchAndAddParent($this, $includeList, $entryObjs, $dateFrom, $dateUntil);
        }
        // Sort the entries by dates
        usort($entryObjs, function($a, $b) {
            return strtotime($a->getDateTime()) - strtotime($b->getDateTime());
        });
        return $entryObjs;
        }

    public function getSubscriptions() {
        $sql = 'SELECT * FROM calendar_subscriptions WHERE child=:caid';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(
            'caid' => $this->id
        ));
        $subs = $query->fetchAll();
        return array_map(function($subObj) {
            $sub = new CalendarSubscription();
            $sub->fromDataObject($subObj);
            return $sub;
        }, $subs);
    }

    public function addEntry($title, $description, $currentUser, $start_time, $end_time, $private) {
        $data = array(
            'id' => uniqid(),
            'title' => $title,
            'description' => $description,
            'calendar_id' => $this->getID(),
            'owner' => $currentUser->getID(),
            'time' => $start_time,
            'end_time' => $end_time,
            'private' => $private
        );
        $sql = 'INSERT INTO `calendar_entries`(`id`, `calendar_id`, `owner`, `title`, `description`, `time`, `end_time`, `private`) VALUES (:id,:calendar_id,:owner,:title,:description,:time,:end_time,:private)';
        $query = DB_CONNECTION->prepare($sql);
        $query->execute($data);
        $entry = new CalendarEntry();
        $entry->fromDataObject($data);
        return $entry;
    }

}

class CalendarSubscription {

    private $parent;
    private $child;

    public function fromDataObject($obj) {
        $this->parent = $obj['parent'];
        $this->child = $obj['child'];
    }

    public function getParentCalendarManager() {
        $pc = new CalendarManager();
        $pc->fromID($this->parent);
        return $pc;
    }

    public function getParentID() {
        return $this->parent;
    }

}

class CalendarEntry {

    private $id;
    private $datetime;
    private $owner;
    private $title;
    private $description;
    private $calendar;
    private $endtime;

    function __construct() {

    }

    public function fromID($id) {
        $sql = 'SELECT * FROM `calendar_entries` WHERE id=:ceid';
        $query = DB_CONNECTION->prepare($sql);
        $query->execute(array(
            'ceid' => $id
        ));
        $this->fromDataObject($query->fetch());
    }

    public function fromDataObject($obj) {
        $this->id = $obj['id'];
        $this->datetime = $obj['time'];
        $this->owner = $obj['owner'];
        $this->title = $obj['title'];
        $this->description = $obj['description'];
        $this->calendar = $obj['calendar_id'];
        $this->endtime = $obj['end_time'];
    }

    public function getID() {
        return $this->id;
    }

    public function getDateTime() {
        return $this->datetime;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getOwner() {
        $user = new User();
        $user->fromUID($this->owner);
        return $user;
    }

    public function getOwnerID() {
        return $this->owner;
    }

    public function getCalendarManager() {
        $cmanager = new CalendarManager();
        $cmanager->fromID($this->calendar);
        return $cmanager;
    }

    public function getEndDateTime() {
        return $this->endtime;
    }

    public function hasWriteAccess($currentUser) {
        return $this->getOwner()->isUser($currentUser);
    }

    public function remove($currentUser) {
        if($this->hasWriteAccess($currentUser)) {
            $sql = 'DELETE FROM calendar_entries WHERE id=:id';
            $query = DB_CONNECTION->prepare($sql);
            $query->execute(array(
                'id' => $this->getID()
            ));
        }
    }
}

?>