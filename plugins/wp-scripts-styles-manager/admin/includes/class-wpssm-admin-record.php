<?php

class WPSSM_Admin_Record {
	/* Class triggerred independantly from WPSSM_Public even if it runs in frontend */

	use Utilities;

 	private $type;

 	/* Class parameters */
 	private $plugin_name;
 	
 	/* Objects */
 	private $assets;
 	 	
  public function __construct( $args ) {
		WPSSM_Debug::log('*** In WPSSM_Admin_Record __construct ***' );		  	
  	$this->hydrate_args( $args );	
		$this->assets = new WPSSM_Options_Assets( array(	'plugin_name' => $this->plugin_name ));
  }
  


/* RECORDING CALLBACKS 
----------------------------------------------------*/
	public function record_header_assets_cb() {
		WPSSM_Debug::log('In record header assets cb');
		$this->assets->record( false );
	}

	public function record_footer_assets_cb() {
		WPSSM_Debug::log('In record footer assets cb');
		$this->assets->record( true );
	}
	
	
  

	
	
}