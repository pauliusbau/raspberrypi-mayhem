#!/usr/bin/python
# -*- coding: utf-8 -*-

# ------------------------------------------------
# to run this scrip from Cron I use these settings:
# 30 2,4 * * * sudo python /home/python/flip-dot_synch.py &
# ------------------------------------------------
# 	If you want to "force" update time-date use a python script argument "f":
# 	sudo python /home/python/flip-dot_synch.py f
# ------------------------------------------------

import sys
import arrow
import re
import serial
import os
import time
import datetime
from time import sleep
from serial import SerialException 

def exit_nicely():
	bluetoothSerial.flushInput() #flush input buffer, discarding all its contents
	bluetoothSerial.flushOutput()#flush output buffer, aborting current output
	bluetoothSerial.close()
	f.close()
	exit()
	quit()


os.chdir(os.path.abspath('/home/python/'))

f = open('flip-dot_synch.log','a')

sys_date = arrow.now().format('YYYY-MM-DD HH:mm:ss')
s = str(sys_date + ' Flip-Dot Synch: \n')
f.write(s)


#status variables
connected = 0
update = 0

if len(sys.argv) >= 2:
	if (sys.argv[1] == 'f' or  sys.argv[1] == 'F' ):
		force_update = 1
	else: 
		force_update = None
	
else:
	force_update = None


class bcolors: #terminal text color
    HEADER = '\033[95m'
    OKBLUE = '\033[94m'
    OKGREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'
    UNDERLINE = '\033[4m'

#config variables
max_seconds_diff = 20


print  bcolors.HEADER + "Flip-Dot Clock auto-config V1.1" + bcolors.ENDC

try:
	bluetoothSerial = serial.Serial( "/dev/rfcomm1", baudrate=9600, timeout=3, stopbits = serial.STOPBITS_ONE, bytesize = serial.EIGHTBITS)
	bluetoothSerial.setRTS(0) 
	connected=1
except serial.SerialException:
		print bcolors.FAIL + "No connection to the device could be established" + bcolors.ENDC
		f.write("   No connection to the device could be established! \n")
		f.close()
		exit()
		quit()
		



if connected:  
	bluetoothSerial.flushInput() #flush input buffer, discarding all its contents
	bluetoothSerial.flushOutput()#flush output buffer, aborting current output
	
	bluetoothSerial.write('x') #quit any existing flip-dot clock config mode
	time.sleep(5)

	
	eilute = ""
	sys_date = arrow.now().format('YYYY-MM-DD HH:mm:ss')
	flipdot_date = arrow.get('1994-09-01 08:00:00', 'YYYY-MM-DD HH:mm:ss')
	
	bluetoothSerial.write( 'a' )
	time.sleep(1)
	

		
	for x in range (0,10):
		eilute = str(bluetoothSerial.readline())
		mat=re.findall(r"(\d+[-]\d+[-]+\d+\s+\d+[:]\d+[:]+\d*)", str(eilute))
		if mat:
			flipdot_date = arrow.get(str(mat), 'YYYY-MM-DD  HH:mm:ss')
			
	print " "
	
	if flipdot_date != arrow.get('1994-09-01 08:00:00', 'YYYY-MM-DD HH:mm:ss'):
		print "Clock  time: %s" %flipdot_date.format('YYYY-MM-DD HH:mm:ss')
		print "System time: %s" %sys_date.format('YYYY-MM-DD HH:mm:ss')

		
		fd = str(arrow.get(flipdot_date).format('YYYY-MM-DD HH:mm:ss'))
		s = str('   Clock  time: '+ fd +'\n')
		f.write(s)
		
		sd = str(arrow.get(sys_date).format('YYYY-MM-DD HH:mm:ss'))
		s = str('   System  time: '+  sd +'\n')
		f.write(s)

		time_difference=abs((arrow.get(flipdot_date) - arrow.get(sys_date)).total_seconds())
		print "Difference: %s seconds" %time_difference
		s = str('   Difference: '+ str(time_difference) +'\n')
		f.write(s)

		if time_difference > max_seconds_diff:
			print " "
			print (bcolors.WARNING + "Difference is bigger then %s seconds.." + bcolors.ENDC) %max_seconds_diff
			print "Attempting to update.."
			update = 1 # time needs to be synchronized
		elif (force_update == 1):
			print (bcolors.WARNING + "Force flip-sot clock date/time update!" + bcolors.ENDC)
			s = str('-- Force flip-sot clock date/time update --' + sys_date + '\n')
			f.write(s)	
		else:
			print " "
			print bcolors.OKGREEN + "Everything seems OK. Bay!"  + bcolors.ENDC
			f.write("   Everything seems OK. Bay!\n")
			
	else: 
		print "Could not read flip-dot clock"
		f.write("   Could not read flip-dot clock \n")
		bluetoothSerial.write( 'x' )
		time.sleep(3)
		exit_nicely()
	
	# 4 testing purposes:
	# update = 1
	# time_difference = 50
	
	#  Going to time/date update sequence:	
	if (update == 1 or force_update == 1):
		# --------------------------- DATE update sequence ------------------------- 
		if (time_difference >= 24*60*60 or force_update == 1): #date needs to be updated
			print ("%s Date update sequence [" + bcolors.OKBLUE + "start"  + bcolors.ENDC + "]")  %str(arrow.now().format('HH:mm:ss'))	
			
			bluetoothSerial.write('d') #enter clocks "set date" function 
			time.sleep(1)
			
			bluetoothSerial.flushInput() #flush input buffer, discarding all its contents
			bluetoothSerial.flushOutput()#flush output buffer, aborting current output
			
			bluetoothSerial.write(str(arrow.now().format('YY')))
			bluetoothSerial.write('\n')
			time.sleep(0.5)
			
			bluetoothSerial.write(str(arrow.now().format('MM')))
			bluetoothSerial.write('\n')
			time.sleep(0.5)
			
			bluetoothSerial.write(str(arrow.now().format('DD')))
			bluetoothSerial.write('\n')
			time.sleep(0.5)
			
					
						
			bluetoothSerial.write( 'y' )	
			time.sleep(0.1)
			
			
			for x in range (0,10):
				eilute = str(bluetoothSerial.readline())	
				mat=re.findall(r'Success!|Error!?', str(eilute)) 
				if mat:
					if str(mat)==str("['Success!']"):
						print ("%s Date update sequence [" + bcolors.OKGREEN + "success"  + bcolors.ENDC + "]")	 %str(arrow.now().format('HH:mm:ss'))	
						sys_date = arrow.now().format('YYYY-MM-DD HH:mm:ss')
						s = str('     Date update sequence [success] ' + sys_date + '\n')
						f.write(s)	
					else:
						print ("%s Date update sequence [" + bcolors.FAIL + "error"  + bcolors.ENDC + "]")  %str(arrow.now().format('HH:mm:ss'))	
						sys_date = arrow.now().format('YYYY-MM-DD HH:mm:ss')
						s = str('     Date update sequence [error] ' + sys_date + '\n')
						f.write(s)	
				
			time.sleep(1)
			
		else:
			print bcolors.OKGREEN + "Date does not need to be updated!" + bcolors.ENDC
			f.write('     Date does not need to be updated! \n')	
		
		
		# --------------------------- TIME update sequence ------------------------- 
		print ("%s Time update sequence [" + bcolors.OKBLUE + "start"  + bcolors.ENDC + "]")	%str(arrow.now().format('HH:mm:ss'))
		
					
		bluetoothSerial.write('t')	#enter clocks "set time" function
		time.sleep(1)
		
		bluetoothSerial.flushInput() #flush input buffer, discarding all its contents
		bluetoothSerial.flushOutput()#flush output buffer, aborting current output
		
		bluetoothSerial.write(str(arrow.now().format('HH')))
		bluetoothSerial.write('\n')
		time.sleep(0.5)
		
		bluetoothSerial.write(str(arrow.now().format('mm')))
		bluetoothSerial.write('\n')
		time.sleep(0.5)
		
		bluetoothSerial.write(str(arrow.now().format('ss')))
		bluetoothSerial.write('\n')
		time.sleep(0.5)

					
		bluetoothSerial.write( 'y' )	
		time.sleep(0.1)			
		
		
		for x in range (0,10):

			eilute = str(bluetoothSerial.readline())	

			# print eilute
			mat=re.findall(r'Success!|Error!?', str(eilute)) #\bSuccess!?\b 	
			if mat:
				if str(mat)==str("['Success!']"):
					print ("%s Time update sequence [" + bcolors.OKGREEN + "success"  + bcolors.ENDC + "]") %str(arrow.now().format('HH:mm:ss'))
					sys_date = arrow.now().format('YYYY-MM-DD HH:mm:ss')
					s = str('     Time update sequence [success] ' + sys_date + '\n')
					f.write(s)	
				else:
					print ("%s Time update sequence [" + bcolors.FAIL + "error"  + bcolors.ENDC + "]")	 %str(arrow.now().format('HH:mm:ss'))	
					sys_date = arrow.now().format('YYYY-MM-DD HH:mm:ss')
					s = str('     Time update sequence [error] ' + sys_date + '\n')
					f.write(s)						
					
		
		time.sleep(1)
		bluetoothSerial.write( 'x' )
		time.sleep(3)		
		exit_nicely()
			
	else: #nothing needs to be updated, can quit now		
		exit_nicely()
	
else:
	print bcolors.FAIL +  "I said, I was not connected!" + bcolors.ENDC
	f.write('I said, I was not connected! \n')	
	exit_nicely()

bluetoothSerial.write( 'x' )
time.sleep(3)
exit_nicely()