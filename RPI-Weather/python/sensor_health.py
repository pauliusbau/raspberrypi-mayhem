import MySQLdb as mdb
import MySQLdb.cursors
import sys
import arrow
import urllib2
import httplib, urllib
import time
import os


os.chdir(os.path.abspath('/home/python/'))

# ------------ Config Variable -------------
error_count_max = 20
error_state_latch = 75 # % (percent value)
sys_date = arrow.now().format('YYYY-MM-DD HH:mm:ss')
# ------------------------------------------
def pushover(msg,priority):
	conn = httplib.HTTPSConnection("api.pushover.net:443")
	conn.request("POST", "/1/messages.json",
	urllib.urlencode({
		"token": "----------------------",
		"user": "-----------------------",
		"priority":priority,
		#"retry":"45",
		#"expire":"3600",
		"message": msg,
		}), { "Content-type": "application/x-www-form-urlencoded" })
	conn.getresponse()

class bcolors: #terminal text color
	HEADER = '\033[95m'
	OKBLUE = '\033[94m'
	OKGREEN = '\033[92m'
	WARNING = '\033[93m'
	FAIL = '\033[91m'
	ENDC = '\033[0m'
	BOLD = '\033[1m'
	UNDERLINE = '\033[4m'

sensor_health_query = ("""select 
						date_format(date_add(NOW(), INTERVAL 0 HOUR), '%Y-%m-%d %H:%i') as qtime
						,sum(T1) as S1
						,sum(T2) as S2
						,sum(T3) as S3 
						from 
						(
						select 
						sum(case when d.D_Temperature_1 is null then 1 else 0 end) as T1
						,sum(case when d.D_Temperature_2 is null then 1 else 0 end) as T2
						,sum(case when d.D_Temperature_3 is null then 1 else 0 end) as T3
						from warehouse.duomenys d
						where d.d_actualdate >= DATE_SUB(NOW(), INTERVAL 1 HOUR) #between '2015-01-19 22:00' and '2015-01-20 12:00'  #

						UNION

						select 
						sum(case when dm.Dm_Temperature_1 is null then 1 else 0 end) as T1
						,sum(case when dm.Dm_Temperature_2 is null then 1 else 0 end) as T2
						,sum(case when dm.Dm_Temperature_3 is null then 1 else 0 end) as T3
						from warehouse.duomenys_minutiniai dm
						where dm.dm_actualdate >= DATE_SUB(NOW(), INTERVAL 1 HOUR) 
						) as Sensors """)

sensor_health_status_query = 	("""select 
								si.si_actualdate
								,si.si_type
								,si.si_info
								,si.si_status
								from system_info si
								where si.si_type='SensorHealth'
								order by si.si_actualdate desc
								limit 1""")					

# ---------------------------------------------------------------------------------------------------------------
print  bcolors.HEADER + "Sensor Health Check" + bcolors.ENDC


try:
	con = mdb.connect('localhost', 'USERNAME', 'PASSWORD', 'warehouse')
	cur = con.cursor(MySQLdb.cursors.DictCursor)
	
	cur.execute(sensor_health_query)
	sensor_health = cur.fetchone()
	error_count = sensor_health['S1']+sensor_health['S2']+sensor_health['S3']
	
	print ("Sensor Health Status:\n\t%s\n\tDHT22 [OUT]: \t%s\n\tDHT22 [IN]: \t%s \n\tDS18B20: \t%s" ) %(sensor_health['qtime'], sensor_health['S1'],sensor_health['S2'],sensor_health['S3'])
		
	
	if error_count >= error_count_max:
		print "Sensor Health Check [" + bcolors.FAIL + "FAIL" + bcolors.ENDC + "]"
		
		cur.execute(sensor_health_status_query)
		sensor_health_status = cur.fetchone()
		# print sensor_health_status
		
		if sensor_health_status['si_status']=='DOWN':
			print "Sensors are down since: %s" %(sensor_health_status['si_actualdate'])
			
		elif sensor_health_status['si_status']=='UP':
			print "-------------------"
			#sensor fault was detected for the first time*
						
			pushover("Sensor health alert:\n"
					"%s\n"
					"DHT22 [OUT]: %s \n"
					"DHT22 [IN]: \t%s \n"
					"DS18B20: \t%s" 
					%(sensor_health['qtime'], sensor_health['S1'], sensor_health['S2'], sensor_health['S3']),1)

			cur.execute("INSERT INTO system_info (SI_ActualDate, SI_Type, SI_Info, SI_Status, SI_Comments) VALUES (NOW(), 'SensorHealth', %s , 'DOWN', %s)"
						,[error_count
						,("%s  DHT22 [OUT]:%s DHT22 [IN]:%s DS18B20:%s" ) %(sensor_health['qtime'], sensor_health['S1'],sensor_health['S2'],sensor_health['S3'])
						])
			
			con.commit()
		
	# Sensors are working fine, but did they before? :)	
	else: 
		print "# Sensors seems to be running"
		
		cur.execute(sensor_health_status_query)
		sensor_health_status = cur.fetchone()
		# print sensor_health_status
		
		if sensor_health_status['si_status']=='UP':
			print "Sensor Health Check [" + bcolors.OKGREEN + "OK" + bcolors.ENDC + "]"
			print "Everything seems fine.. Exiting"				
			
			exit()
			quit()
			
		elif sensor_health_status['si_status']=='DOWN':
			print "Sensors were down for %s since %s" %(arrow.get(sys_date) - arrow.get(sensor_health_status['si_actualdate']), sensor_health_status['si_actualdate'])
			
			if error_count*100/int(sensor_health_status['si_info']) <= error_state_latch:
				print "Sensor Health Check [" + bcolors.OKBLUE + "UP" + bcolors.ENDC + "]"
				
				pushover("Sensor are healthy again:\n"
						"%s\n"
						"DHT22 [OUT]: %s \n"
						"DHT22 [IN]: \t%s \n"
						"DS18B20: \t%s" 
						%(sensor_health['qtime'], sensor_health['S1'],sensor_health['S2'],sensor_health['S3']),0)
				
				#SQL insert that sensors are up and running again
				cur.execute("INSERT INTO system_info (SI_ActualDate, SI_Type, SI_Info, SI_Status, SI_Comments) VALUES (NOW(), 'SensorHealth', %s , 'UP', %s)"
						,[error_count
						,("%s  DHT22 [OUT]:%s DHT22 [IN]:%s DS18B20:%s" ) %(sensor_health['qtime'], sensor_health['S1'],sensor_health['S2'],sensor_health['S3'])
						])
		
				con.commit()
				exit()
				quit()
				
		else:
			print 'Strange..'
			exit()
			quit()
		
except mdb.Error, e:
	if con:
		con.rollback()
		
	print "Error %d: %s" % (e.args[0],e.args[1])
	sys.exit(1)
	
finally:	
		
	if con:
		con.close()
	exit()
	quit()