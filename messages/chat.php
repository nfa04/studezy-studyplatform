<?php

  require '../res/incl/classes/user.php';
  header('Access-Control-Allow-Origin: '.CHATSERVER_REMOTE_LOCATION);
  
  $u = new User();
  $u->restoreFromSession(true);

?><!DOCTYPE html>
<html>
  <head>
    <title>Your messages | StudEzy</title>
    <?php require '../res/incl/head.php'; ?>
    <link rel="stylesheet" href="/res/css/message.css">
  </head>
  <body>
    <?php require '../res/incl/nav.php'; ?>
    <div id="left_chatbar">
      <h1>StudEzy Messages</h1>
      <div>
        <a id="new_chat"><img src="/res/img/plus.svg" height="20"> New chat</a> 
        <a id="new_contact"><img src="/res/img/plus.svg" height="20"> New contact</a>
      </div><br>
      <div id="chat_container"></div>
    </div>
    <div id="top_chatbar">
      <span>
          <img src="/res/img/group.svg" id="active_chat_img">
      </span>
      <span id="active_chat_name">No chat selected</span>
      <!--<span id="calling_options">
        <img src="/res/img/call.svg" id="call_option">
        <img src="/res/img/video_call.svg" id="videocall_option">
      </span>-->
    </div>
    <div id="messages"></div>
    <form id="form" action="">
      <input id="input" autocomplete="off" /><!--<a href="javascript:;" onclick="window.location.href = 'sendAttachment?i=' + chat;"><img src="/res/img/attachment.svg"></a>--><button>Send</button>
    </form>
    <script src="/res/lib/socket.io/socket.io.js"></script>
    <script src="/res/js/core.js"></script>
    <script src="/res/js/userSelector.js"></script>
    <script src="/res/js/message.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/node-forge@1.0.0/dist/forge.min.js"></script>-->
  </body>
</html>
