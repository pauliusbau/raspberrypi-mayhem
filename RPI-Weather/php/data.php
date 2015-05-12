<?php
$servername = "localhost";
$username = "USER";
$password = "PASSWORD";
$dbname = "warehouse";

$con = mysql_connect($servername,$username,$password);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

mysql_select_db($dbname, $con);
if (isset($_GET["getLast"])) {
	$sth = mysql_query("select 
						UNIX_TIMESTAMP(date_format(d.d_actualdate, '%Y-%m-%d %H:%i'))*1000 as d_actualdate
						,d.D_Temperature_1 d_temperature_1
						,d.D_Humidity_1 d_humidity_1
						,d.D_Temperature_2 d_temperature_2
						,d.D_Humidity_2 d_humidity_2
						,d.D_Temperature_3 d_temperature_3
						from warehouse.duomenys d
						where d.D_ActualDate >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
						and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
						and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
						and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
						and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
						and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)
						UNION ALL
						select 
						UNIX_TIMESTAMP(date_format(dm.dm_actualdate, '%Y-%m-%d %H:%i'))*1000 as d_actualdate
						,dm.DM_Temperature_1 d_temperature_1
						,dm.DM_Humidity_1 d_humidity_1
						,dm.DM_Temperature_2 d_temperature_2
						,dm.DM_Humidity_2 d_humidity_2
						,dm.DM_Temperature_3 d_temperature_3
						from warehouse.duomenys_minutiniai dm
						where dm.DM_ActualDate >= DATE_SUB(NOW(), INTERVAL 1 HOUR) 

						order by 1 desc
						limit 1");
} else {
	if (isset($_GET["dateMin"],$_GET["dateMax"])) {		
		if($_GET["dataAvg"]==1){
			$sth = mysql_query("SELECT 
							UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 3 HOUR), '%Y-%m-%d %H:00'))*1000 as d_actualdate
							,round(avg(d.d_temperature_1),1) as d_temperature_1
							,round(avg(d.d_temperature_2),1) as d_temperature_2
							,round(avg(d.d_humidity_1),1) as d_humidity_1
							,round(avg(d.d_humidity_2),1) as d_humidity_2
							,round(avg(d.d_temperature_3),1) as d_temperature_3
							from warehouse.duomenys d 
							where d.d_actualdate >= '".$_GET["dateMin"]."' 
							and d.d_actualdate <= '".$_GET["dateMax"]."'
							and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
							and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
							and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
							and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
							and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)
							
							group by date_format(d.d_actualdate, '%Y-%m-%d %H')
							
							order by 1");
		} else{
			$sth = mysql_query("SELECT 
								UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 3 HOUR), '%Y-%m-%d %H:%i'))*1000 as d_actualdate
								,round(d.d_temperature_1,1) as d_temperature_1
								,round(d.d_temperature_2,1) as d_temperature_2
								,round(d.d_humidity_1,1) as d_humidity_1
								,round(d.d_humidity_2,1) as d_humidity_2
								,round(d.d_temperature_3,1) as d_temperature_3
								from warehouse.duomenys d 
								where d.d_actualdate >= '".$_GET["dateMin"]."' 
								and d.d_actualdate <= '".$_GET["dateMax"]."'
								and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
								and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
								and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
								and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
								and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)
								order by d.d_actualdate");
		//$sql = mysql_query("SELECT timestamp_value, traffic_count FROM foot_traffic WHERE timestamp_value LIKE '".$_GET["dateMin"]."%'");
		}
	} else {
		//$sth = mysql_query("SELECT d.d_actualdate as d_actualdate, d.d_temperature_1, d.d_temperature_2 from warehouse.duomenys d where d.d_actualdate >= DATE_SUB(date(NOW()), INTERVAL 1 DAY) order by d.d_id");
		
		$sth = mysql_query("SELECT 
							UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 3 HOUR), '%Y-%m-%d %H:00'))*1000 as d_actualdate
							,round(avg(d.d_temperature_1),1) as d_temperature_1
							,round(avg(d.d_temperature_2),1) as d_temperature_2
							,round(avg(d.d_humidity_1),1) as d_humidity_1
							,round(avg(d.d_humidity_2),1) as d_humidity_2
							,round(avg(d.d_temperature_3),1) as d_temperature_3
							from warehouse.duomenys d 
							where d.d_actualdate >= DATE_SUB(date(NOW()), INTERVAL 1 WEEK) 
							and d.d_actualdate < DATE_SUB(date(NOW()), INTERVAL 1 DAY)
							and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
							and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
							and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
							and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
							and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)
							
							group by date_format(d.d_actualdate, '%Y-%m-%d %H')

							UNION 

							SELECT 
							UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 3 HOUR), '%Y-%m-%d %H:%i'))*1000 as d_actualdate
							,round(d.d_temperature_1,1) as d_temperature_1
							,round(d.d_temperature_2,1) as d_temperature_2
							,round(d.d_humidity_1,1) as d_humidity_1
							,round(d.d_humidity_2,1) as d_humidity_2
							,round(d.d_temperature_3,1) as d_temperature_3
							from warehouse.duomenys d 
							where d.d_actualdate >= DATE_SUB(date(NOW()), INTERVAL 1 DAY)
							and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
							and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
							and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
							and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
							and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)

							order by 1");
	}
}


$return_arr = array();


while($r = mysql_fetch_array($sth)) {	
	
	$rows['at']	= $r['d_actualdate'];
	$rows['T1'] = $r['d_temperature_1'];
	$rows['T2'] = $r['d_temperature_2'];
	$rows['H1'] = $r['d_humidity_1'];
	$rows['H2'] = $r['d_humidity_2'];
	$rows['T3'] = $r['d_temperature_3'];
	
	array_push($return_arr,$rows);
	
}

	$data = array('datapoints' => $return_arr);



print json_encode($data, JSON_NUMERIC_CHECK);

mysql_close($con);
?>
