select 
UNIX_TIMESTAMP(date_format(d.d_actualdate, '%Y-%m-%d %H:%i'))*1000 as d_actualdate
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

order by 1 desc
limit 1;



