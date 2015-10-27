<?php
/*
Possible input combinations:
data.php
data.php?getLast
data.php?dateMin=2015-10-27&dateMax=2015-10-28&dataAvg=1
*/
$servername = "localhost";
$username = "USERNAME";
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
						,d.D_Pressure d_pressure
						,d.D_Temperature_4 d_temperature_4
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
						,dm.DM_Pressure d_pressure
						,dm.DM_Temperature_4 d_temperature_4
						from warehouse.duomenys_minutiniai dm
						where dm.DM_ActualDate >= DATE_SUB(NOW(), INTERVAL 1 HOUR) 

						order by 1 desc
						limit 1");
} else {
	if (isset($_GET["dateMin"],$_GET["dateMax"])) {		
		if($_GET["dataAvg"]==1){
			$sth = mysql_query("SELECT 
							UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 2 HOUR), '%Y-%m-%d %H:00'))*1000 as d_actualdate
							,round(avg(d.d_temperature_1),1) as d_temperature_1
							,round(avg(d.d_temperature_2),1) as d_temperature_2
							,round(avg(d.d_humidity_1),1) as d_humidity_1
							,round(avg(d.d_humidity_2),1) as d_humidity_2
							,round(avg(d.d_temperature_3),1) as d_temperature_3
							,round(avg(d.d_pressure),1) as d_pressure
							,round(avg(d.d_temperature_4),1) as d_temperature_4
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
								UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 2 HOUR), '%Y-%m-%d %H:%i'))*1000 as d_actualdate
								,round(d.d_temperature_1,1) as d_temperature_1
								,round(d.d_temperature_2,1) as d_temperature_2
								,round(d.d_humidity_1,1) as d_humidity_1
								,round(d.d_humidity_2,1) as d_humidity_2
								,round(d.d_temperature_3,1) as d_temperature_3
								,round(d.d_temperature_3,1) as d_temperature_3
								,round(d.d_pressure,1) as d_pressure
								,round(d.d_temperature_4,1) as d_temperature_4
								from warehouse.duomenys d 
								where d.d_actualdate >= '".$_GET["dateMin"]."' 
								and d.d_actualdate <= '".$_GET["dateMax"]."'
								and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
								and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
								and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
								and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
								and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)
								order by d.d_actualdate");
		}
	} else {
		$sth = mysql_query("SELECT 
							UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 2 HOUR), '%Y-%m-%d %H:00'))*1000 as d_actualdate
							,round(avg(d.d_temperature_1),1) as d_temperature_1
							,round(avg(d.d_temperature_2),1) as d_temperature_2
							,round(avg(d.d_humidity_1),1) as d_humidity_1
							,round(avg(d.d_humidity_2),1) as d_humidity_2
							,round(avg(d.d_temperature_3),1) as d_temperature_3
							,round(avg(d.d_pressure),1) as d_pressure
							,round(avg(d.d_temperature_4),1) as d_temperature_4
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
							UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 2 HOUR), '%Y-%m-%d %H:%i'))*1000 as d_actualdate
							,round(d.d_temperature_1,1) as d_temperature_1
							,round(d.d_temperature_2,1) as d_temperature_2
							,round(d.d_humidity_1,1) as d_humidity_1
							,round(d.d_humidity_2,1) as d_humidity_2
							,round(d.d_temperature_3,1) as d_temperature_3
							,round(d.d_pressure,1) as d_pressure
							,round(d.d_temperature_4,1) as d_temperature_4
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
	$rows['P'] = $r['d_pressure'];
	$rows['T4'] = $r['d_temperature_4'];
	array_push($return_arr,$rows);

}

	$data = array('datapoints' => $return_arr);



print json_encode($data, JSON_NUMERIC_CHECK);

mysql_close($con);
?>
