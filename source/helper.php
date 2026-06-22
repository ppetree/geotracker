<?php
/**
 * Helper class for GeoTracker Visitors Map module
 * GeoTracker Module 
 *
 * @link http://github.com/ppetree/geotracker
 * @copyright Copyright (C) 2023-2024 Phil Petree
 * @license https://github.com/ppetree/geotracker/blob/main/LICENSE GNU/GPL 
 * @version 1.9.0
 * Shows your most recent visitors on a Google Map
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

class ModGeoTrackerHelper
{
    static function ipGeoLookup($ipService, $access_key)
    {
        $app = Factory::getApplication();
        $ip = $app->input->server->getString('REMOTE_ADDR', '');
        $url = '';

        if ($ipService == 'IPAPI') {
            $url = "http://api.ipapi.com/$ip/?access_key=$access_key";
        } elseif ($ipService == 'IP2LOC') {
            $url = "https://api.ip2location.io/?key=$access_key&ip=$ip&format=json";
        } else {
            // no service chosen so do nothing
            return '';
        }

        $content = '';

        // make the call, get the data
        if (function_exists('curl_init')) {
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $url);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
            $content = trim(curl_exec($c));
            curl_close($c);
        } elseif (ini_get('allow_url_fopen')) {
            // try it the old fashioned way
            $timeout = ini_set('default_socket_timeout', 30);
            $fp = @fopen($url, 'r');
            if ($fp) {
                $content = @stream_get_contents($fp);
                @fclose($fp);
            }
        }

        if (empty($content)) {
            return '';
        }

        // decode the response
        $json = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::add('GeoTracker: JSON decode error for IP lookup - ' . json_last_error_msg(), Log::ERROR, 'mod_geotracker');
            return '';
        }

        // validate properties
        $latitude = isset($json->latitude) ? $json->latitude : (isset($json->lat) ? $json->lat : null);
        $longitude = isset($json->longitude) ? $json->longitude : (isset($json->lon) ? $json->lon : (isset($json->lng) ? $json->lng : null));

        if ($latitude === null || $longitude === null) {
            return '';
        }

        $latitude = trim((string) $latitude);
        $longitude = trim((string) $longitude);

        if ($latitude !== '' && $longitude !== '') {
            // combine them into a string for storage
            $result = $latitude . ',' . $longitude;
            return $result;
        }

        return '';
    }

    // see if we already have a record for this address
    static function getRecordID($coordinates)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__geotracker_visitors'))
            ->where($db->quoteName('geoLocation') . ' = ' . $db->quote($coordinates));

        $db->setQuery($query);
        $isstored = (int) $db->loadResult();

        if (!$isstored) {
            return 0;
        }

        return $isstored; // caller gets the count
    }

    // this function adds the lat/lon to the database
    static function saveLocation($params, $coordinates)
    {
        $showmap  = $params->get('showmap', 1);
        $showtext = $params->get('showtext', 1);

        $db = Factory::getDbo();

        if (strlen($coordinates) < 3) {
            return false;
        }

        try {
            $query = $db->getQuery(true);
            $columns = [$db->quoteName('num_visits'), $db->quoteName('geoLocation')];
            $values  = [(int) 1, $db->quote($coordinates)];

            $query->insert($db->quoteName('#__geotracker_visitors'))
                ->columns($columns)
                ->values(implode(',', $values));

            $db->setQuery($query);
            $db->execute();

            // Do not echo from helpers; return true and let caller decide display
            return true;
        } catch (\Exception $e) {
            Log::add('GeoTracker: DB insert error - ' . $e->getMessage(), Log::ERROR, 'mod_geotracker');
            return false;
        }
    }

    // this function updates the number of times a visitor has visited the site
    static function updateVisitCount($coordinates)
    {
        if (strlen($coordinates) < 3) {
            return false;
        }

        $db = Factory::getDbo();

        try {
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__geotracker_visitors'))
                ->set($db->quoteName('num_visits') . ' = ' . $db->quoteName('num_visits') . ' + 1')
                ->where($db->quoteName('geoLocation') . ' = ' . $db->quote($coordinates));

            $db->setQuery($query);
            $db->execute();
            return true;
        } catch (\Exception $e) {
            Log::add('GeoTracker: DB update error - ' . $e->getMessage(), Log::ERROR, 'mod_geotracker');
            return false;
        }
    }

    /* this gets all the latest visitors to be used as lat/lon map markers */
    static function getMapMarkers($limit, $showlatest)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)->select($db->quoteName('geoLocation'))->from($db->quoteName('#__geotracker_visitors'));

        if ($showlatest == 1) {
            $query->order($db->quoteName('last_visit') . ' DESC');
        } else {
            $query->distinct()->order($db->quoteName('id') . ' DESC');
        }

        $db->setQuery($query, 0, (int) $limit);
        $results = $db->loadObjectList();

        return $results;
    }
}
