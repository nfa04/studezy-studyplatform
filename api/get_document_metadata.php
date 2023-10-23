<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/document.php';

    $u = new User();
    
    if($u->restoreFromSession()) {

        $doc = new Document();
        $doc->fromID($_POST['id']);

        $owner = $doc->getOwner();

        echo json_encode(array(
            'name' => $doc->getName(),
            'ownerID' => $owner->getID(),
            'ownerName' => $owner->getName(),
            'co-authors' => array_map(function($author) {
                global $doc;
                return array(
                    'name' => $author->getName(),
                    'id' => $author->getID(),
                    'write_access' => $doc->hasWriteAccess($author)
                );
            }, $doc->getCoAuthors(true)),
            'private' => $doc->isPrivate()
        ));

    }

?>