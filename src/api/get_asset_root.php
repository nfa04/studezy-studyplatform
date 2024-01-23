<?php
    define('SERVER_CONFIG', json_decode(file_get_contents(__DIR__.'/../../.studezy-server-vars.json'), true));
    echo SERVER_CONFIG['ASSET_REMOTE_LOCATION_ROOT'];
?>