<?php
// reference PHP class to connect to Sunlight Laps API // not using modified version of class (see line ~220)
include_once('class.sunlightlabs.mod.php');

$lat = (string)$_GET['lat'];
$lng = (string)$_GET['lng'];

// Enter Your Sunlight Labs API Key
$api_key = '';
$sf = new SunlightDistrict;
$sf->api_key = $api_key;
$result = $sf->districtsGeoloc($lat,$lng);

// array-ify the object
// http://stackoverflow.com/questions/1567698/stdclass-object-problems
$array = (array) $result;

// convert the entire object into a multidimensional (3D) array
$array2 = objectToArray($array);

function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}

// return results
echo $array2['state'] . "-" . $array2['number'];
?>