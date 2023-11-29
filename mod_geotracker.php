<?php
/**

 * GeoTracker Module 

 * @link http://github.com/ppetree/geotracker
 * @copyright Copyright (C) 2023 Phil Petree
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL 
 * Shows your visitors on a Google Map
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 

// Include the syndicate functions only once
require_once( dirname(__FILE__).'/helper.php' );
require( JModuleHelper::getLayoutPath( 'mod_geotracker' ) );

?>
