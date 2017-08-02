<?php

require('/home/arnels/TrT/lorastat/conf.php');
require('/home/arnels/TrT/lorastat/func.php');

$db = pg_connect("$host $port $dbname $credentials");

if(!$db) {
    die("Error : Unable to open database\n");
}

$result_status = pg_query($db, "SELECT DISTINCT lora_gw,
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

if(!$result_status) {
    die("Error : Unable to fetch DB");
}

$result_24_hours = pg_query($db,
        "SELECT EXTRACT(HOUR FROM time) AS hour,
        SUM(packets) AS packets_relayed
        FROM log WHERE ((NOW() - time) < INTERVAL '1 DAYS')
        GROUP BY EXTRACT(HOUR FROM time)
        ORDER BY EXTRACT(HOUR FROM time);");

if(!$result_24_hours) {
    die("Error : Unable to fetch DB");
}

$result_31_days = pg_query($db,
        "SELECT to_char(DATE_TRUNC('DAY', time), 'YYYY-MM-DD'),
        SUM(packets) AS packets_relayed FROM log 
        WHERE ((NOW() - time) < INTERVAL '31 DAYS')
        GROUP BY 1 ORDER BY 1;");

if(!$result_31_days) {
    die("Error : Unable to fetch DB");
}

$columns = array(
	array('label' => 'Hour of the day', 'type' => 'string'),
	array('label' => 'Packets per hour last day', 'type' => 'number')
);
$table_24_hours = db_result_to_chart_data($result_24_hours, $columns);

$columns = array(
	array('label' => 'Day', 'type' => 'string'),
	array('label' => 'Packets per day', 'type' => 'number')
);
$table_31_days = db_result_to_chart_data($result_31_days, $columns);

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

  <!-- Javascript
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

</head>
<body>

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container last-seen-grid" style="padding-top: 5%">
<?php

$i = 0;
while($row = pg_fetch_row($result_status)) {

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
if($i % 2 != 0) {
    echo '</div>';
}
?>

  </div>

  <div class="container" style="margin-top: 4%">
    <div class="row">
      <div class="tvelwe columns">
        <div id="columnchart_hour"></div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="tvelwe columns">
        <div id="columnchart_day"></div>
      </div>
    </div>
  </div>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->

<script type="text/javascript">
    google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(drawChart_hour);
    google.charts.setOnLoadCallback(drawChart_day);
    function drawChart_hour() {
          var data = new google.visualization.DataTable(
        <?php
        echo json_encode($table_24_hours);
        ?>
      );

      var view = new google.visualization.DataView(data);

      var options = {
	chartArea: {
	  // leave room for y-axis labels
	  width: '90%'
	},
	legend: {
	  position: 'top'
	},
	width: '100%',
	height: 400,
	colors: ['#64B5F6']
      };

      var chart = new
          google.visualization.ColumnChart(document.getElementById("columnchart_hour"));
      chart.draw(view, options);
      function resize () {
              // change dimensions if necessary
              chart.draw(data, options);
      }
      if (window.addEventListener) {
              window.addEventListener('resize', resize);
      }
      else {
              window.attachEvent('onresize', resize);
      }

  }
    function drawChart_day() {
          var data = new google.visualization.DataTable(
        <?php
        echo json_encode($table_31_days);
        ?>
      );

      var view = new google.visualization.DataView(data);

      var options = {
	chartArea: {
	  // leave room for y-axis labels
	  width: '90%'
	},
	legend: {
	  position: 'top'
	},
	width: '100%',
	height: 400,
	colors: ['#64B5F6']
      };

      var chart = new
          google.visualization.ColumnChart(document.getElementById("columnchart_day"));
      chart.draw(view, options);
      function resize () {
              // change dimensions if necessary
              chart.draw(data, options);
      }
      if (window.addEventListener) {
              window.addEventListener('resize', resize);
      }
      else {
              window.attachEvent('onresize', resize);
      }
  }
  </script>

</body>
</html>
