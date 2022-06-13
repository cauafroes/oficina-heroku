<!--
  Rui Santos
  Complete project details at https://RandomNerdTutorials.com/cloud-weather-station-esp32-esp8266/

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files.

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.
-->
<?php
    include_once('database.php');
    if ($_GET["readingsCount"]){
      $data = $_GET["readingsCount"];
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      $readings_count = $_GET["readingsCount"];
    }
    // default readings count set to 60
    else {
      $readings_count = 60;
    }
    
    $all_data = everything($readings_count);
    while ($data = $all_data->fetch_assoc()){
        $all_values[] = $data;
    }
    $graph_temp = json_encode(array_reverse(array_column($all_values, 'temp')), JSON_NUMERIC_CHECK);
    $graph_humi = json_encode(array_reverse(array_column($all_values, 'humi')), JSON_NUMERIC_CHECK);
    $graph_press = json_encode(array_reverse(array_column($all_values, 'press')), JSON_NUMERIC_CHECK);
    $graph_reading_time = json_encode(array_reverse(array_column($all_values, 'reading_time')), JSON_NUMERIC_CHECK);
    
    /*
    foreach ($teste123 as $value){
        var_export(json_encode(array_reverse(array_column($teste123, 'temp')), JSON_NUMERIC_CHECK));
        echo "<br>";
    };
    */

    $last_reading = getLastReadings();
    $last_reading_temp = $last_reading["temp"];
    $last_reading_humi = $last_reading["humi"];
    $last_reading_press = $last_reading["press"];
    $last_reading_rain = $last_reading["rain"];
    $last_reading_wind = $last_reading["wind"];
    $last_reading_time = $last_reading["reading_time"];

    // Uncomment to set timezone to - 1 hour (you can change 1 to any number)
    $last_reading_time = date("d-m-Y H:i", strtotime("$last_reading_time - 3 hours"));
    // Uncomment to set timezone to + 7 hours (you can change 7 to any number)
    //$last_reading_time = date("Y-m-d H:i:s", strtotime("$last_reading_time + 7 hours"));

    $min_temp = minReading($readings_count, 'temp');
    $max_temp = maxReading($readings_count, 'temp');
    $avg_temp = avgReading($readings_count, 'temp');

    $min_humi = minReading($readings_count, 'humi');
    $max_humi = maxReading($readings_count, 'humi');
    $avg_humi = avgReading($readings_count, 'humi');
?>

<!DOCTYPE html>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <link rel="stylesheet" type="text/css" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/x-icon" href="favico.ico">
        <title>OR - estacao</title>
        <script type="text/javascript" src="highcharts.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    </head>
    <header class="header">
        <h1>Estação meteorológica</h1>
        <form method="get">
            <input type="number" name="readingsCount" min="1" placeholder="Número de leituras (<?php echo $readings_count; ?>)">
            <input type="submit" value="Buscar">
        </form>
    </header>
<body>
    <p>Última leitura: <?php echo $last_reading_time; ?></p>
    <p class="headertax">Taxa de leitura: 1/min</p>
    <section class="content">
	    <div class="box gauge--1">
	    <h3>TEMPERATURA</h3>
              <div class="mask">
			  <div class="semi-circle"></div>
			  <div class="semi-circle--mask"></div>
			</div>
		    <p style="font-size: 30px;" id="temp">--</p>
		    <table cellspacing="5" cellpadding="5">
		        <tr>
		            <th colspan="3">Últimas <?php echo $readings_count; ?> leituras</th>
	            </tr>
		        <tr>
		            <td>Min</td>
                    <td>Max</td>
                    <td>Média</td>
                </tr>
                <tr>
                    <td><?php echo $min_temp['min_amount']; ?> &deg;C</td>
                    <td><?php echo $max_temp['max_amount']; ?> &deg;C</td>
                    <td><?php echo round($avg_temp['avg_amount'], 2); ?> &deg;C</td>
                </tr>
            </table>
        </div>
        <div class="box gauge--2">
            <h3>UMIDADE</h3>
            <div class="mask">
                <div class="semi-circle"></div>
                <div class="semi-circle--mask"></div>
            </div>
            <p style="font-size: 30px;" id="humi">--</p>
            <table cellspacing="5" cellpadding="5">
                <tr>
                    <th colspan="3">Últimas <?php echo $readings_count; ?> leituras</th>
                </tr>
                <tr>
                    <td>Min</td>
                    <td>Max</td>
                    <td>Média</td>
                </tr>
                <tr>
                    <td><?php echo $min_humi['min_amount']; ?> %</td>
                    <td><?php echo $max_humi['max_amount']; ?> %</td>
                    <td><?php echo round($avg_humi['avg_amount'], 2); ?> %</td>
                </tr>
            </table>
        </div>
    </section>


    <div id="chart-temperature" class="container"></div>
    <div id="chart-humidity" class="container"></div>
    <div id="chart-pressure" class="container"></div>
<?php
    echo   '<h2> Últimas ' . $readings_count . ' leituras</h2>
            <table cellspacing="5" cellpadding="5" id="tableReadings">
                <tr>
                    <th>ID</th>
                    <th>Sensor</th>
                    <th>Local</th>
                    <th>Temp</th>
                    <th>Humi</th>
                    <th>Press</th>
                    <th>Chuva</th>
                    <th>Vento</th>
                    <th>Hora</th>
                </tr>';

    $result = getAllReadings($readings_count);
        if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row_id = $row["id"];
            $row_sensor = $row["sensor"];
            $row_location = $row["location"];
            $row_value1 = $row["temp"];
            $row_value2 = $row["humi"];
            $row_value3 = $row["press"];
            $row_value4 = $row["rain"];
            $row_value5 = $row["wind"];
            $row_reading_time = $row["reading_time"];
            // Uncomment to set timezone to - 1 hour (you can change 1 to any number)
            $row_reading_time = date("d-m-Y H:i", strtotime("$row_reading_time - 3 hours"));
            // Uncomment to set timezone to + 7 hours (you can change 7 to any number)
            //$row_reading_time = date("Y-m-d H:i:s", strtotime("$row_reading_time + 7 hours"));

            echo '<tr>
                    <td>' . $row_id . '</td>
                    <td>' . $row_sensor . '</td>
                    <td>' . $row_location . '</td>
                    <td>' . $row_value1 . '</td>
                    <td>' . $row_value2 . '</td>
                    <td>' . $row_value3 . '</td>
                    <td>' . $row_value4 . '</td>
                    <td>' . $row_value5 . '</td>
                    <td>' . $row_reading_time . '</td>
                  </tr>';
        }
        echo '</table>';
        $result->free();
    }
?>

<script>
    var temp = <?php echo $last_reading_temp; ?>;
    var humi = <?php echo $last_reading_humi; ?>;
    setTemperature(temp);
    setHumidity(humi);

    function setTemperature(curVal){
    	//set range for Temperature in Celsius -5 Celsius to 38 Celsius
    	var minTemp = 0.0;
    	var maxTemp = 50.0;
        //set range for Temperature in Fahrenheit 23 Fahrenheit to 100 Fahrenheit
    	//var minTemp = 23;
    	//var maxTemp = 100;

    	var newVal = scaleValue(curVal, [minTemp, maxTemp], [0, 180]);
    	$('.gauge--1 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#temp").text(curVal + ' ºC');
    }

    function setHumidity(curVal){
    	//set range for Humidity percentage 0 % to 100 %
    	var minHumi = 0;
    	var maxHumi = 100;

    	var newVal = scaleValue(curVal, [minHumi, maxHumi], [0, 180]);
    	$('.gauge--2 .semi-circle--mask').attr({
    		style: '-webkit-transform: rotate(' + newVal + 'deg);' +
    		'-moz-transform: rotate(' + newVal + 'deg);' +
    		'transform: rotate(' + newVal + 'deg);'
    	});
    	$("#humi").text(curVal + ' %');
    }

    function scaleValue(value, from, to) {
        var scale = (to[1] - to[0]) / (from[1] - from[0]);
        var capped = Math.min(from[1], Math.max(from[0], value)) - from[0];
        return ~~(capped * scale + to[0]);
    }
</script>
<script>

var value1 = <?php echo $graph_temp; ?>;
var value2 = <?php echo $graph_humi; ?>;
var value3 = <?php echo $graph_press; ?>;
var reading_time = <?php echo $graph_reading_time; ?>;


/*
        TEMA ESCURO: AUTORIA DE CAUÃ FRÓES

Highcharts.setOptions({
    colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
    chart: {
        backgroundColor: '#333333',
        borderWidth: 0,
        plotBackgroundColor: '#333333',
        plotBorderWidth: 0
    },
    title: {
        style: {
            color: '#e6e6e6',
            font: '16px "Trebuchet MS", Verdana, sans-serif'
        }
    },
    subtitle: {
        style: {
            color: '#e6e6e6',
            font: '12px "Trebuchet MS", Verdana, sans-serif'
        }
    },
    xAxis: {
        gridLineWidth: 1,
        gridLineColor: '#666666',
        lineColor: '#595959',
        tickColor: '#595959',
        labels: {
            style: {
                color: '#e6e6e6',
                font: '11px Trebuchet MS, Verdana, sans-serif'
            }
        },
        title: {
            style: {
                color: '#e6e6e6',
                fontWeight: 'bold',
                fontSize: '12px',
                fontFamily: 'Trebuchet MS, Verdana, sans-serif'

            }
        }
    },
    yAxis: {
        alternateGridColor: null,
        gridLineColor: '#666666',
        minorTickInterval: 'none',
        lineColor: '#595959',
        lineWidth: 1,
        tickWidth: 1,
        labels: {
            style: {
                color: '#e6e6e6',
                font: '11px Trebuchet MS, Verdana, sans-serif'
            }
        },
        title: {
            style: {
                color: '#e6e6e6',
                fontWeight: 'bold',
                fontSize: '12px',
                fontFamily: 'Trebuchet MS, Verdana, sans-serif'
            }
        }
    },
    legend: {
        itemStyle: {
            font: '9pt Trebuchet MS, Verdana, sans-serif',
            color: 'white'

        },
        itemHoverStyle: {
            color: '#e6e6e6'
        },
        itemHiddenStyle: {
            color: 'gray'
        }
    },
    credits: {
        style: {
            right: '10px'
        }
    },
    labels: {
        style: {
            color: '#e6e6e6'
        }
    }
});

/*
/*
// create the chart
Highcharts.chart('container', {

xAxis: {
    type: 'datetime'
},

series: [{
    data: [29.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
    pointStart: Date.UTC(2010, 0, 1),
    pointInterval: 3600 * 1000 // one hour
}]
});
*/


var chartTH = new Highcharts.Chart({
  chart:{ renderTo:'chart-temperature' }, 
  title: { text: 'Temperatura' },
  series: [{
  showInLegend: false,
  data: value1
  }
  ],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true},
    },
    series: {color: '#0F9FFF' }
  },
  xAxis: {
    type: 'time',
    dateTimeLabelFormats: { second: '%H:%M' },
    categories: reading_time
  },
  yAxis: {
    title: { text: 'Temperatura (ºC)' }
  },
  credits: { enabled: false }
});

var chartH = new Highcharts.Chart({
  chart:{ renderTo:'chart-humidity' }, 
  title: { text: 'Umidade' },
  series: [{
    showInLegend: false,
    data: value2
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true},
    },
    series: {color: '#FF0F0F' }
  },
  xAxis: {
    type: 'time',
    dateTimeLabelFormats: { second: '%H:%M:%S' },
    categories: reading_time
  },
  yAxis: {
    title: { text: 'Umidade (%)' }
  },
  credits: { enabled: false }
});

var chartA = new Highcharts.Chart({
  chart:{ renderTo:'chart-pressure' },
  title: { text: 'Pressão' },
  series: [{
    showInLegend: false,
    data: value3
  }],
  plotOptions: {
    line: { animation: false,
      dataLabels: { enabled: true},
    },
    series: { color: '#0FFF10' }
  },
  xAxis: {
    type: 'datetime',
    //dateTimeLabelFormats: { second: '%H:%M:%S' }
    categories: reading_time
  },
  yAxis: {
    title: { text: 'Pressão (Pa)' }
  },
  credits: { enabled: false }
});


</script>
</body>
</html>
