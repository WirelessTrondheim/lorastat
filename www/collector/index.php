<?php

require('/home/arnels/TrT/lorastat/conf.php');

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
    $token = $_GET['token'];
else
    die;

if(!isset($lora_gws[$lora_gw]))
    die;


echo hash('sha256', ($num . $lora_gws[$lora_gw]));
//is the client able to create a correct token?
if(!(hash('sha256', ($num . $lora_gws[$lora_gw])) == $token ))
    die;



echo "\n";
echo hash('sha256', ($num . $lora_gws[$lora_gw]));

echo "nice";

$result = pg_query_params($db,
        'INSERT INTO log (log_id, lora_gw, packets, bytes)
        VALUES($1, $2, $3, $4) ON CONFLICT DO NOTHING;',
        array($num, $lora_gw, $packets, $bytes));
?>
