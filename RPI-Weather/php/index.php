<!DOCTYPE html>
	<head>	
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" type="text/css" href="style.css">
		<title>RPI WEATHER STATION</title>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
		<script  type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
		
		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		
		<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.0/css/bootstrap-toggle.min.css" rel="stylesheet">
		<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.0/js/bootstrap-toggle.min.js"></script>
		
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', 'UA-52275128-2', 'auto');
		  ga('send', 'pageview');

		</script>
		<script type="text/javascript">
		// --------------------------- Day Time calculations and band plotting ----------------------
		
		function computeSunrise(day, sunrise) {
			/*Sunrise/Sunset Algorithm taken from
			http://williams.best.vwh.net/sunrise_sunset_algorithm.htm
			inputs:
			day = day of the year
			sunrise = true for sunrise, false for sunset
			output:
			time of sunrise/sunset in hours */
			//lat, lon for Vilnius, Lithuania
			var longitude = 25.2800243;
			var latitude = 54.6893865;
			var zenith =  90.83333333333333;
			var D2R = Math.PI/180;
			var R2D = 180/Math.PI;
			// convert the longitude to hour value and calculate an approximate time
			var lnHour = longitude/15;
			var t;
			if (sunrise) {
				t = day + ((6-lnHour)/24);
			} else {
				t =day + ((18-lnHour)/24);
			};
			//calculate the Sun's mean anomaly
			M = (0.9856 * t) - 3.289;
			//calculate the Sun's true longitude
			L = M + (1.916 * Math.sin(M*D2R)) + (0.020 * Math.sin(2 * M* D2R)) + 282.634;
			if (L > 360) {
			L = L - 360;
			} else if (L < 0) {
				L = L + 360;
			};
			//calculate the Sun's right ascension
			RA = R2D*Math.atan(0.91764 * Math.tan(L*D2R));
			if (RA > 360) {
				RA = RA - 360;
			} else if (RA < 0) {
				RA = RA + 360;
			};
			//right ascension value needs to be in the same qua
			Lquadrant = (Math.floor(L/(90))) * 90;
			RAquadrant = (Math.floor(RA/90)) * 90;
			RA = RA + (Lquadrant - RAquadrant);
			//right ascension value needs to be converted into hours
			RA = RA / 15;
			//calculate the Sun's declination
			sinDec = 0.39782 * Math.sin(L*D2R);
			cosDec = Math.cos(Math.asin(sinDec));
			//calculate the Sun's local hour angle
			cosH = (Math.cos(zenith*D2R) - (sinDec * Math.sin(latitude*D2R))) / (cosDec * Math.cos(latitude*D2R));
			var H;
			if (sunrise) {
				H = 360 - R2D*Math.acos(cosH)
			} else {
				H = R2D*Math.acos(cosH)
			};
			H = H /15;
			//calculate local mean time of rising/setting
			T = H + RA - (0.06571 * t) - 6.622;
			//adjust back to UTC
			UT = T - lnHour;
			if (UT > 24) {
				UT = UT - 24;
			} else if (UT < 0) {
				UT = UT + 24;
			}
			//convert UT value to local time zone of latitude/longitude
			localT = UT + 3 ;
			//convert to Milliseconds
			return localT*3600*1000;
			}
			
		function dayOfYear(diena) {
				var yearFirstDay = Math.floor(new Date().setFullYear(new Date().getFullYear(), 0, 1) / 86400000);
				var today = Math.ceil((new Date(diena).getTime()) / 86400000);
				var dayOfYear = today - yearFirstDay;
				return dayOfYear;
		}
		// ------------------ Plot sunrise and sunset plot bands ------------
		
		//---------------------- plotDayTime -------------------------
		function plotDayTimeCustom(dMin,dMax,dCount){	
			
					
				
				console.log("dMin = "+dMin + " dMax = " + dMax + " dCount = " +dCount);		
				
				no_sun = ['1yminmax', '1y', '3m'];   			  
				options.xAxis.plotBands = []		
								
				for (var i = 0; i <= dCount; i++) {
					var d = new Date(dMin);				  
					d.setHours(0,0,0,0);
				  //console.log("[1] d= " +d);
				  	d.setDate(d.getDate()+i);
				 // console.log("[2] d= " +d);
				  				
					var sunrise = d.getTime()+computeSunrise(dayOfYear(d), true);
					var sunset = d.getTime()+computeSunrise(dayOfYear(d), false);
					
					console.log("sunrise: " + new Date(sunrise) + "   sunset: " + new Date(sunset) + "dayOfYear()=	"+dayOfYear(d));
					
				options.xAxis.plotBands.push(
				  {				  
					from: sunrise,
					to: sunset,
					color: '#FCFFC5',
					id: 'daytime'
				  }
				  );
				};					
		};
		
		// ----------------------- Turn OFF/ON plot bands (by clicking on legend item "Day Time" ---------------
		function toggleBands(chart){
			$.each(chart.xAxis[0].plotLinesAndBands, function(index,el){
				if(el.svgElem != undefined) {
					el.svgElem[ el.visible ? 'show' : 'hide' ]();
					el.visible = !el.visible;
				}
			});			
		};
		
		// ----------------------------------- HighChart configuration & stuff -----------------------------------
		
		var options;
		var Temp1 = [];
		var Temp2 = [];
		var Temp3 = [];
		var Temp4 = [];
		var Prss = [];
		var Hum1 = [];
		var Hum2 = [];	
		
		var first_load = 1; //is it?
		var data_type = 0; // Skirta aJax uzkrovimui: 0 - getData(); 1 - update_series();	
		var data_avg = 1; //ar norim matyti valandu vidurkius, ar "zalius" duomenis
		var zoom = 0; // prizoominta ar ne?
		
		
		
		$(document).ready(function() {
			
			//-----------detecting windows size -------------
			console.log("window H: " + $(window).height() + " W:" + $(window).width());
			table_fitter($(window).width());
			
			//-- Tooltip formavimas is http://www.tutorialspoint.com/bootstrap/bootstrap_tooltip_plugin.htm
			$('[data-toggle="tooltip"]').tooltip({
				placement : 'top'
			});
			
			$('#next').prop('disabled', true); //isjungiam "Next" mygtuka primo uzsikrovimo metu
			
			// --- HighCharts options:
			
			Highcharts.setOptions({
				global: {
					useUTC: false
				}
			});
			
			options = {
								
				chart: {
					animation: false,
					zoomType: 'x',
					renderTo: 'container',
                    type: 'line',
					//alignTicks: false,
                    //marginRight: 130,
                    //marginBottom: 25
					
					
					events: {
						load: function() {
							if(first_load == 1){
								console.log("load event..");
																
								// set up the updating of the chart each second
								setInterval(function(){
									getLastData();					
									console.log("getLastData update!");	
									
									if(data_type == 0){
										getData(); //load data from jSon
										console.log("getData update!");			
									} else{
										update_series(); //load custom data from jSon
										console.log("update_series update!");
									}							
								}, 60*1000);
								
								first_load = 0;
							}
							else{ first_load = first_load;}
						},
						selection: function (event) { 
							if (event.xAxis) { 	//patikrinimas, ar yra prizoominta
								zoom = 1;}
							else { 				//isejus is zoomo, perpiesiam grafika
								zoom = 0;
								if(data_type == 0){
									getData(); //load data from jSon
									console.log("getData update!");								
								} else{
									update_series(); //load custom data from jSon
									console.log("update_series update!");
								}
							}	
							console.log("zoom: " + zoom);
						}						
					}
					
				
                },
				  
				
				exporting:{
					//width: 1024,
					sourceWidth: 1920
				},
                title: {
                    text: 'RPI WEATHER STATION',
                    x: -20 //center
                },
                subtitle: {
                    text: '',
                    x: -20
                },
				
				lang: {
					decimalPoint: ',',
					thousandsSep: ' '
				},
				
						
                xAxis: {
                   
					type: 'datetime',
					//startOnTick: true,
					showFirstLabel: true,
					//endOnTick: true,
					showLastLabel: true,
				    
					//categories: [],
					
					
					/*
					title: {
						text: 'Time'
					},
					*/					
					
					
					gridLineWidth: 1, 
					GridLineColor: '#E0E0E0',
					minorTickInterval: 24 * 3600  * 1000,
					
					//minorTickPosition:'outside',
					//minorTickLength:10,
					//minorTickWidth: 1,
					
					minorGridLineColor: '#E0E0E0',
					minorGridLineWidth: 5,
					//minorTickLength: 0,
					//minorTickInterval: 1, //change interval
					
					//tickInterval: 24 * 3600 * 1000,
					//pointInterval: 24 * 3600 * 1000,
					
					dateTimeLabelFormats: {						
						millisecond:"%H:%M",
						second:"%H:%M",
						minute:"%H:%M",
						hour:"%Y-%m-%d %H:%M",
						day:"%Y-%m-%d",
						week:"%Y-%m-%d",
						month:"%Y-%m-%d",
						year:"%Y"
					},
					labels: {
						align: 'center',
						overflow: 'justify',
						//align: 'left',
						step: 1,
						enabled: true,
					}
					
					
				},
                yAxis: [{ //primary Y axis
                    title: {
                        text: 'Temperature, C',
						//max: 30,
						tickInterval:10, //set ticks to 20
                    },
                    plotLines: [{
                        value: 0,
                        width: 2,
                        color: '#FF0000'
                    }]					
					
                },
					{//secondary Y axis
					//tickInterval:10, //set ticks to 20
					min: 0,
					max: 100,
					endOnTick: true,
					title: {
                        text: 'Humidity, %'
                    }
					
				}
				,
					{//third Y axis
					//tickInterval:100, //set ticks to 20
					min: 970,
					max: 1010,
					endOnTick: true,
					title: {
                        text: 'Air Pressure, hPa'
                    }
					
				}],
                tooltip: {
				
                   	xDateFormat: '%Y-%m-%d %H:%M',	
					crosshairs: true,
					shared: true
					
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -10,
                    y: 100,
                    borderWidth: 0
                },
				
				//To turn ON/OFF Day Time plot bands
				plotOptions:{				
					area:{
						events:{
							legendItemClick: function() {
								if (this.name == 'Day Time') {
									toggleBands(chart);
								}
							}
						}
					}
				},
				
					
					
				credits: {
					enabled: false,
					text: 'Paulius Bautrenas',
					href: 'http://paulius.bautrenas.lt'
				},
                series: [
					
					
					{
						animation: false,
						type: 'spline',
						name: 'Inside Temp.',
						tooltip: {
							valueSuffix: '°C'
						},
						data: Temp2 //[]
					},
					
					{
						animation: false,
						name: 'Inside Hum.',
						//color: '#4572A7',
						//visible: false,
						type: 'spline',
						tooltip: {
							valueSuffix: ' %'
						},
						yAxis: 1,
						data: Hum2
					},
					
					{
						animation: false,
						type: 'spline',
						name: 'Outside Temp.',
						tooltip: {
							valueSuffix: '°C'
						},
						data: Temp1 //[]
					},
					
					{
						animation: false,
						name: 'Outside Hum.',
						//visible: false,
						//color: '#4572A7',
						type: 'spline',
						tooltip: {
							valueSuffix: ' %'
						},
						yAxis: 1,
						//dashStyle: 'longdash',
						data: Hum1
					},
					
					{
						animation: false,
						type: 'spline',
						name: 'Heater Temp.',
						tooltip: {
							valueSuffix: '°C'
						},
						data: Temp3 //[]
					},
					
					{
						animation: false,
						name: 'Air Pressure',
						//visible: false,
						//color: '#4572A7',
						type: 'spline',
						tooltip: {
							valueDecimals: 2,
							valueSuffix: ' hPa'
						},
						yAxis: 2,
						//dashStyle: 'longdash',
						data: Prss
					},
					
					
					{
						animation: false,
						type: 'spline',
						name: 'BMP085, °C',
						tooltip: {
							valueSuffix: '°C'
						},
						data: Temp4 //[]
					},
					
					{
						animation: false,
						type:'area',
						name:'Day Time',
						yAxis: 1,
						color: '#FCFFC5'
					}
				]
            };
            
			
			// ------- 4 first load -------
			 			
			getData(); //joad data from jSon
			getLastData(); //joad data from jSon
			
			
			
        });
		//-----------detecting windows size -------------
		$(window).on('resize', function(){
			console.log("window H: " + $(window).height() + " W:" + $(window).width());
			table_fitter($(window).width());
		});
		
		// --- removing unneeded table collumns depending on windows size ----
		function table_fitter(width){
			
			if (width > 1080 && width <=1180 ){
			//	$('td:nth-child(6),th:nth-child(6)').hide();
			//	$('td:nth-child(7),th:nth-child(7)').hide();
				$('td:nth-child(8),th:nth-child(8)').hide();
			}
			
		   else if (width > 1000 && width <=1080){
			//	$('td:nth-child(6),th:nth-child(6)').hide();
				$('td:nth-child(7),th:nth-child(7)').hide();
			//  $('td:nth-child(8),th:nth-child(8)').hide();
			}
			
			else if (width < 1000){
				$('td:nth-child(6),th:nth-child(6)').hide();
				//$('td:nth-child(7),th:nth-child(7)').hide();
				//$('td:nth-child(8),th:nth-child(8)').hide();
			}
			else {
				$('td:nth-child(6),th:nth-child(6)').show();
				$('td:nth-child(7),th:nth-child(7)').show();
				$('td:nth-child(8),th:nth-child(8)').show();	
			}
			
		}
		
		//-------fucking Chrome doesnt't support .toLocaleFormat('%Y-%m-%d') :/
		//-------this function does the same thing as .toLocaleFormat 
		Date.prototype.FormatDate = function(format) {
		var f = {y : this.getYear() + 1900,m : this.getMonth() + 1,d : this.getDate(),H : this.getHours(),M : this.getMinutes(),S : this.getSeconds()}
			for(k in f)
				format = format.replace('%' + k, f[k] < 10 ? "0" + f[k] : f[k]);
			return format;
		};
		
		
		// --------------------------------------------  Turning ON/OFF historical data averaging ------------------------------
		// Check box bootstrap style from here: http://www.bootstraptoggle.com/#demos
		// Event function from https://www.npmjs.com/package/bootstrap-toggle 
		$(function() {
			$('#AvgCheck').change(function() {
				 if ($(this).prop('checked')) {					
					data_avg = 1 ;
				} else {
				data_avg = 0;	
				}
			console.log('data_avg: ' + data_avg);
			update_series();
			})
		})
			
		
		// -------------------- jQuary Date selector ------------------------------------------------------------------------------------------------------------------
		var dateStart=new Date(new Date().setDate(new Date().getDate()-7));			
		var dateEnd=new Date(new Date().setDate(new Date().getDate()+1));
		var dayCount=8;

		var dateMin=dateStart.FormatDate('%y-%m-%d');
		var dateMax=dateEnd.FormatDate('%y-%m-%d');
		
		//var dMin=(new Date(new Date().setDate(new Date().getDate()-7)));
		//var dMax=(new Date(new Date().setDate(new Date().getDate()+1)));	
		//var dateMin = dateFormater(dMin);
		//var dateMax = dateFormater(dMax);
		
		$(function() {
			$( "#from" ).datepicker({
				//defaultDate: "+1w",
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				firstDay: 1,
				numberOfMonths: 1,
				onClose: function( selectedDate_Min ) {
					$( "#to" ).datepicker( "option", "minDate", selectedDate_Min );
					document.getElementById("to").style.color="black";
					document.getElementById("from").style.color="black";
					dateMin=selectedDate_Min;
				}
			});
			$( "#to" ).datepicker({
				//defaultDate:  new Date(),//"+1w",
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				firstDay: 1,
				numberOfMonths: 1,
				onClose: function( selectedDate_Max ) {
					$( "#from" ).datepicker( "option", "maxDate", selectedDate_Max );
					document.getElementById("to").style.color="black";
					document.getElementById("from").style.color="black";
					dateMax=selectedDate_Max;
				}
			});
			//default datapicker values (7 days ago - now):
			$("#from").datepicker("setDate", dateMin);
			$("#to").datepicker("setDate", dateMax);	
			//isolate selected date range in datepickers:
			$("#to").datepicker("option","minDate",dateMin );
			$("#from").datepicker("option","maxDate",dateMax );
			
		});
		
		//for checking if variables are empty of not
		function empty(data){
		  if(typeof(data) == 'number' || typeof(data) == 'boolean'){return false;}
		  if(typeof(data) == 'undefined' || data === null){return true;}
		  if(typeof(data.length) != 'undefined'){return data.length == 0;}
		  var count = 0;
		  for(var i in data){
			if(data.hasOwnProperty(i))
			{
			  count ++;
			}
		  }
		  return count == 0;
		};
		
		// --------------------
		
		function getLastData(){
					
					console.log("Funkcija: getLastData()");
					
					$.getJSON("data.php?getLast=1", function(obj){
					
							var AT= obj.datapoints[0].at;  	//linux timestamp
							
							var T1= obj.datapoints[0].T1;  
							var H1= obj.datapoints[0].H1;
							var T2= obj.datapoints[0].T2;  
							var H2= obj.datapoints[0].H2;
							var T3= obj.datapoints[0].T3; 
							var P=	obj.datapoints[0].P;
							var T4= obj.datapoints[0].T4; 
													
							var laikas=new Date(AT).FormatDate('%y-%m-%d %H:%M');
							document.getElementById("laikas").innerHTML = laikas;
							
							//check if vars are "null"
							if (!T1) T1="-"; else T1=T1;
							if (!H1) H1="-"; else H1=H1;
							if (!T2) T2="-"; else T2=T2;
							if (!H2) H2="-"; else H2=H2;
							if (!T3) T3="-"; else T3=T3;
							if (!P) P="-"; else P=P;
							if (!T4) T4="-"; else T4=T4;
							
							//inside
							document.getElementById("TH2").innerHTML = T2 +" °C" + " " + H2 +" %";
							//outside
							document.getElementById("TH1").innerHTML = T1 +" °C" + " " + H1 +" %";
							//heater
							document.getElementById("T3").innerHTML = T3 +" °C";
							//Air Pressure:
							document.getElementById("PRSS").innerHTML = P +" hPa" + " (" + T4 +" °C)";
							
					});	
				};
	
		
		
		// --------------------functions for LOADING jSon DATA to chart series---------------------------
		//load default JSON dataset to chart
		function getData(){
			
			console.log("Funkcija: getData()");
			
			var Temp1 = [];
			var Temp2 = [];
			var Temp3 = [];
			var Temp4 = [];
			var Prss = [];
			var Hum1 = [];
			var Hum2 = [];
						
			
			//to tell that we are using default date interval:
			data_type=0;
			
			$('#next').prop('disabled', true);
			
			 $.getJSON('data.php', function(inData) {
				var xval = new Date();	
				
				// ----------------- ploting daytime bandplots -----------
				dateStart = new Date(inData.datapoints[0].at);
				dateEnd = new Date(inData.datapoints[inData.datapoints.length-1].at);
				dateEnd = new Date(dateEnd.setDate(dateEnd.getDate()+1)); 	//adding one extra day to dateEnd		
				dayCount =  ((inData.datapoints[inData.datapoints.length-1].at - inData.datapoints[0].at)/1000/3600/24).toFixed();
				//dayCount = new Date(dateEnd-dateStart).getDate();
				
				plotDayTimeCustom(dateStart,dateEnd,dayCount);					

				
					for (i = 0; i < inData.datapoints.length; i++) {
						var yval = inData.datapoints[i].T1;
						xval = inData.datapoints[i].at;
						var x = [xval, yval];

						Temp1.push(x);
					}
					
					for (i = 0; i < inData.datapoints.length; i++) {
						var yval = inData.datapoints[i].T2;
						xval = inData.datapoints[i].at;
						var x = [xval, yval];
						Temp2.push(x);
					}
					
					for (i = 0; i < inData.datapoints.length; i++) {
						var yval = inData.datapoints[i].T3;
						xval = inData.datapoints[i].at;
						var x = [xval, yval];
						Temp3.push(x);
					}
					
					for (i = 0; i < inData.datapoints.length; i++) {
						var yval = inData.datapoints[i].T4;
						xval = inData.datapoints[i].at;
						var x = [xval, yval];
						Temp4.push(x);
					}
					
					for (i = 0; i < inData.datapoints.length; i++) {
						var yval = inData.datapoints[i].P;
						xval = inData.datapoints[i].at;
						var x = [xval, yval];
						Prss.push(x);
					}
					
					for (i = 0; i < inData.datapoints.length; i++) {
						var yval = inData.datapoints[i].H1;
						xval = inData.datapoints[i].at;
						var x = [xval, yval];

						Hum1.push(x);
					}
					
					for (i = 0; i < inData.datapoints.length; i++) {
						var yval = inData.datapoints[i].H2;
						xval = inData.datapoints[i].at;
						var x = [xval, yval];

						Hum2.push(x);
					}
		 
				options.series[0].data = Temp2;  
				options.series[1].data = Hum2;	
				options.series[2].data = Temp1; 	
				options.series[3].data = Hum1; 	
				options.series[4].data = Temp3; 
				options.series[5].data = Prss; 
				options.series[6].data = Temp4; 
				
				if (zoom == 0){
					chart = new Highcharts.Chart(options);
				}
				else{
					console.log(" -- data updated, but not redrawn");
				}
				
				
				
				//default datepicker values for default jSon
				//dateMin=(new Date(new Date().setDate(new Date().getDate()-7))).FormatDate('%y-%m-%d');
				//dateMax=(new Date(new Date().setDate(new Date().getDate()+1))).FormatDate('%y-%m-%d');
				dateMin=dateStart.FormatDate('%y-%m-%d');
				dateMax=dateEnd.FormatDate('%y-%m-%d');
				console.log('dateMin -> getData(): ' + dateMin);
				console.log('dateMax -> getData(): ' + dateMax);
				//display default datapicker values (7 days ago - now):
				$("#from").datepicker("setDate", dateMin);
				$("#to").datepicker("setDate", dateMax);
				document.getElementById("from").style.color="black";
				document.getElementById("to").style.color="black";
				//isolate selected date range in datepickers:
				$("#to").datepicker("option","minDate",dateMin);
				$("#from").datepicker("option","maxDate",dateMax );				
							
            });		
		};
		
				
		//redraw chart with new selected data
		function update_series(){	
			
			console.log("Funkcija: update_series()");
			console.log(dateMin);
			console.log(dateMax);
			
					
			//if(dateMin != null && dateMax != null){
			if(empty(dateMin) == false && empty(dateMax) == false){
				var Temp1 = [];
				var Temp2 = [];
				var Temp3 = [];
				var Temp4 = [];
				var Prss = [];
				var Hum1 = [];
				var Hum2 = [];		
				
				//to tell that we are using custom date interval:
				data_type=1;
				
				$.getJSON("data.php?dateMin="+dateMin+"&dateMax="+dateMax+"&dataAvg="+data_avg, function(inData){
					
					// ------------------ daytime plots drawing --------------------
					dateStart = new Date(inData.datapoints[0].at);
					dateEnd = new Date(inData.datapoints[inData.datapoints.length-1].at);
					dateEnd = new Date(dateEnd.setDate(dateEnd.getDate()+1)); 	//adding one extra day to dateEnd
					//dayCount = new Date(dateEnd-dateStart).getDate();
					dayCount =  ((inData.datapoints[inData.datapoints.length-1].at - inData.datapoints[0].at)/1000/3600/24).toFixed();			
					
					plotDayTimeCustom(dateStart,dateEnd,dayCount); //ispiesiam sviesaus paros meto juostas
					
					// ------------------- "Next" mygtuko valdymas ------------
					if(dateEnd >= new Date()){
					$('#next').prop('disabled', true);	
					}//ijungiam next mygtuka		
					else {$('#next').prop('disabled', false);	//ijungiam next mygtuka		
					}
						
					
					//quick check if there is some data available in the dataset:
					if(inData.datapoints.length==0){
						from.value="No data available!";	document.getElementById("from").style.color="red";
						to.value="No data available!";	document.getElementById("to").style.color="red";
					} else {
						document.getElementById("from").style.color="black";
						document.getElementById("to").style.color="black";}
					
					var xval = new Date();	
										
				
						for (i = 0; i < inData.datapoints.length; i++) {
							var yval = inData.datapoints[i].T1;
							xval = inData.datapoints[i].at;
							var x = [xval, yval];
							Temp1.push(x);
						}
						
						for (i = 0; i < inData.datapoints.length; i++) {
							var yval = inData.datapoints[i].T2;
							xval = inData.datapoints[i].at;
							var x = [xval, yval];
							Temp2.push(x);
						}
						
						for (i = 0; i < inData.datapoints.length; i++) {
							var yval = inData.datapoints[i].T3;
							xval = inData.datapoints[i].at;
							var x = [xval, yval];
							Temp3.push(x);
						}
						
						for (i = 0; i < inData.datapoints.length; i++) {
						var yval = inData.datapoints[i].T4;
						xval = inData.datapoints[i].at;
						var x = [xval, yval];
						Temp4.push(x);
						}
						
						for (i = 0; i < inData.datapoints.length; i++) {
							var yval = inData.datapoints[i].P;
							xval = inData.datapoints[i].at;
							var x = [xval, yval];
							Prss.push(x);
						}
						
						for (i = 0; i < inData.datapoints.length; i++) {
							var yval = inData.datapoints[i].H1;
							xval = inData.datapoints[i].at;
							var x = [xval, yval];

							Hum1.push(x);
						}
						
						for (i = 0; i < inData.datapoints.length; i++) {
							var yval = inData.datapoints[i].H2;
							xval = inData.datapoints[i].at;
							var x = [xval, yval];

							Hum2.push(x);
						}
					
					options.series[0].data = Temp2;  
					options.series[1].data = Hum2;	
					options.series[2].data = Temp1; 	
					options.series[3].data = Hum1; 	
					options.series[4].data = Temp3;
					options.series[5].data = Prss; 
					options.series[6].data = Temp4; 					
					
					if (zoom == 0){
						chart = new Highcharts.Chart(options);
						
					}
					else{
						console.log(" -- data updated, but not redrawn");
					}	
				});
			}//end of if
			else if(empty(dateMin) == true && empty(dateMax) == false){
				from.value="Select date!";	document.getElementById("from").style.color="red";
			}
			else if(empty(dateMin) == false && empty(dateMax) == true){
				to.value="Select date!";	document.getElementById("to").style.color="red";
			}
			else{
				from.value="Select date!";	document.getElementById("from").style.color="red";
				to.value="Select date!";	document.getElementById("to").style.color="red";
			};
		};
		
		
		function turn_time(back){
			
			var dS = new Date(dateStart);
			var dE = new Date(dateEnd);
			console.log("[0] dS: "+dS+ " dE: "+ dE);	
			if(back==1){
				$('#next').prop('disabled', false);	//ijungiam next mygtuka			
				
				dateStart = new Date(dS.setDate(dS.getDate() - 7));	
				console.log("[1] dateStart: "+dateStart+ " dateEnd: "+ dateEnd);				
				dateEnd = new Date(dE.setDate(dE.getDate() - 7));					
				console.log("[2] dateStart: "+dateStart+ " dateEnd: "+ dateEnd);
			} else{
				dateStart = new Date(dS.setDate(dS.getDate() + 7));	
				console.log("[1] dateStart: "+dateStart+ " dateEnd: "+ dateEnd);				
				dateEnd = new Date(dE.setDate(dE.getDate() + 7));					
				console.log("[2] dateStart: "+dateStart+ " dateEnd: "+ dateEnd);
			}			
			
			dateMin=dateStart.FormatDate('%y-%m-%d');
			dateMax=dateEnd.FormatDate('%y-%m-%d');			
			//display default datapicker values (7 days ago - now):
			$("#from").datepicker("setDate", dateMin);
			$("#to").datepicker("setDate", dateMax);
			document.getElementById("from").style.color="black";
			document.getElementById("to").style.color="black";
			//isolate selected date range in datepickers:
			$("#to").datepicker("option","minDate",dateMin);
			$("#from").datepicker("option","maxDate",dateMax );		
			
			update_series();				
		};
		</script>
	</head>
<body>
<script src="http://code.highcharts.com/4.0.4/highcharts.js"></script>  
<!-- <script src="http://code.highcharts.com/highcharts.js"></script> --> 
<script src="http://code.highcharts.com/4.0.4/modules/exporting.js"></script>

<div class="container-fluid" style="padding:0px; margin:10px; border:1px solid black; border-radius: 10px;">	
	<div class="row">	
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<div class="table-responsive">
					<table class="table table-reflow">
					<thead>
						<tr>
							<th></th>
							<th><h4>Inside</h4></th>
							<th><h4>Outside</h4></th>
							<th><h4>Heater</h4></th>
							<th><h4>Pressure</h4></th>
							<th><h4 style="color:white">(ノಠ益ಠ)ノ彡┻━┻ |</h4></th>
							<th><h4 style="color:white">┻━┻︵ \(°□°)/ ︵ ┻━┻</h4></th>
							<th><h4 style="color:white">ლ(ಠ益ಠლ)</h4></th>

						</tr>
					  </thead>
					  <tbody>
						<th scope="row">
							<h5 id="laikas">Time and Date</h5>
						</th>
							<td><h5 id="TH2">TH2</h5></td>
							<td><h5 id="TH1">TH1</h5></td>
							<td><h5 id="T3">T3</h5></td>
							<td><h5 id="PRSS">PRSS</h5></td>
							<th><h5 style="color:white">____________________________</h5></th>
							<th><h5 style="color:white">____________________________</h5></th>
							<th><h5 style="color:white">¯\_(ツ)_/¯</h5></th>
					  </tbody>
					</table>
				</div>
		</div>
	</div>	
</div>				
<div class="container-fluid" style="padding:20px; margin:10px; border:1px solid black; border-radius: 10px;">			
	
				
	
	<div class="row">
		<form class="form-inline">
			<label for="from">From</label>
			<input type="text" id="from" name="from" class="form-control input-sm"/>
			<label for="to">to</label>
			<input type="text" id="to" name="to" class="form-control input-sm"/>
			<button type="button" onclick="javascript:update_series();" class="btn btn-primary btn-sm">Update!</button> 
			<button type="button" onclick="javascript:getData();" class="btn btn-danger btn-sm">Reset!</button> 
			<a data-toggle="tooltip" data-original-title="Data Averaging?">
				<input type="checkbox" id="AvgCheck" checked data-toggle="toggle" data-size="small" data-onstyle="success" data-offstyle="danger">
			</a>
			<a data-toggle="tooltip" data-original-title="Previous Week">
				<button type="button" id="prev" onclick="javascript:turn_time(1);" class="btn btn-default btn-sm">Previous</button> 
			</a>
			<a data-toggle="tooltip" data-original-title="Next Week">
				<button type="button" id="next" onclick="javascript:turn_time();" class="btn btn-default btn-sm">Next</button> 
			</a>					
		</form>
	</div>
	<div class="row">	  
		<div id="container" style="min-width: 400px; height: 600px; margin: 0 auto"></div>
	</div>
</div>

</body>
</html>
