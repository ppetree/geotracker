<?php
/**

 * GeoTracker Module 

 * @link http://github.com/ppetree/geotracker
 * @copyright Copyright (C) 2023-2024 Phil Petree
 * @license https://github.com/ppetree/geotracker/blob/main/LICENSE GNU/GPL 
 * @version 1.9.0
 * Shows your visitors on a Google Map
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Include the syndicate functions only once
require_once( dirname(__FILE__).'/helper.php' );
require( JModuleHelper::getLayoutPath( 'mod_geotracker' ) );

?>
