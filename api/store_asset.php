<?php

    require '../res/incl/classes/user.php';
    require_once '../res/incl/mime2ext.php';

    ini_set('post_max_size', '2GB');
    ini_set('upload_max_filesize', '2GB');

    $u = new User();
    $u->restoreFromSession(true);

    $course = new Course();
    $course->fromID($_POST['cid'], $u);

    $asset = new Asset();
    $asset->fromDataObject(array(
        'id' => uniqid(),
        'name' => $_POST['name'],
        'type' => $_FILES['file']['type'],
        'owner' => $u->getID(),
        'course' => $_POST['cid'],
    ));

    $tmpLocation = ASSET_INTERNAL_TMP_LOCATION.$asset->getID().'.'.mime2ext($_FILES['file']['type']);

    if($course->addAsset($asset)) move_uploaded_file($_FILES['file']['tmp_name'], $tmpLocation);

    // Move the file to Amazon S3 for storing
    $ch = curl_init(AWS_ROOT_LOCATION.$asset->getID().'.'.mime2ext($_FILES['file']['type']));
    curl_setopt($ch, CURLOPT_PUT, true);
    $fileHandle = fopen($tmpLocation, 'r');
    curl_setopt($ch, CURLOPT_INFILE, $fileHandle);

    // Set header to make the file have the right type on S3
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: '.$_FILES['file']['type']));

    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmpLocation));
    curl_exec($ch);

    // Now remove the file from the webserver
    unlink($tmpLocation);

?>