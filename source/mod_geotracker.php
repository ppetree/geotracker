<?php
/**

 * GeoTracker Module - Shows your sites visitors on a Google Map
 *
 * @link http://github.com/ppetree/geotracker
 * @copyright Copyright (C) 2023 Phil Petree
 * @license https://github.com/ppetree/geotracker/blob/main/LICENSE GNU/GPL 
 * @version 1.8.85
 *
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 

// Include the syndicate functions only once
require_once( dirname(__FILE__).'/helper.php' );
require( JModuleHelper::getLayoutPath( 'mod_geotracker' ) );

?>
