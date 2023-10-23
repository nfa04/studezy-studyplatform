<?php

    require '../res/incl/classes/user.php';
    require_once '../res/incl/mime2ext.php';

    $u = new User();
    
    if($u->restoreFromSession()) {
        $a = new Assignment();
        $a->fromID($_POST['assignment_id']);

        $assetID = uniqid();
        $internalFileName = ASSET_INTERNAL_TMP_LOCATION.$assetID.'.'.mime2ext($_FILES['file']['type']);

        move_uploaded_file($_FILES['file']['tmp_name'], $internalFileName);

        // Move the file to Amazon S3 for storing
        $ch = curl_init(AWS_ROOT_LOCATION.$assetID.'.'.mime2ext($_FILES['file']['type']));
        curl_setopt($ch, CURLOPT_PUT, true);
        $fileHandle = fopen($internalFileName, 'r');
        curl_setopt($ch, CURLOPT_INFILE, $fileHandle);

        // Set header to make the file have the right type on S3
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: '.$_FILES['file']['type']));

        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($internalFileName));
        curl_exec($ch);

        // Now remove the file from the webserver
        unlink($internalFileName);

        // Submit the file
        $a->submit($assetID, $_FILES['file']['type'], $_POST['file_name'], $u);
    }

?>