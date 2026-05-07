<?php

// API Comet сервера

function CometQL($CometQuery){
    $Comet_devid = '1413'; // id разработчика
    $Comet_devkey = 'oNInYLaqoderk4ajK5UzccA2LFwWg12PQS6K5C6zgeEYA297CynGubb5DtzBXUpa'; // ключ разработчика
    ini_set('display_errors','on');
    error_reporting(E_ALL);
    $link = mysqli_connect("app.comet-server.ru", $Comet_devid, $Comet_devkey, "CometQL_v1");
    if (mysqli_connect_errno()){ CometQL($CometQuery); exit(); };
    $result = mysqli_query ( $link, $CometQuery );
    mysqli_close ( $link );
}

