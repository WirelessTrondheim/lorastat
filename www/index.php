<?php

require('/generic/place');

$db = pg_connect( "$host $port $dbname $credentials"  );

if(!$db) {
   die("Error : Unable to open database\n");
}

if(isset($_GET['lora_gw']))
    $lora_gw = $_GET['lora_gw'];
else
    die;

if(isset($_GET['packets']))
    $packets = $_GET['packets'];
else
    die;

if(isset($_GET['bytes']))
    $bytes = $_GET['bytes'];
else
    die;

if(isset($_GET['num']))
    $num = $_GET['num'];
else
    die;

if(isset($_GET['token']))
    $lora_gw = $_GET['token'];
else
    die;




echo "nice";

    /*$result = pg_query_params($db, 'insert into log (lora_gw, packets, bytes) 
            values($1, $2, $3);', array('testgw', 4, 5));
   */
?>
