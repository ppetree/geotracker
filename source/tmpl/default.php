<?php
/**
 * GeoTracker Module For Map Display or ip to lat/lon conversion
 *
 * @link http://github.com/ppetree/geotracker.com
 * @copyright Copyright (C) 2023-2024 Phil Petree
 * @license https://github.com/ppetree/geotracker/blob/main/LICENSE GNU/GPL 
 * @version 1.9.0
 * Shows your visitors on a Google Map
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$doc     = Factory::getDocument();
$wa      = $doc->getWebAssetManager();
$juri    = Uri::base();
$apikey  = $params->get('apikey');
$ipaccess = $params->get('ipaccess');
$ipservice = $params->get('ipservice');
$showmap = $params->get('showmap', 1);
$showtext = $params->get('showtext', 1);
$showlatest = $params->get('shownewest', 1);
$limit = $params->get('limit', '100');
$height = $params->get('height', '180px');
$width = $params->get('width', '100%');
$zoom = $params->get('zoom', '0');
$minzoom = $params->get('minzoom', '0');
$maxzoom = $params->get('maxzoom', '20');
$zoomcontrol = 1;

if ($minzoom >= $maxzoom) {
    $zoomcontrol = 0;
}

$type = $params->get('type', 'ROADMAP');
$stylez = $params->get('stylez');
$scrollwheel = $params->get('scrollwheel', 0);
$scrollwheel = $scrollwheel ? 'true' : 'false';

$maplatitude = $params->get('maplatitude', '0');
$maplongitude = $params->get('maplongitude', '0');

$cookie_timeout = $params->get('cookie_timeout', 7200);

$mapmarkers = '';
$coordinates = '';

// we've switched to days
// Make sure old installations now default to one day
if (intval($cookie_timeout) < 1 || $cookie_timeout > 30) {
    // set it to one day
    $cookie_timeout = 24 * 3600;
} else {
    $cookie_timeout = ($cookie_timeout * 24 * 3600);
}

// default the lower limit to 10
if (intval($limit) < 10) {
    $limit = 10;
}

// do we have a cookie? A valid cookie?
if (isset($_COOKIE['geotracker']) && $_COOKIE['geotracker'] != 0) {
    $coordinates = $_COOKIE['geotracker'];
    ModGeoTrackerHelper::updateVisitCount($coordinates);
    if ($showmap && $showtext) {
        echo Text::_('GEOTR_VALIDCOOKIE');
    }
} else {
    if ($showmap && $showtext) {
        echo ' ' . Text::_('GEOTR_COOKIESET') . ' ';
    }

    $coordinates = ModGeoTrackerHelper::ipGeoLookup($ipservice, $ipaccess);

    // see if we're already in the database with this ipaddress.
    $id = ModGeoTrackerHelper::getRecordID($coordinates);
    if ($id > 0) {
        // and renew the cookie (use secure cookie options)
        setcookie('geotracker', $coordinates, ['expires' => time() + $cookie_timeout, 'path' => '/', 'samesite' => 'Lax']);
    } else {
        ModGeoTrackerHelper::saveLocation($params, $coordinates);
        setcookie('geotracker', $coordinates, ['expires' => time() + $cookie_timeout, 'path' => '/', 'samesite' => 'Lax']);
    }
}

if ($showmap) {
    $mapcoords = explode(',', $coordinates);
    if (!empty($mapcoords[0])) {
        $maplatitude = $mapcoords[0];
    }
    if (!empty($mapcoords[1])) {
        $maplongitude = $mapcoords[1];
    }

    $results = ModGeoTrackerHelper::getMapMarkers($limit, $showlatest);
    $i = 0;

    $mapmarkers = "var userIcon =  new google.maps.MarkerImage('" . $juri . "/modules/mod_geotracker/img/marker.png' , \n    \tnew google.maps.Size(32,37),new google.maps.Point(0,0),new google.maps.Point(16,37) );";

    foreach ($results as $location) {
        $coords = explode(',', $location->geoLocation);
        $lat = $coords[0];
        $lng = $coords[1];

        $mapmarkers .= "var marker" . $i . " = new google.maps.Marker({\n                        position: new google.maps.LatLng(" . $lat . "," . $lng . "),\n                        map: map,\n                        clickable: false, \n                        icon: userIcon\n                });";
        $i++;
    }

    // Use WebAssetManager for external scripts and inline script
    if (!empty($apikey)) {
        $wa->registerAndUseScript('mod_geotracker.googlemaps', 'https://maps.googleapis.com/maps/api/js?key=' . $apikey);
    } else {
        $wa->registerAndUseScript('mod_geotracker.googlemaps', 'https://maps.googleapis.com/maps/api/js');
    }

    $mapscript = "function initialize() { \n\t\tvar mapOptions = {\n\t\t\t  center: new google.maps.LatLng(" . $maplatitude . ", " . $maplongitude . "),\n\t\t\t  zoom: " . $zoom . ",\n\t\t\t  minZoom: " . $minzoom . ",\n\t\t\t  maxZoom: " . $maxzoom . ",\n\t\t\t  scrollwheel :" . $scrollwheel . ",\n\t\t\t  mapTypeControl: 0,\n\t\t\t  streetViewControl: 0,\n\t\t\t  zoomControl: " . $zoomcontrol . ",\n\t\t\t  mapTypeId: google.maps.MapTypeId." . $type . "\n\t\t};\n\n\t \tvar map = new google.maps.Map(document.getElementById(\"visitorsmap\"),  mapOptions);";

    if ($stylez != '') {
        $mapscript .= "\n\n\t\tvar stylez = " . $stylez . ";\n\t\tvar styledMapOptions = {\n\t  \t\tname: \"1\"\n\t\t}\n\n\t\tvar geoMapType = new google.maps.StyledMapType(\n\t\t   \tstylez, styledMapOptions);\n\n\t\tmap.mapTypes.set(\"1\", geoMapType);\n\t\tmap.setMapTypeId(\"1\");";
    }

    $mapscript .= "\n\n" . $mapmarkers . "\n\t} \n\tgoogle.maps.event.addDomListener(window, 'load', initialize);";

    $wa->addInlineScript($mapscript);

    echo '<div >\n\t<div id="visitorsmap" style="height:' . htmlspecialchars($height, ENT_QUOTES, 'UTF-8') . ';width:' . htmlspecialchars($width, ENT_QUOTES, 'UTF-8') . ';">\n\t<div style="text-align:center;">\n\t<br /><br /><br />\n\t<img src="' . $juri . 'modules/mod_geotracker/img/loader.gif" alt="GeoTracker" width="16" height="11" />\n\t</div>\n\t</div>\n\t</div>';
}
