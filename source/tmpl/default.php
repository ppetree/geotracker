<?php

/**

 * GeoTracker Module For Map Display or ip to lat/lon conversion

 * GeoTracker Module 

 * @link http://github.com/ppetree/geotracker.com
 * @copyright Copyright (C) 2023 Phil Petree
 * @license https://github.com/ppetree/geotracker/blob/main/LICENSE GNU/GPL 
 * @version 1.8.85
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

// we're gonna override some user settings that we know don't work
// kinda dumb since we should just not allow those settings but hey!
if (intval($cookie_timeout)< 3600)
{ 
	$cookie_timeout = 3600;
} 

if (intval($limit)< 10)
{ 
	$limit = 10; 
} 

if (isset($_COOKIE['geotracker']))
{
	if($showmap && $showtext)
		echo JText::_('GEOTR_VALIDCOOKIE'); 
}
else
{  
    setcookie ("geotracker" ,0, time() + $cookie_timeout ,"/");
	if($showmap && $showtext)
		echo " ".JText::_('GEOTR_COOKIESET')." "; 

    $coordinates = ModGeoTrackerHelper::ipGeoLookup($ipservice, $ipaccess);
	// echo "<p>returned coordinates: $coordinates </p>";

	// echo "<p>storing coordinates $coordinates</p>";
    ModGeoTrackerHelper::saveLocation($params, $coordinates); 

	// echo "<p>Now prepare for showing the map</p>";
	$mapcoords= explode(',',$coordinates);
	if( $mapcoords[0]!='' )
		$maplatitude =  $mapcoords[0];
	if( $mapcoords[1]!='' )
		$maplongitude =  $mapcoords[1];
} 

// now see if we're showing the map
// if we are, we'll build out the javascript that
// we're gonna insert into the document head
if($showmap)
{
    $results = ModGeoTrackerHelper::getMapMarkers($limit); 
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
