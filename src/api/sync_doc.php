<?php
    require '../res/incl/classes/user.php';
    require '../res/incl/mime2ext.php';

    $u = new User();
    $u->restoreFromSession(true);

    $chap = new Chapter();
    $chap->fromID($_POST['id']);
    $c = $chap->getCourse($u);

    // Delta parser

    if($c->hasWriteAccess($u)) {

        $ops = json_decode($_POST['c'], true)['ops'];
        $content = $chap->getContent($u);
    
        $newDoc = '';
        $off = 0;
    
        function str_get_taglen($str) {
           return strlen($str) - strlen(strip_tags($str));
        }

        function substr_tags($str, $start, $length) {
            /*$sub = substr(trim($str), $start, $length);
        	$add = substr($str, $start + strlen($sub), str_get_taglen($sub));
            $sub .= $add;
            $reverse = strrev($sub);
            $tagOpen = strpos($reverse, '<');
            $tagClose = strpos($reverse, '>');

            $endTag = '';
            if(($tagOpen < $tagClose) AND $tagClose !== false) {
                $endTag = substr($str, $start + strlen($sub));
                $endTag = substr($endTag, 0, strpos($endTag, '>') + 1);
            }

            return $sub.$endTag;*/

            $sub = substr($str, $start);
            $lastTag = substr($sub, 0, strpos($sub, '>'));
            
        }

        $exstr = 'some string <im> some string';
        var_dump(substr_tags($exstr, 1, 19));
    
        foreach($ops AS $op) {
            foreach($op AS $o=>$args) {
                switch($o) {
                    case 'retain':
                        $retain = substr_tags($content, 0, $args);
                        $off += $args - str_get_taglen($retain);
                        var_dump(htmlspecialchars($retain));
                        $newDoc .= $retain;
                        break;
                    case 'insert':
                        //var_dump($args);
                        if(gettype($args) == 'string') {
                            $newDoc .= str_replace(['<', '>'], '', $args);
                            //$off += strlen($args);this is the all new editor by Noel Aeby.
                        } else {
                            $insert_method = array_key_first($args);
                            switch($insert_method) {
                                case 'image':
                                    $s = explode(',', $args[$insert_method]);
                                    $asset = new Asset();
                                    $asset->fromDataObject(array(
                                        'id' => uniqid(),
                                        'name' => uniqid(),
                                        'type' => substr($s[0], 5, strpos($s[0], ';') - 5),
                                        'owner' => $u->getID(),
                                        'course' => $c->getID()
                                    ));
                                    if($c->addAsset($asset)) {
                                        file_put_contents('/var/www/html/assets/'.$asset->getID().'.'.mime2ext($asset->getType()), base64_decode(str_replace(' ', '+', $s[1])));
                                    }
                                    $objTag = '<img src="/assets/'.$asset->getID().'.'.mime2ext($asset->getType()).'">';
                                    $newDoc .= $objTag;
                                break;
                            }
                        }
                        break;
                    case 'delete':
                        // check if this is the beginning of a tag
                        $strip_string = substr(strip_tags($content), $off);
                        if(substr($strip_string,0,1) == '<') {
                            $off += strpos($strip_string, '>');
                        } else $off += $args;
                        //$content = substr($content, 0, $off - $args).substr($content, $off);
                        break;
                }
            }
        }

        $newDoc .= substr($content, $off + str_get_taglen($newDoc));
        $newDoc = str_replace(["\r", "\n"], '<br>', $newDoc);
        $chap->setContent($u, $newDoc);
    } else {
        die('Authentification failed. Refused connection.');
    }

    function anonymous_asset_create($dataURL) {
        // function for creating an asset without specified name or id (=anonymous) from DataURL (passed in deltas)
        
    }

?>