<?php

/**

 * GeoTracker Module For Map Display or ip to lat/lon conversion

 * GeoTracker Module 

 * @link http://github.com/ppetree/geotracker.com
 * @copyright Copyright (C) 2023-2024 Phil Petree
 * @license https://github.com/ppetree/geotracker/blob/main/LICENSE GNU/GPL 
 * @version 1.9.0
 * Shows your visitors on a Google Map
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

$doc 			= JFactory::getDocument();
$juri 			= JURI::base();
$apikey 		= $params->get('apikey');
$ipaccess       = $params->get('ipaccess');
$ipservice      = $params->get('ipservice');
$showmap		= $params->get('showmap',1);
$showtext		= $params->get('showtext',1);
$showlatest     = $params->get('shownewest',1);
$limit 			= $params->get('limit','100');
$height 		= $params->get('height','180px');
$width 			= $params->get('width','100%');
$zoom 			= $params->get('zoom','0');
$minzoom 		= $params->get('minzoom','0');
$maxzoom 		= $params->get('maxzoom','20');
$zoomcontrol	= 1;

if($minzoom>=$maxzoom)
	$zoomcontrol = 0;

$type 			= $params->get('type','ROADMAP');
$stylez 		= $params->get('stylez');
$scrollwheel 	= $params->get('scrollwheel',0);
if ($scrollwheel) 
	$scrollwheel = 'true';
else 
	$scrollwheel = 'false';


$maplatitude 	= $params->get('maplatitude','0');
$maplongitude 	= $params->get('maplongitude','0');

$cookie_timeout	= $params->get('cookie_timeout',7200); 

$mapmarkers='';
$coordinates='';

// we've switched to days
// Make sure old installations now default to one day
if (intval($cookie_timeout) < 1 || $cookie_timeout > 30)
{ 
	// set it to one day
	$cookie_timeout = 24 * 3600;
}
else
{
	$cookie_timeout = ($cookie_timeout * 24 * 3600);
}

// default the lower limit to 10
if (intval($limit)< 10)
{ 
	$limit = 10; 
} 

// now we have our parameters set, let's process this user

// do we have a cookie? A valid cookie?
if (isset($_COOKIE['geotracker']) && $_COOKIE['geotracker'] != 0)
{
	// we have a cookie so let's update the visit counter
    $coordinates = $_COOKIE['geotracker'];
	// echo("Cookie 'geotracker says: $coordinates<br> ");
	ModGeoTrackerHelper::updateVisitCount($coordinates);
	if($showmap && $showtext)
		echo JText::_('GEOTR_VALIDCOOKIE');
}
else
{  
	if($showmap && $showtext)
		echo " ".JText::_('GEOTR_COOKIESET')." ";

    $coordinates = ModGeoTrackerHelper::ipGeoLookup($ipservice, $ipaccess);
	echo "<p>returned coordinates: $coordinates </p>";

	// see if we're already in the database with this ipaddress.
	$id = ModGeoTrackerHelper::getRecordID($coordinates);
	if($id > 0)
	{
		// it's already in the table so lets update the record

		// and renew the cookie
		setcookie("geotracker", $coordinates, time() + $cookie_timeout, "/");
	}
	else
	{
		// echo "<p>storing coordinates $coordinates</p>";
		ModGeoTrackerHelper::saveLocation($params, $coordinates); 

		// set the cookie with the coordinates
		setcookie("geotracker", $coordinates, time() + $cookie_timeout, "/");
	}

} 

// now see if we're showing the map
// if we are, we'll build out the javascript that
// we're gonna insert into the document head
if($showmap)
{
	// echo "<p>Now prepare for showing the map</p>";

	// Use this users coordinates to center the map
	$mapcoords= explode(',',$coordinates);
	if( $mapcoords[0]!='' )
		$maplatitude = $mapcoords[0];
	if( $mapcoords[1]!='' )
		$maplongitude = $mapcoords[1];
	
	// get the rest of the markers up to $limit
    $results = ModGeoTrackerHelper::getMapMarkers($limit, $showlatest);
    $i = 0;
	$mapmarkers = "var userIcon =  new google.maps.MarkerImage('".$juri."/modules/mod_geotracker/img/marker.png' , 
	 	new google.maps.Size(32,37),new google.maps.Point(0,0),new google.maps.Point(16,37) );"; 

    foreach ($results as $location)
	{
        $coords= explode(',',$location->geoLocation);
		$lat =  $coords[0];
		$lng =  $coords[1];

		$mapmarkers .="var marker".$i." = new google.maps.Marker({
						position: new google.maps.LatLng(".$lat.",".$lng."),
						map: map,
						clickable: false, 
						icon: userIcon
				});";
		$i++;
    } 

	$doc->addScript('https://maps.googleapis.com/maps/api/js?key='.$apikey);
	$mapscript = "function initialize() { 
			var mapOptions = {
				  center: new google.maps.LatLng(".$maplatitude.", ".$maplongitude."),
				  zoom: ".$zoom.",
				  minZoom: ".$minzoom.",
				  maxZoom: ".$maxzoom.",
				  scrollwheel :".$scrollwheel.",
				  mapTypeControl: 0,
				  streetViewControl: 0,
				  zoomControl: ".$zoomcontrol.",
				  mapTypeId: google.maps.MapTypeId.".$type."
			};

        	var map = new google.maps.Map(document.getElementById(\"visitorsmap\"),  mapOptions);";


	if($stylez!='')
	{
		$mapscript .="

		var stylez = ".$stylez.";
		var styledMapOptions = {
      		name: \"1\"
  		}

		var geoMapType = new google.maps.StyledMapType(
      		stylez, styledMapOptions);

		map.mapTypes.set(\"1\", geoMapType);
  		map.setMapTypeId(\"1\");";
	}

	$mapscript .= $mapmarkers."
      	}
      google.maps.event.addDomListener(window, 'load', initialize);
	  ";				

 	$doc->addScriptDeclaration( $mapscript );

	echo '<div >
	<div id="visitorsmap" style="height:'.$height.';width:'.$width.';">
	<div style="text-align:center;">
	<br /><br /><br />
	<img src="'.$juri.'modules/mod_geotracker/img/loader.gif" alt="GeoTracker" width="16" height="11" />
	</div>
	</div>
	</div>';
}
