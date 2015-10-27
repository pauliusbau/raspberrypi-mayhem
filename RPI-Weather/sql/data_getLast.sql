select 
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
limit 1;



