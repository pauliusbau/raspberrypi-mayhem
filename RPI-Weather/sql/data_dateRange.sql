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
where	d.d_actualdate >= "2015-10-27" 
and d.d_actualdate <= "2015-10-28"  
and (d.D_Temperature_1 is null or d.D_Temperature_1 between -40 and 60)
and (d.D_Temperature_2 is null or d.D_Temperature_2 between -40 and 60)
and (d.D_Temperature_3 is null or d.D_Temperature_3 between -40 and 60)
and (d.D_Humidity_1 is null or d.D_Humidity_1 between 0 and 100)
and (d.D_Humidity_2 is null or d.D_Humidity_2 between 0 and 100)
order by d.d_actualdate