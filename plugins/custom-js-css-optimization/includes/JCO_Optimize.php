<?php 

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class JCO_Optimize extends JCO_Settings {
	
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array($this, 'conditionally_deregister_scripts'), PHP_INT_MAX );
	}


	public function conditionally_deregister_scripts() {
		
		if ( !is_front_page() ) {
			wp_dequeue_script( 'easingslider' );
		}
		
		if ( !is_single() ) {
			//PC::debug(array('Not in POST OR RECIPE'));
			wp_dequeue_script( 'galleria' );
			wp_dequeue_script( 'galleria-fs' );
			wp_dequeue_script( 'galleria-fs-theme' );
		}
		
		wp_dequeue_script( 'cnss_js' );
		//wp_enqueue_script( 'cnss_js', PLUGINS_URL . '/easy-social-icons/js/cnss.js' , true );


		//wp_dequeue_script( 'jquery-ui-sortable' );
		//wp_dequeue_script( 'bp-confirm' );
		wp_deregister_script( 'bp-legacy-js' );
		wp_register_script( 'bp-legacy-js', 
			PLUGINS_URL . '/buddypress/bp-templates/bp-legacy/js/buddypress.min.js',
			array(),
			false,
			true );
		wp_enqueue_script( 'bp-legacy-js' );
	}


}
