#Driver based on https://github.com/ControlEverythingCommunity/SI7021/blob/master/Python/SI7021.py

import smbus
import time

# Get I2C bus
try:
	bus = smbus.SMBus(1)
except (IOError,IndexError):
	print "I2C error.."	

def read_data(address, register):
	data = []
	
	bus.write_byte(address, register)	
	time.sleep(0.3)
	data.append(bus.read_byte(address))
	data.append(bus.read_byte(address))
	return data
	

# SI7021 address: 	0x40
# SI7021 register:	0xF5	Select Relative Humidity NO HOLD master mode
# SI7021 register:	0xF3	Select temperature NO HOLD master mode

def read_hum():
	data = read_data(0x40,0xF5)
	
	humidity = ((data[0] * 256 + data[1]) * 125 / 65536.0) - 6
	# print "Relative Humidity is : %.2f %%" %humidity
	return humidity

def read_temp():
	data = read_data(0x40,0xF3)
	
	temperature = ((data[0] * 256 + data[1]) * 175.72 / 65536.0) - 46.85
	# print "Temperature is : %.2f C" %temperature
	return temperature

# read_hum()
# time.sleep(0.3)
# read_temp()
# time.sleep(0.3)
