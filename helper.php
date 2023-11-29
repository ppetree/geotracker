<?php

/**

 * Helper class for GeoTracker Visitors Map module
 * GeoTracker Module 

 * @link http://github.com/ppetree/geotracker.com
 * @copyright Copyright (C) 2023 Phil Petree
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL 
 * Shows your most recent visitors on a Google Map
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

class ModGeoTrackerHelper
{
	static function ipGeoLookup($ipService, $access_key)
	{ 
		$ip = $_SERVER['REMOTE_ADDR'];
		$url = "";
		if($ipService == "IPAPI")
		{	
		   $url = "http://api.ipapi.com/$ip/?access_key=$access_key";
		}
		elseif($ipService == "IP2LOC")
		{
			$url = "https://api.ip2location.io/?key=$access_key&$ip";
		}
		else
		{
			// know service chosen so do nothing
			return;
		}


		if (function_exists('curl_init'))
		{

				$c = curl_init();
				curl_setopt($c, CURLOPT_URL, $url);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
				$content = trim(curl_exec($c));
				curl_close($c);
		}

		elseif (ini_get('allow_url_fopen'))
		{
			$timeout = ini_set('default_socket_timeout', 30);
			$fp = @fopen($url, 'r');
			$content = @fread($fp, 4096);
			@fclose($fp);
		}

		$json = json_decode($content);
		// var_dump($content);
		// var_dump($json);

		$latitude = $json->latitude;
		$longitude = $json->longitude;

		if($latitude && $longitude)
		{
			$result = "$latitude,$longitude";
			// var_dump("ip2location results " .$result);
			return $result; 
		}
		else
		{
			// use for debugging only
		    // var_dump("ip2location has no results for " .$ip);
		}
	} 

	/* this function adds the lat/lon to the database */
	static function saveLocation($params,$coordinates)
	{ 
		$showmap  = $params->get('showmap',1);
		$showtext = $params->get('showtext',1);

		// echo "<p>getting DBO</p>";
		$db = JFactory::getDBO();

		$q ="SELECT COUNT(geoLocation) 
		FROM #__geotracker_visitors 
		WHERE geoLocation='".$coordinates."' ";
		$db->setQuery($q );
		$isstored = $db->loadResult();

		// echo "<p>testing to see if it's already in the DB</p>";
		if(!$isstored)
		{
			// echo "<p>Not in DB so store it</p>";
			if ( strlen($coordinates)<3  ) { return; } 

			$q = "INSERT INTO #__geotracker_visitors (`geoLocation`) 
			VALUES ('" .$coordinates ."')"; 
			// echo "<p>setting query as: $q</p>";
			$db->setQuery($q); 

			// echo "<p>Executing query</p>";
			if (!$db->execute())
			{ 
				// echo "<p>DBError: " .$db->getErrorMsg() ."</p>"; 
				return; 
			} 
			if($showmap && $showtext) echo JText::_('GEOTR_YOUADDED');
		}
		else
		{
			// echo "<p>lat/lon is already stored</p>";
			if($showmap && $showtext) echo JText::_('GEOTR_YOURLOCATION');
		}
	} 

	/* this gets all the latest visitors to be used as lat/lon map markers */
	/* we should probably add date/time and referrer so we can do map popups */
	static function getMapMarkers($limit)
	{
		$db = JFactory::getDBO();
		$q = "SELECT DISTINCT geoLocation 
		FROM #__geotracker_visitors ORDER BY id DESC LIMIT ".$limit; 
		$db->setQuery($q); 
		$results = $db->loadObjectList(); 	
		return $results;
	}
} 
?>