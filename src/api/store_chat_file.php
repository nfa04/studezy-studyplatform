<?php

    ini_set('post_max_size', '2GB');
    ini_set('upload_max_filesize', '2GB');

    require '../res/incl/classes/user.php';

    $u = new User();

    if($u->restoreFromSession()) {
        $id = uniqid();

        $tmpLocation = ASSET_INTERNAL_TMP_LOCATION.'chat-'.$id.'.'.mime2ext($_FILES['file']['type']);
        move_uploaded_file($_FILES['file']['tmp_name'], $tmpLocation);
        
        $ch = curl_init(AWS_ROOT_LOCATION.'chat-'.$id.'.'.mime2ext($_FILES['file']['type']));
        curl_setopt($ch, CURLOPT_PUT, true);
        $fileHandle = fopen($tmpLocation, 'r');
        curl_setopt($ch, CURLOPT_INFILE, $fileHandle);
        // Set header to make the file have the right type on S3
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: '.$_FILES['file']['type']));
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmpLocation));
        unlink($tmpLocation);

        echo json_encode(array(
            'fileID' => 'chat-'.$id,
            'ott' => $u->getOneTimeMessageSendToken($id)
        ));
    }

?>