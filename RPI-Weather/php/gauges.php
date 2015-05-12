<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>DHT22_NOW</title>
		<meta name="viewport" content="width = device-width,initial-scale = 1" >
	<head>
	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
	<script  type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	<script src="http://code.highcharts.com/4.0.4/highcharts.js"></script>
	<script src="http://code.highcharts.com/4.0.4/highcharts-more.js"></script>
	<script src="http://code.highcharts.com/4.0.4/modules/exporting.js"></script>
	

	<script type="text/javascript">
		var options_out;
		var options_in;
		var getGaugeConfig;
		var Temp1 =[];
		var Temp2 =[];
		var Hum1 =[];
		var Hum2 =[];
		
		var charts = [];
		
		var first_load = 1; //is it?
		
		function Start(){
			if(first_load == 1){
				console.log("load event..");
				first_load = 0;
				
				setInterval(function(){getData();}, 60*1000);
			}
			else{ first_load = first_load;}
		
		};

		$(document).ready(function() {
		 
	// --------------------------------------- common options for gauge chart ---------------		
	
			getGaugeConfig  = function(renderID, title, data_temp, data_hum){ 
				var config = {};
				
				config.chart = {
					renderTo: renderID,
					type: 'gauge',
					alignTicks: false,
					plotBackgroundColor: null,
					plotBackgroundImage: null,
					plotBorderWidth: 0,
					plotShadow: false,
					spacingTop: 0,
					spacingLeft: 0,
					spacingRight: 0,
					spacingBottom: 0
					
				};
				config.exporting = {
					enabled: false
				};
				config.credits= {
					enabled: false
				};    
				config.title= {
					text: title
				};
				config.pane= {
					startAngle: -150,
					endAngle: 150,
					center: ['50%', '40%']
				};        
				config.yAxis= [{//Temperature
					min: -40,
					max: 40,
					lineColor: null,
					tickColor: 'grey',
					minorTickColor: 'grey',
					offset: -5,
					lineWidth: 2,
					labels: {
						distance: -20,
						rotation: 'auto'
					},
					labels: {
					   // distance: -5,
						rotation: 'auto',
						formatter: function() {
							   if (this.value >0) {
								  return '<span style="color: #A41E09; font-weight: bold; font-size: 10pt;">' + this.value + "</span>";
							   } 
								 else {
								   return '<span style="color: #000080; font-weight: bold; font-size: 10pt;">' + this.value + "</span>";
								  }
							   return this.value;
						}
					},
					plotBands: [{
						from: -40,
						to: 0,
						thickness: '5%',
						outerRadius: '100%',
						color: '#336' 
					}, {
						from: 0,
						to: 40,
						thickness: '5%',
						outerRadius: '100%',
						color: '#933' 
					}],  
					tickLength: 10,
					minorTickLength: 5,
					endOnTick: false
				}, {
					//Humidity
					min: 0,
					max: 100,
					tickPosition: 'inside',
					lineColor: 'grey',
					lineWidth: 2,
					offset: -40,
					pane:0,
					minorTickPosition: 'inside',
					//tickColor: '#933',
					//minorTickColor: '#933',
					tickLength: 10,
					minorTickLength: 5,                                 
					endOnTick: false
				}];
				config.series= [
					{name: "Temperature",             
					  dial: {
								backgroundColor : 'black',
								//radius: '50%',
								baseWidth: 3,
								baseLength: '95%',
								rearLength: 0
							},
					  dataLabels: {
							//color: '#E58964',
							borderWidth: 0,
							y: 50,
							x: 0,
							format: '{y} °C'
					  },   
					tooltip: {
							enable: false,
							valueSuffix: '°C'
						},				  
					data: data_temp            
					},
					{name: "Humidity",
					 yAxis: 1,
					  dial: {
							backgroundColor : 'grey',
							radius: '50%',
							baseWidth: 3,
							baseLength: '95%',
							rearLength: 0
						},
					 dataLabels: {
							//color: '#E58964',
							borderWidth: 0,
							y: 70,
							x: 0,
							format: '{y} %'
					  },       
					 tooltip: {	
								enable: false,
								valueSuffix: ' %'
							},
					 data: data_hum            
					}
				]   
				return config;
			};
			
			getData();
		});

		

		//load default JSON dataset to chart
				function getData(){
					
					$.getJSON("data.php?getLast=1", function(obj){
					
						//var jtext= '{"datapoints":[' + '{"at":1422267720000,"T1":-2.5,"T2":20.7,"H1":74.3,"H2":39.2,"T3":20.5}'+']}';
						//var obj = JSON.parse(jtext);
							
							var Temp1 =[];
							var Temp2 =[];
							var Hum1 =[];
							var Hum2 =[];
							
							//to tell that we are using default date interval:
							data_type=0;
							
							 
							var T1= obj.datapoints[0].T1;  
							var H1= obj.datapoints[0].H1;
							var T2= obj.datapoints[0].T2;  
							var H2= obj.datapoints[0].H2;
							   
							Temp1.push(T1);
							Hum1.push(H1);
							Temp2.push(T2);
							Hum2.push(H2);
							
							/*
							options_out.series[0].data = Temp1;
							options_out.series[1].data = Hum1;	 
							options_in.series[0].data = Temp2;
							options_in.series[1].data = Hum2;							
							chart = new Highcharts.Chart(options_out);	
							chart = new Highcharts.Chart(options_in);	
							*/

							charts.push(new Highcharts.Chart(getGaugeConfig("gauge_out", "OUTSIDE", Temp1, Hum1)));	
							charts.push(new Highcharts.Chart(getGaugeConfig("gauge_in", "INSIDE", Temp2, Hum2)));	
							
						
								
								
							$(window).trigger('resize');
									
					
					});	
				};
		</script>
	</head>
<body onload="Start()">	
	
		<div class="container" style="width:auto; padding:5px; margin:20px; border:0px solid black; border-radius: 10px;">			
			<div class="row">		
				<div class="col-sm-12 col-lg-3 col-xs-12">	
					<div id="gauge_out" style="border:0px solid black; width: 260; height: 260px; margin: 0px"></div>		
				</div>
				<div class="col-sm-12 col-lg-3 col-xs-12">				
					<div id="gauge_in"  style="border:0px solid black; width: 260; height: 260px; margin: 0px"></div>	
				</div>
			</div>
		</div>
</body>
</html>