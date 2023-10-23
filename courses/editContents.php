<?php

    require '../res/incl/classes/user.php';
    require_once '../res/incl/mime2ext.php';

    $u = new User();
    $u->restoreFromSession(true);

    $c = new Course();
    $c->fromID($_GET['i'], $u);

    if(!$c->hasWriteAccess($u)) die();

?><!DOCTYPE html>
<html>
    <head>
        <title>Edit contents of "<?php echo $c->getName(); ?>" | StudEzy</title>
        <?php require '../res/incl/head.php'; ?>
    </head>
    <body>
        <?php require '../res/incl/nav.php'; ?>
        <h1>Edit contents of "<?php echo $c->getName(); ?>"</h1>
        <div>By "<?php echo $c->getOwner()->getName(); ?>"</div>
        <form>
            <textarea id="description_textarea"><?php echo $c->getDescription(); ?></textarea><br>
            <input type="button" value="Save" onclick="saveDescription()">
        </form>
        <h2>Chapters</h2>
        <form>
            <div>
                <input type="button" value="Add chapter" onclick="addChapter()">
            </div>
            <table>
                <thead>
                    <tr>
                        <td>Name</td>
                        <td>Move up</td>
                        <td>Move down</td>
                        <td>Remove</td>
                    </tr>
                </thead>
                <tbody id="contentTable">
                    <?php
                        $contents = $c->getContents();
                        $i = 1;
                        foreach($contents AS $cont) {
                            echo '<tr id="content_'.$cont->getID().'"><td>'.$cont->getName().'</td><td><a href="javascript:;" onclick="moveChapter(this, 1);"><img src="../res/img/keyboard_arrow_up.svg"></a></td><td><a href="javascript:;" onclick="moveChapter(this, 0);"><img src="../res/img/keyboard_arrow_down.svg"></a></td><td><a href="dropChapter?i='.$cont->getID().'"><img src="../res/img/delete.svg"></a></td></tr>';
                            $i++;
                        }
                    ?>
                </tbody>
            </table>
            <h2>Assets</h2>
            <div>
                <a href="addAsset?i=<?php echo $c->getID(); ?>"><input type="button" value="Add asset"></a>
            </div>
            <table>
                <thead>
                    <tr>
                        <td>#</td>
                        <td>Type</td>
                        <td>Name</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $i = 1;
                        $assets = $c->getAssets();
                        foreach($assets AS $asset) {
                            echo '<tr><td>'.$i.'</td><td>'.$asset->getType().'</td><td><a href="'.ASSET_REMOTE_LOCATION_ROOT.$asset->getID().'.'.mime2ext($asset->getType()).'">'.$asset->getName().'</a></td><td><a href="dropAsset.php?i='.$asset->getID().'"><img src="/res/img/delete.svg"></a></td></tr>';
                            $i++;
                        }
                    ?>
                </tbody>
            </table>
            <div>

            </div>
            <script src="../res/js/core.js"></script>
            <script src="../res/js/contentEditingOptions.js"></script>
        </form>
    </body>
</html>