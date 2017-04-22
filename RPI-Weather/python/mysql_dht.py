#!/usr/bin/python
# -*- coding: utf-8 -*-

# ------------------------------------------------
# to run this scrip from Cron use I use these settings:
# 5,20,35,50  * * * * sudo python /home/python/mysql_dht.py >/dev/null 2>&1
# 0,2,4,6,8,10,12,14,16,18,22,24,26,28,30,32,34,36,38,40,42,44,46,48,52,54,56,58 * * * * sudo python /home/python/mysql_dht.py m >/dev/null 2>&1
# ------------------------------------------------


import MySQLdb as mdb
import sys
import arrow
import re
import subprocess
import os
import numpy as np

import DS18B20
from Adafruit_BMP085 import BMP085
import RPi.GPIO as GPIO
import SI7021

# print 'Number of arguments:', len(sys.argv), 'arguments.'
# print 'Argument List:', str(sys.argv)

class bcolors: #terminal text color
    HEADER = '\033[95m'
    OKBLUE = '\033[94m'
    OKGREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'
    UNDERLINE = '\033[4m'

GPIO.setmode(GPIO.BCM) ## uses GPIO numbers NOT pin numbers
GPIO.setwarnings( False )

GPIO.setup(26, GPIO.OUT) ## 37 PIN / GPIO26 

os.chdir(os.path.abspath('/home/python/'))

#globals for temperature and humidity averaging
temp = 0
hum = 0

# ---------- various settings ----------
bmp = BMP085(0x77)
iteration_type_text = 'MData'
iteration_type_text_2 = 'MData_2min'
iteration_count = 16

if len(sys.argv) >= 2:
	iteration_type = sys.argv[1]
else:
	iteration_type = None


#smart averaging process witch eliminates outlier data points
def sanity_test(dataList):
	MLS = sorted(dataList)
	#print MLS
	Q1 = np.percentile(MLS,25)
	Q3 = np.percentile(MLS,75)
	IRQ=Q3-Q1
	L=Q1-1.5*IRQ
	H=Q1+1.5*IRQ
	#print "L: %s H: %s" %(L,H)

	data = 0
	count = 0
	for i in range(0,len(MLS)):
		if (MLS[i] >= L and MLS[i] <= H):
			data = data + MLS[i]
			count = count + 1
			#print "+ i: %s MLS[%s]: %s count: %s" %(i,i,MLS[i],count)
		else:
			count = count
			#print "- i: %s MLS[%s]: %s count: %s" %(i,i,MLS[i],count)
		
	data_avg = round(data/count,1)
	#print data_avg
	return data_avg


#sys.stdout = open('mysql_dht.log', 'w')
def read_dht22_raw ( PiPin ):
	success = 0
	output = subprocess.check_output(["./Adafruit_DHT", "2302", str(PiPin)])
	matches = re.search("Temp =\s+([-+]?[0-9.]+)", output)
	if (matches):
		global temp
		temp = matches.group(1)
		success = 1
		#print temp
	else:
		success = 0
		#print "fail"
	matches = re.search("Hum =\s+([0-9.]+)", output)
	if (matches):
		global hum
		hum = matches.group(1)
		# if float(hum)>100.0:
			# hum=100.0
		# else:
			# hum = hum
		#print hum
	else:
		success = 0
		#print "fail"
	return success

def read_dht22(PiPin):
	count = 0
	temperature = []
	humidity = []
	for x in range (0, iteration_count):
		if (read_dht22_raw(PiPin)):
			count = count + 1
			temperature.append(float(temp))
			humidity.append(float(hum))
		else: count = count #print "echem.."

	if (count>0):
		temperature = sanity_test(temperature)
		humidity=sanity_test(humidity)
	else: 
		count=count
		temperature = None
		humidity = None
			
	print "[%s] Temperature: %s Humidity: %s" %(PiPin,temperature,humidity) 
	return temperature,humidity
	
	
try:
	con = mdb.connect('localhost', 'USER', 'PASSWORD', 'warehouse');

	cur = con.cursor()
	cur.execute("SELECT VERSION()")
	ver = cur.fetchone()
	
	sys_date = arrow.now().format('YYYY-MM-DD HH:mm:ss')

	print  bcolors.HEADER + "RPI Weather Station v1.5" + bcolors.ENDC
	print "Database version : %s" % ver
	print sys_date

	GPIO.output(26,True) #Turn LED ON, while reading DHT2 sensors
	
	# ------------------------------ DHT22 read ------------------------
	print bcolors.OKBLUE + "DHT22 data:" + bcolors.ENDC
	temperature_1,humidity_1 = read_dht22(13)
	temperature_2,humidity_2 = None, None # currently DHT22 conneter to 19 pin is disconneted
	
	# ------------------------------ DS18B20 read ------------------------
	print bcolors.OKBLUE + "DS18B20 data:" + bcolors.ENDC
	try:
		oneWire = DS18B20.read_temp()
		if (oneWire != None):
			temperature_3 = round(float(DS18B20.read_temp()),1)
			print "DS18B20.read_temp()= %s" %(temperature_3)
		else: print "No data from DS18B20 :("
	except (RuntimeError, TypeError, NameError):
			temperature_3 = None
			print "No data from DS18B20 :("
	
	# -------------------------- BMP085 read -------------------------------
	print bcolors.OKBLUE + "BMP085 data:" + bcolors.ENDC
	temperature_4 = bmp.readTemperature()
	pressure = bmp.readPressure()

	print "Temperature: %.2f C" % temperature_4
	print "Pressure:    %.2f hPa" % (pressure / 100.0)
	
	
	# -------------------------- SI7021 read -------------------------------
	print bcolors.OKBLUE + "SI7021 data:" + bcolors.ENDC
	temperature_2 = round(SI7021.read_temp(),2)
	humidity_2 =  round(SI7021.read_hum(),2)
	
	print "Temperature is : %.2f C" %temperature_2
	print "Relative Humidity is : %.2f %%" %humidity_2	
	
	# -------------------------- DATA SQL INSERT -------------------------------
	if (iteration_type != 'm' and iteration_type != 'M'): 
		print bcolors.WARNING + "# 15 min iteracija" + bcolors.ENDC
		cur.execute("""INSERT INTO duomenys (D_ActualDate, D_Temperature_1, D_Humidity_1 ,D_Temperature_2, D_Humidity_2, D_Temperature_3, D_Pressure ,D_Temperature_4, D_Info)
					VALUES ( %s, %s,%s,%s,%s,%s,%s,%s, %s)""",
					[sys_date,temperature_1,humidity_1,temperature_2,humidity_2,temperature_3,(pressure / 100.0), temperature_4, iteration_type_text])
				
	else:
		print bcolors.WARNING + "# 2 min iteracija" + bcolors.ENDC
		cur.execute("""INSERT INTO duomenys_minutiniai (DM_ActualDate, DM_Temperature_1, DM_Humidity_1 ,DM_Temperature_2, DM_Humidity_2, DM_Temperature_3, DM_Pressure ,DM_Temperature_4, DM_Info)
					VALUES ( %s, %s,%s,%s,%s,%s,%s,%s, %s)""",
					[sys_date,temperature_1,humidity_1,temperature_2,humidity_2,temperature_3,(pressure / 100.0), temperature_4, iteration_type_text_2])
	con.commit()

	GPIO.output(26,False)
	
except mdb.Error, e:
	if con:
		con.rollback()
		
	print "Error %d: %s" % (e.args[0],e.args[1])
	sys.exit(1)
	
finally:	
		
	if con:	
		con.close()

	GPIO.cleanup()
