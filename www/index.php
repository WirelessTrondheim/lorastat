<?php

require('/home/arnels/TrT/lorastat/conf.php');
require('/home/arnels/TrT/lorastat/func.php');

$db = pg_connect("$host $port $dbname $credentials");

if(!$db) {
    die("Error : Unable to open database\n");
}

$result = pg_query($db, "SELECT DISTINCT lora_gw,
        EXTRACT(EPOCH FROM (NOW() - MAX(time))) AS last_seen,
        SUM(CASE WHEN ((NOW() - time) < INTERVAL '10 MINUTES') THEN packets END)
            AS packets_last_10_min,
        SUM(CASE WHEN ((NOW() - time) < INTERVAL '1 DAYS') THEN packets end)
            AS packets_last_1_days,
        SUM(CASE WHEN ((NOW() - time) < INTERVAL '1 WEEKS') THEN packets END)
            AS packets_last_1_weeks,
        SUM(CASE WHEN ((NOW() - time) < INTERVAL '10 MINUTES') THEN bytes END)
            AS bytes_last_10_min,
        SUM(CASE WHEN ((NOW() - time) < INTERVAL '1 DAYS') THEN bytes END)
            AS bytes_last_1_days,
        SUM(CASE WHEN ((NOW() - time) < INTERVAL '1 WEEKS') THEN bytes END)
            AS bytes_last_1_weeks
        FROM log GROUP BY lora_gw ORDER BY lora_gw;");

if(!$result) {
    die("Error : Unable to fetch DB");
}


?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>TrT - Lorastat</title>
  <meta name="description" content="">

  <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

  <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/custom.css">

  <!-- Favicon
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="icon" type="image/png" href="images/favicon.png">

</head>
<body>

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container last-seen-grid" style="padding-top: 5%">
<?php

$i = 0;
while($row = pg_fetch_row($result)) {

    $lora_gw = $row[0];
    $last_seen = floor($row[1]);
    $packets_last_10_mins = $row[2];
    $packets_last_1_days = $row[3];
    $packets_last_1_week = $row[4];
    $bytes_last_10_mins = $row[5];
    $bytes_last_1_days = $row[6];
    $bytes_last_1_weeks = $row[7];

    $gateway_status = '';
    if($last_seen < 60) {
        $gateway_status = 'gateway-up';
    }
    else {
        $gateway_status = 'gateway-down';
    }

    if($i % 2 == 0) {
        echo '
            <div class="row" style="">
          ';
    }
    echo '<div class="six columns gateway ' . $gateway_status . '"
        style="padding-left: 5px; margin-top: 4%">';
    echo '<h4>' . $lora_gw . '</h4>';
    echo '<ul>
            <li>Seen ' . relativeTime($last_seen) . '</li>
            <li>Relayed ' . $packets_last_1_days .' packets today</li>
            <li>Relayed ' . formatBytes($bytes_last_1_days) . ' today</li>
          </ul>
        </div>
      ';
    if($i % 2 != 0) {
        echo '</div>';
    }
    $i++;
}
?>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
</html>
