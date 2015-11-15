# raspberrypi-mayhem
This is the place, where I will put all my python and php scripts designed for RPI.

###1. RPI-Weather
<img src="/RPI-Weather/img/rpi_weather.png" width="800">


I have build my own RPI weather station using 2 DHT22 and ds18b20 and BMP180 sensors. I used MySQL + php for storing and visualizing sensor data (more info http://paulius.bautrenas.lt/blog/?p=642). One strange problem I had to deal with was an incorrect DHT22 readings. For example 1 out of 10 reading could differ from the other by several degrees. Maybe it was due to poor quality sensors or long cable or magnetic radiation or smth., but I just couldn’t stand storing incorrect data. Simple data points averaging did’t help, but I did found a way how to eliminate misleading data point using python and “Interquartile range” method. More info can be found here: http://paulius.bautrenas.lt/blog/?p=550 (sorry it’s in Lithuanian)


###2. RPI-bluetoothTimeSynch

![](http://paulius.bautrenas.lt/blog/wp-content/uploads/2015/04/flip-dot-clock-synch.png)

My fist attempt to automatically synch my [flip-dot clock](http://github.com/pauliusbau/electronics-farm/tree/master/Flip-Dot%20Clock) with RPI via Bluetooth. More info can be found in my Lithuanian [blog](http://paulius.bautrenas.lt/blog/?p=750)..
