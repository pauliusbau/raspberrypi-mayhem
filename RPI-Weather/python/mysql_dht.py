#!/usr/bin/python
# -*- coding: utf-8 -*-

import MySQLdb as mdb
import sys
import arrow
import re
import subprocess
import os
import numpy as np
import DS18B20
#import sys

import RPi.GPIO as GPIO

GPIO.setmode(GPIO.BCM) ## uses GPIO numbers NOT pin numbers
GPIO.setwarnings( False )
GPIO.setup(26, GPIO.OUT) ## 37 PIN / GPIO26 

os.chdir(os.path.abspath('/home/python/'))

#globals for temperature and humidity averaging
temp = 0
hum = 0

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
def read_dht22 ( PiPin ):
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
        #print hum
    else:
        success = 0
        #print "fail"
    return success

try:
    con = mdb.connect('localhost', 'USER', 'PASSWORD', 'warehouse');

    cur = con.cursor()
    cur.execute("SELECT VERSION()")

    ver = cur.fetchone()

    sys_date = arrow.now().format('YYYY-MM-DD HH:mm:ss')

    
    print "Database version : %s " % ver
    print sys_date

    GPIO.output(26,True) #Turn LED ON, while reading DHT2 sensors
    
    count_1 = 0
    temperature_1 = []
    humidity_1 = []
    for x in range (0, 16):
        if (read_dht22(19)):
            count_1 = count_1 + 1
            temperature_1.append(float(temp))
            humidity_1.append(float(hum))
            #print "[19] Temp: %s Hum: %s Count: %s" %(temp,hum,count_1)
        else: count_1 = count_1 #print "echem.."

    if (count_1>0):
        temperature_1 = sanity_test(temperature_1)
        humidity_1=sanity_test(humidity_1)
    else: count_1=count_1
            
    print "[19] Temperature: %s Humidity: %s" %(temperature_1,humidity_1) 
    #print "[19] Temperature: %s Humidity: %s" %(round(temperature_1/count_1,1),round(humidity_1/count_1,1))    

    count_2 = 0
    temperature_2 = []
    humidity_2 = []
    for x in range (0, 16):
        if (read_dht22(13)):
            count_2 = count_2 + 1
            temperature_2.append(float(temp))
            humidity_2.append(float(hum))
            #print "[13] Temp: %s Hum: %s Count: %s" %(temp,hum,count_2)
        else: count_2 = count_2 #print "echem.."

    if (count_2>0):
        temperature_2 = sanity_test(temperature_2)
        humidity_2 = sanity_test(humidity_2)
    else: count_2=count_2
            
    print "[13] Temperature: %s Humidity: %s" %(temperature_2,humidity_2)

    # ------------------------------ DS18B20 nuskaitymas ------------------------
    try:
	oneWire = DS18B20.read_temp()
    	if (oneWire != None):
        	temperature_3 = round(float(DS18B20.read_temp()),1)
       		print "DS18B20.read_temp()= %s" %(temperature_3)
   	else: print "No data from DS18B20 :("
    except (RuntimeError, TypeError, NameError):
        print "No data from DS18B20 :("
    


    if (oneWire != None):        
        if (count_1 > 0 and count_2 > 0):
                cur.execute("""INSERT INTO duomenys (D_ActualDate, D_Temperature_1, D_Humidity_1 ,D_Temperature_2, D_Humidity_2, D_Temperature_3, D_Info)
                VALUES ( %s, %s,%s,%s,%s,%s, 'MData')""",
                [sys_date,temperature_1,humidity_1,temperature_2,humidity_2,temperature_3])

        elif (count_1 == 0 and count_2 > 0):
                cur.execute("""INSERT INTO duomenys (D_ActualDate, D_Temperature_1, D_Humidity_1 ,D_Temperature_2, D_Humidity_2, D_Temperature_3, D_Info)
                VALUES ( %s, NULL,NULL,%s,%s,%s, 'MData')""",
                [sys_date,temperature_2,humidity_2,temperature_3])

        elif (count_1 > 0 and count_2 == 0):
                cur.execute("""INSERT INTO duomenys (D_ActualDate, D_Temperature_1, D_Humidity_1 ,D_Temperature_2, D_Humidity_2, D_Temperature_3, D_Info)
                VALUES ( %s,%s,%s,NULL,NULL,%s, 'MData')""",
                [sys_date,temperature_1,humidity_1,temperature_3])

        else :
                cur.execute("""INSERT INTO duomenys (D_ActualDate, D_Temperature_1, D_Humidity_1 ,D_Temperature_2, D_Humidity_2, D_Temperature_3, D_Info)
                VALUES ( %s, NULL,NULL,NULL,NULL,%s, 'MData')""",
                [sys_date,temperature_3])
	
	
	
    else:
        if (count_1 > 0 and count_2 > 0):
                cur.execute("""INSERT INTO duomenys (D_ActualDate, D_Temperature_1, D_Humidity_1 ,D_Temperature_2, D_Humidity_2, D_Info)
                VALUES ( %s, %s,%s,%s,%s, 'CronJob')""",
                [sys_date,temperature_1,humidity_1,temperature_2,humidity_2])

        elif (count_1 == 0 and count_2 > 0):
                cur.execute("""INSERT INTO duomenys (D_ActualDate, D_Temperature_1, D_Humidity_1 ,D_Temperature_2, D_Humidity_2, D_Info)
                VALUES ( %s, NULL,NULL,%s,%s, 'CronJob')""",
                [sys_date,temperature_2,humidity_2])

        elif (count_1 > 0 and count_2 == 0):
                cur.execute("""INSERT INTO duomenys (D_ActualDate, D_Temperature_1, D_Humidity_1 ,D_Temperature_2, D_Humidity_2, D_Info)
                VALUES ( %s,%s,%s,NULL,NULL, 'CronJob')""",
                [sys_date,temperature_1,humidity_1])

        else :
                cur.execute("""INSERT INTO duomenys (D_ActualDate, D_Temperature_1, D_Humidity_1 ,D_Temperature_2, D_Humidity_2, D_Info)
                VALUES ( %s, NULL,NULL,NULL,NULL, 'CronJob')""",
                [sys_date])

                
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
    
