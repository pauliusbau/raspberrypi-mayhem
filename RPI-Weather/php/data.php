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


try{
$db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

 if (isset($_GET["getLast"])) {
		$sth = $db->prepare("select 
							UNIX_TIMESTAMP(date_format(d.d_actualdate, '%Y-%m-%d %H:%i'))*1000 as at
							,d.D_Temperature_1 as T1
							,d.D_Humidity_1 as H1
							,d.D_Temperature_2 as T2
							,d.D_Humidity_2 as H2
							,d.D_Temperature_3 as T3
							,d.D_Pressure as P
							,d.D_Temperature_4 as T4
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
							,dm.DM_Temperature_1 as T1
							,dm.DM_Humidity_1 as H1
							,dm.DM_Temperature_2 as T2
							,dm.DM_Humidity_2 as H2
							,dm.DM_Temperature_3 as T3
							,dm.DM_Pressure as P
							,dm.DM_Temperature_4 as T4
							from warehouse.duomenys_minutiniai dm
							where dm.DM_ActualDate >= DATE_SUB(NOW(), INTERVAL 1 HOUR) 
							and (dm.DM_Temperature_1 is null or dm.DM_Temperature_1 between -40 and 60)
							and (dm.DM_Temperature_2 is null or dm.DM_Temperature_2 between -40 and 60)
							and (dm.DM_Temperature_3 is null or dm.DM_Temperature_3 between -40 and 60)
							and (dm.DM_Humidity_1 is null or dm.DM_Humidity_1 between 0 and 100)
							and (dm.DM_Humidity_2 is null or dm.DM_Humidity_2 between 0 and 100)
							order by 1 desc
							limit 1");
		$sth->execute();
		$results=$sth->fetchAll(PDO::FETCH_ASSOC);
 } 	else {
	if (isset($_GET["dateMin"],$_GET["dateMax"])) {		
		if($_GET["dataAvg"]==1){
			$sth = $db->prepare("SELECT 
							UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 2 HOUR), '%Y-%m-%d %H:00'))*1000 as at
							,round(avg(d.d_temperature_1),1) as T1
							,round(avg(d.d_temperature_2),1) as T2
							,round(avg(d.d_humidity_1),1) as H1
							,round(avg(d.d_humidity_2),1) as H2
							,round(avg(d.d_temperature_3),1) as T3
							,round(avg(d.d_pressure),1) as P
							,round(avg(d.d_temperature_4),1) as T4
							from warehouse.duomenys d 
							where d.d_actualdate >= ? 
							and d.d_actualdate <= ?
							and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
							and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
							and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
							and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
							and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)
							
							group by date_format(d.d_actualdate, '%Y-%m-%d %H')
							
							order by 1");
			$sth->execute(array($_GET["dateMin"], $_GET["dateMax"]));
			$results=$sth->fetchAll(PDO::FETCH_ASSOC);
		} else{
			$sth = $db->prepare("SELECT 
								UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 2 HOUR), '%Y-%m-%d %H:%i'))*1000 as at
								,round(d.d_temperature_1,1) as T1
								,round(d.d_temperature_2,1) as T2
								,round(d.d_humidity_1,1) as H1
								,round(d.d_humidity_2,1) as H2
								,round(d.d_temperature_3,1) as T3
								,round(d.d_temperature_3,1) as T3
								,round(d.d_pressure,1) as P
								,round(d.d_temperature_4,1) as T4
								from warehouse.duomenys d 
								where d.d_actualdate >= ?
								and d.d_actualdate <= ?
								and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
								and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
								and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
								and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
								and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)
								order by d.d_actualdate");
			$sth->execute(array($_GET["dateMin"], $_GET["dateMax"]));
			$results=$sth->fetchAll(PDO::FETCH_ASSOC);
		}
	} else {
		$sth = $db->prepare("SELECT 
							UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 2 HOUR), '%Y-%m-%d %H:00'))*1000 as at
							,round(avg(d.d_temperature_1),1) as T1
							,round(avg(d.d_temperature_2),1) as T2
							,round(avg(d.d_humidity_1),1) as H1
							,round(avg(d.d_humidity_2),1) as H2
							,round(avg(d.d_temperature_3),1) as T3
							,round(avg(d.d_pressure),1) as P
							,round(avg(d.d_temperature_4),1) as T4
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
							UNIX_TIMESTAMP(date_format(date_add(d.d_actualdate, INTERVAL 2 HOUR), '%Y-%m-%d %H:%i'))*1000 as at
							,round(d.d_temperature_1,1) as T1
							,round(d.d_temperature_2,1) as T2
							,round(d.d_humidity_1,1) as H1
							,round(d.d_humidity_2,1) as H2
							,round(d.d_temperature_3,1) as T3
							,round(d.d_pressure,1) as P
							,round(d.d_temperature_4,1) as T4
							from warehouse.duomenys d 
							where d.d_actualdate >= DATE_SUB(date(NOW()), INTERVAL 1 DAY)
							and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
							and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
							and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
							and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
							and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)

							order by 1");
		$sth->execute();
		$results=$sth->fetchAll(PDO::FETCH_ASSOC);
	}
}	 



$data = array('datapoints' => $results);
print json_encode($data, JSON_NUMERIC_CHECK);


// close db connection
$db = null;
}
catch(PDOException $e) {
    echo $e->getMessage();
}
?>
