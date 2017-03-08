<?php
/*
Plugin Name: Custom Navigation Helpers
Plugin URI: http://goutu.org/
Description: Custom shortcodes & widgets for site navigation purposes
Version: 1.0
Author: Pascal Olive
Author URI: http://goutu.org
License: GPL
*/


// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');
	
	
	
/* Main
------------------------------------------------------------*/
require_once 'shortcodes/taxonomies-list-shortcode.php';
require_once 'shortcodes/index-link-shortcode.php';
require_once 'shortcodes/misc-shortcodes.php';

require_once 'widgets/dropdown-posts-sort-widget.php';

