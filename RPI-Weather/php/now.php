<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="description" content="Most up to date RPI Weather Station data">
		<meta name="keywords" content="HTML,CSS,XML,JavaScript">
		<meta name="author" content="Paulius Bautrenas">
		<title>RPI Weather NOW</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
		<meta http-equiv=”Pragma” content=”no-cache”>
		<meta http-equiv=”Expires” content=”-1?>
		<meta http-equiv=”CACHE-CONTROL” content=”NO-CACHE”>


	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script> 
		
		

		var first_load = 1;		
		
		function eraseCache(){
			  console.log("Apsivalymas!");
			  window.location = window.location.href+'?eraseCache=true';
			  window.location.reload(true);
		}
		
		
		function Start() { 
			action = window.setInterval("getData()",1*60*1000);
			cleanUP = window.setInterval("eraseCache()",60*60*1000);//
			if(first_load == 1){	
				getData(); //first load
			} else{
				first_load=0;
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
		
		
		function getData(){
					
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
							
							//inside
							document.getElementById("TH2").innerHTML = T2 +" Â°C" + " " + H2 +" %";
							//outside
							document.getElementById("TH1").innerHTML = T1 +" Â°C" + " " + H1 +" %";
							//heater
							document.getElementById("T3").innerHTML = T3 +" Â°C";
							//Air Pressure:
							document.getElementById("PRSS").innerHTML = P +" hPa" + " (" + T4 +" Â°C)";
							
					});	
				};
	
    
		
		
	</script>
	</head>
		
	<body onload="Start()" style="background-color:#686868;">
	

		<div class="container-fluid" style="background-color:#686868; padding-top:5px;">			
			<div class="row">				
						
					<h1 id="laikas" style="color:white"   class="text-muted text-uppercase">Time and Date</h1>
					<h1 id="text" style="color:grey" class="text-muted">Inside:</h1>
					<h2 id="TH2" style="color:white" class="text-muted">TH2</h2>
										
					<h1 id="text" style="color:grey" class="text-muted">Outside:</h1>
					<h2 id="TH1" style="color:white" class="text-muted">TH1</h2>
					
					<h1 id="text" style="color:grey" class="text-muted">Heater:</h1>
					<h2 id="T3" style="color:white" class="text-muted">T3</h2>
					
					<h1 id="text" style="color:grey" class="text-muted">Pressure:</h1>
					<h2 id="PRSS" style="color:white" class="text-muted">PRSS</h2>
															
			</div>					
		</div>

	

	</body>
</html>