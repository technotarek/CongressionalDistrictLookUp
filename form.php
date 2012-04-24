<html>
	<head>
		<title>Congressional District Look Up by Geo-Coded Address</title>
		<link type="text/css" rel="stylesheet" href="css/screen.css" media="screen"/>
	</head>
	<body>
		<div class="container">
		<form method="post" id="form" name="form">
		  <fieldset>
			 <legend>Contact Information</legend>
			 <div class="row">
				<div class="span4">
				    <label for="address1">Address 1</label>
				    <input type="text" id="address1" name="address1" tabindex="1" />                                                  
				</div>
				<div class="span4">
				    <label for="address2">Address 2</label>
				    <input type="text" id="address2" name="address2" tabindex="2" class="" />                              
				</div>
			 </div>
			 <div class="row">
				<div class="span4">
				    <label for="city">City</label>
				    <input type="text" id="city" name="city" tabindex="3" />                                                  
				</div>
				<div class="span2">
				    <label for="state">State</label>
				    <input type="text" id="state" name="state" maxlength="2" tabindex="4" />
				</div>
				<div class="span3 offset1">
				    <label for="zip">Zip </label>
				    <input type="text" id="zip" name="zip" maxlength="5" tabindex="5" autocomplete="off">                    
				</div>
			 </div>
			 <div class="row">
			 	<div class="span12">
				    <label for="district">District<span id="ajaxStatus"></span></label>		    
				    <input type="text" name="district" id="district" />
				</div>
			 </div>
		  </fieldset>
		  <button type="submit" class="btn" name="submit">Submit</button>
		</form>
	</div>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script> 
	<script src="http://maps.googleapis.com/maps/api/js?key=GOOGLEAPIKEY&amp;sensor=false"></script>
    <script>
		$(document).ready(function() {

			// finds the length (eg number of characters) of a given input value
			// used to trigger congressional district look up when entered zip code reaches 5 characters
			function valueLength(inputSelector)
			{
				var inputLength = $(inputSelector).val().length;
				return inputLength;
			}

			// Add Congressional District - Main Pledge Form
			$("#zip").bind('keyup',function()
			{
				// when the entered zip code reaches 5 characters 
				if(valueLength(this) > 4)
				{
					codeAddress('address1','city','state','zip','#district');
				}
			});

		});

		// Use Google Maps V3 API to get GeoCode of user's address
		// Then, feed that geocode to the Sunlight Labs API to get the congressional district
		var geocoder;
		function codeAddress(street,city,state,zip,target) {
			geocoder = new google.maps.Geocoder();
			// street address
			var addressA = document.getElementById(street).value;

			// city
			var addressB = document.getElementById(city).value;

			// state
			var addressC = document.getElementById(state).value;

			// zip
			var addressD = document.getElementById(zip).value;

			// concatenated, full address
			var address = addressA+" "+addressB+" "+addressC+" "+addressD;
			geocoder.geocode( { 'address': address}, function(results, status) {
			  if (status == google.maps.GeocoderStatus.OK) {

			  	// based on Google result, set vars for latitude and longitude
				var geoLat = results[0].geometry.location.lat();
				var geoLng = results[0].geometry.location.lng();

					// AJAX -- CONGRESSIONAL DISTRICT LOOKUP
					$.ajaxSetup ({  
					        cache: false  
					    });  
					var ajax_load = "<img src='ajaxLoad.gif' alt='loading...' />";  

					//  load() functions  
					var loadUrl = "inc/sunlight-php/districtLookUpByGeo.php"; 

					// ajax call to look up matching district from sunlight labs api
					$("#ajaxStatus").html(ajax_load);

			        $.get(  
			            loadUrl,  
			            {
			            	lat: geoLat,
			            	lng: geoLng
			            },  
			            function(responseText){
			            	$("#ajaxStatus").html('');  
							$(target).val(responseText);
			            },  
			            "html"
			        );

			  } else {
			    alert("Geocode was not successful for the following reason: " + status);
			  }
			});
		}
    </script>
	</body>
</html>
