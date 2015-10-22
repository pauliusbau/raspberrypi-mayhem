# raspberrypi-mayhem
This is the place, where I will put all my python and php scripts designed for RPI.

###1. RPI-Weather
<img src="http://paulius.bautrenas.lt/blog/wp-content/uploads/2015/02/highcarts1.png" width="800">


I have build my own RPI weather station too using 2 DHT22 and 1 ds18b20 sensors (BMP180 is still in a mail). I used MySQL + php for storing and visualizing sensor data (more info http://paulius.bautrenas.lt/blog/?p=642). One strange problem I had to deal with was an incorrect DHT22 readings. For example 1 out of 10 reading could differ from the other by several degrees. Maybe it was due to poor quality sensors or long cable or magnetic radiation or smth., but I just couldn’t stand storing incorrect data. Simple data points averaging did’t help, but I did found a way how to eliminate misleading data point using python and “Interquartile range” method. More info can be found here: http://paulius.bautrenas.lt/blog/?p=550 (sorry it’s in Lithuanian)


###2. RPI-bluetoothTimeSynch

![](http://paulius.bautrenas.lt/blog/wp-content/uploads/2015/04/flip-dot-clock-synch.png)

My fist attempt to automatically synch my flip-dot clock with RPI via Bluetooth. More info can be found in my Lithuanian [blog](http://paulius.bautrenas.lt/blog/?p=750)..
