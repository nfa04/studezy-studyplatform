<?php

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {
        $id = uniqid();
        $a = new Asset();
        $a->fromID($_POST['assetID']);

        $fileHandle = fopen('php://temp/maxmemory:0', 'rw');
        fputs($fileHandle, '');
        rewind($fileHandle);

        // Write the file onto S3 object storage using REST API
        $ch = curl_init(AWS_ROOT_LOCATION.'chat-'.$id.'.'.mime2ext($a->getType()));
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, $fileHandle);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'x-amz-website-redirect-location: '.$a->getID().mime2ext($a->getType())));
        curl_setopt($ch, CURLOPT_INFILESIZE, 0);

        echo json_encode(array(
            'fileID' => $id,
            'ott' => $u->getOneTimeMessageSendToken($id)
        ));
    }

?>