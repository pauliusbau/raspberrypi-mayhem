#RPI-bluetoothTimeSynch

![](http://paulius.bautrenas.lt/blog/wp-content/uploads/2015/04/flip-dot-clock-synch.png)

My fist attempt to automatically synch my flip-dot clock with RPI via Bluetooth. More info can be found in my Lithuanian [blog](http://paulius.bautrenas.lt/blog/?p=750)..

---
![](http://paulius.bautrenas.lt/blog/wp-content/uploads/2014/12/IMG_7319-e1431441515256.jpg)


Long story short, some time ago I have built a [flip-dot clock](http://paulius.bautrenas.lt/blog/?p=607) from and old flip-dot matrix used in a public bus. Due to unknown reasons, my clock started to run a bit fast. I decided to temporally fix it via [Bluetooth](http://paulius.bautrenas.lt/blog/?p=493) time synchronization with my RPI.

For this script to work, you have to do several things. First, you have to set up Bluetooth module on RPI and connect to a serial Bluetooth module, install Bluetooth python library and only then you can run the script.

####Setting up the USB Bluetooth Dongle and Pairing it with the Bluetooth Serial Module

Plug the USB Bluetooth dongle into the Raspberry Pi. Then open up a command line terminal and run the following commands
```
sudo apt-get update
sudo apt-get install bluetooth bluez-utils blueman
```

Get the name of your USB Bluetooth dongle by running. It should be something like 'hci0'.
```
hciconfig
```

Now, ensuring that the slave Bluetooth module is powered on, run the following command to find out the address of the serial Bluetooth module.
```
hcitool scan
```

After a short delay, this should return the addresses of nearby Bluetooth devices. 
![](http://paulius.bautrenas.lt/blog/wp-content/uploads/2015/04/hcitool_scan.png)

Before Bluetooth devices can communicate, they need to be paired. This can be done by running the following command:
```
sudo bluez-simple-agent hci# xx:xx:xx:xx:xx:xx
```

where # is the number of your device (probably hci0) and xx:xx:xx:xx:xx:xx is the address of the serial Bluetooth module. After a pause, this program should ask you for the pin code of the Bluetooth module. 

At this point, we have 2 Bluetooth devices that can communicate with each other, but we need to set up a protocol called RFCOMM so that they can communicate over a serial connection. Run:
```
sudo nano /etc/bluetooth/rfcomm.conf
```

to edit rfcomm.conf, and add the following lines:
```
rfcomm1 {
    bind yes;
    device xx:xx:xx:xx:xx:xx;
    channel 1;
    comment "Connection to Bluetooth serial module";
}
```

Where again, xx:xx:xx:xx:xx:xx is the address of the Bluetooth serial module. Once that is done, save the file and un the following command in order to get the serial port /dev/rfcomm1.
```
sudo rfcomm bind all
```
To test that your connection works, you could use a serial terminal such as minicom or cutecom (you may need to install these first). However, we’re going to use a Python script and the pySerial library:
```
sudo apt-get install python-serial
```
Also for script to run need to install [Arrow](http://crsmithdev.com/arrow/) python library:

```
sudo apt-get install python-setuptools
sudo easy_install pip
sudo pip install arrow
```


####Running the script

Now we are finally able to run time synchronization script:
```
sudo python /home/python/flip-dot_synch.py
```

![](http://paulius.bautrenas.lt/blog/wp-content/uploads/2015/04/flip-dot-clock-synch.png)

What script does is connects to a flip-dot clock, reads its time value and compares it to a RPI system clock. If time difference is bigger than 20 seconds, script initiates time and date synchronization sequence. Scrip also creates a log file:
```
less /home/python/flip-dot_synch.log
```
![](http://paulius.bautrenas.lt/blog/wp-content/uploads/2015/04/flipdot_synch_log.png)

####CRON automation

If you are like me you will want to run this script automatically every day. I do it with cron:
```
30 2,4 * * * sudo python /home/python/flip-dot_synch.py &
```

####FORCE time & date synchronization

If you want to force update time & date on your clock, i. e. to skip all RPI and flip-dot clock time/date difference calculations, you can use a python script argument "f":
```
sudo python /home/python/flip-dot_synch.py f
```
