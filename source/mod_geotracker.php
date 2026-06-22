<?php
/**
 * GeoTracker Module 
 *
 * @link http://github.com/ppetree/geotracker
 * @copyright Copyright (C) 2023-2024 Phil Petree
 * @license https://github.com/ppetree/geotracker/blob/main/LICENSE GNU/GPL 
 * @version 1.9.0
 * Shows your visitors on a Google Map
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/helper.php';

$layout = \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_geotracker');
require $layout;

?>
