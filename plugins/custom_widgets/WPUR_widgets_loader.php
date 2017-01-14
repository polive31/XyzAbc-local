<?php
/*
Plugin Name: WPUR Custom Widgets
Plugin URI: http://goutu.org
Description: Provides additional recipe widgets based on WP Ultimate Recipe  
Author: Pascal Olive 
Version: 1.0
Author URI: http://goutu.org
*/

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

	
add_action( 'plugins_loaded' , 'load_wpur_widgets_plugin' );

function load_wpur_widgets_plugin() {
	
	if ( class_exists( 'WPUltimateRecipe' ) ) {
		require( dirname( __FILE__ ) . '/WPUR_custom_recipe_list.php' );
		require( dirname( __FILE__ ) . '/WPUR_custom_nutrition_label.php' );
	} 
	else {
		add_action( 'admin_notices', 'wpur_widgets_install_notice' );
	}

	function wpur_widgets_install_notice() {
		echo '<div id="message" class="error fade"><p style="line-height: 150%">';
		_e('<strong>WPUR Widgets Plugin</strong></a> requires the WP Ultimate Recipe plugin to work. Please <a href="plugins.php">activate/install WPUR or deactivate this plugin </a>.');
		echo '</p></div>';
	}	
	
}

?>
