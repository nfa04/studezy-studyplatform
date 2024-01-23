<?php

    require '../res/incl/classes/user.php';
    require '../res/incl/classes/document.php';

    $u = new User();
    $u->restoreFromSession(true);

    $doc = new Document();
    $doc->fromID($_GET['i']);

?><!DOCTYPE html>
<html>
    <head>
        <title>"<?php echo $doc->getName(); ?>" - Document Details | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1><?php echo $doc->getName(); ?></h1>
        <h2>Document details</h2>
        <table>
            <thead>
                <tr>
                    <td>Property</td>
                    <td>Value</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Owner</td>
                    <td><?php echo $doc->getOwner()->getName(); ?></td>
                </tr>
                <tr>
                    <td>Co-Authors</td>
                    <td><?php
                        $ca = $doc->getCoAuthors();
                        echo implode(', ', array_map(function($author) {
                            return $author->getName();
                        }, $ca));
                    ?></td>
                </tr>
                <tr>
                    <td>Last edited</td>
                    <td><?php echo $doc->getLastEditTime(); ?></td>
                </tr>
                <tr>
                    <td>Read access</td>
                    <td><?php echo ($doc->hasReadAccess($u) ? 'Yes': 'No'); ?></td>
                </tr>
                <tr>
                    <td>Write access</td>
                    <td><?php echo ($doc->hasWriteAccess($u) ? 'Yes': 'No'); ?></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>