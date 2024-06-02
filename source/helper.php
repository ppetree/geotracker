<?php
/**

 * Helper class for GeoTracker Visitors Map module
 * GeoTracker Module 

 * @link http://github.com/ppetree/geotracker
 * @copyright Copyright (C) 2023-2024 Phil Petree
 * @license https://github.com/ppetree/geotracker/blob/main/LICENSE GNU/GPL 
 * @version 1.9.0
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
			$url = "https://api.ip2location.io/?key=$access_key&ip=$ip&format=json";
		}
		else
		{
			// no service chosen so do nothing
			return;
		}

		// make the call, get the data
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
			// try it the old fashioned way
			$timeout = ini_set('default_socket_timeout', 30);
			$fp = @fopen($url, 'r');
			$content = @fread($fp, 4096);
			@fclose($fp);
		}
		// decode the response
		$json = json_decode($content);

		// we're only saving the lat/lon so just snag that
		$latitude = $json->latitude;
		$longitude = $json->longitude;

		if($latitude && $longitude)
		{
			// combine them into a string for storage
			$result = "$latitude,$longitude";
			// var_dump("ip2location results: " .$result);
			return $result; 
		}
		else
		{
			// use for debugging only
                        // var_dump("ip2location has no results for " .$ip);
			return "";
		}
	}

	// see if we already have a record for this address
	static function getRecordID($coordinates)
	{
		// echo "<p>getting DBO</p>";
		$db = JFactory::getDBO();

		echo "<p>testing to see if it's already in the DB</p>";
		$q ="SELECT COUNT(id) 
		FROM #__geotracker_visitors 
		WHERE geoLocation='".$coordinates."' ";
		$db->setQuery($q );
		$isstored = $db->loadResult();

		if(!$isstored)
		{
			echo "<p>Not in DB</p>";
			return(0);
		}
		return($isstored);	// caller gets the rowid
	}

	// this function adds the lat/lon to the database
	static function saveLocation($params, $coordinates)
	{
		$showmap  = $params->get('showmap',1);
		$showtext = $params->get('showtext',1);

		// echo "<p>getting DBO</p>";
		$db = JFactory::getDBO();

		if ( strlen($coordinates)<3  ) { return; } 

		$q = "INSERT INTO #__geotracker_visitors (`num_visits`,`geoLocation`) 
		VALUES ('1', '$coordinates')"; 
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

	// this function updates the number of times a visitor has visited the site
	// if the cookie is set this will be called. If the cookie has expired, this will be called when we have new info
	static function updateVisitCount($coordinates)
	{
		// cookie is set so we can update num_visits
		if ( strlen($coordinates) <3  ) { return; }

		$db = JFactory::getDBO();
		$q = "UPDATE #__geotracker_visitors SET num_visits=num_visits+1
		WHERE geoLocation='$coordinates'"; 
		// echo("setting query as: $q");
		$db->setQuery($q); 

		// echo "<p>Executing query</p>";
		if (!$db->execute())
		{ 
			// echo "<p>DBError: " .$db->getErrorMsg() ."</p>"; 
			return(FALSE);
		}
		return(TRUE);
	}

	/* this gets all the latest visitors to be used as lat/lon map markers */
	/* we should probably add date/time and referrer so we can do map popups */
	static function getMapMarkers($limit, $showlatest)
	{
		if($showlatest == 1)
		{
			// get '$limit' number of most recent (new and returning) visitors
			$db = JFactory::getDBO();
			$q = "SELECT geoLocation
			FROM #__geotracker_visitors
			ORDER BY last_visit DESC LIMIT ".$limit; 
			$db->setQuery($q); 
			$results = $db->loadObjectList(); 	
			return $results;
		}
		else
		{
			// get '$limit' number of newest unique visitors
			$db = JFactory::getDBO();
			$q = "SELECT DISTINCT geoLocation
			FROM #__geotracker_visitors ORDER BY id DESC LIMIT ".$limit; 
			$db->setQuery($q); 
			$results = $db->loadObjectList(); 	
			return $results;
		}
	}
} 
?>
