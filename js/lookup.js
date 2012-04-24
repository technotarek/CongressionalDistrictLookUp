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
			var ajax_load = "<img src='images/ajaxLoad.gif' alt='loading...' />";  

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