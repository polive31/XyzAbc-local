<?php 

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WPSSM_Optimize {
	
	public $opt_general_settings;
	public $opt_enqueued_assets;
	
	public function __construct() {
		$this->hydrate();
	
		//echo '<pre> <p>opt_general_settings in construct : </p><p>' . var_dump($this->opt_general_settings['record']) . '</p></pre>';
		if ( ($this->opt_general_settings['record']=='off') && ($this->opt_general_settings['optimize']=='on') ) {
			//echo '<pre> OPTIMIZE ACTIVATED ! </pre>';
			add_action( 'wp_enqueue_scripts', array($this, 'apply_scripts_mods_cb'), PHP_INT_MAX );
			add_action( 'wp_enqueue_scripts', array($this, 'apply_styles_mods_cb'), PHP_INT_MAX );
		}
	}
	
	public function hydrate() {
		// hydrate general settings property with options content
		$this->opt_general_settings = get_option( 'wpssm_general_settings' );
		echo '<pre> opt_general_settings ' . var_dump($this->opt_general_settings) . '</pre>';

		// hydrate enqueued assets property with options content
		$this->opt_enqueued_assets = get_option( 'wpssm_enqueued_assets' );
		if (!isset($this->opt_enqueued_assets['scripts'])) $this->opt_enqueued_assets['scripts']=array();
		if (!isset($this->opt_enqueued_assets['styles'])) $this->opt_enqueued_assets['styles']=array();
		//echo '<pre> opt_enqueued_assets ' . var_dump($this->opt_enqueued_assets) . '</pre>';
	}

	public function apply_scripts_mods_cb() {
		echo '<pre> IN apply_scripts_mods_cb ! </pre>';
		$scripts = $this->opt_enqueued_assets['scripts'];
		//PC::debug(array('In apply_scripts_mods_cb : scripts '=>$scripts));
		$mods = array_column($scripts, 'mods', 'handle');
		//PC::debug(array('In apply_scripts_mods_cb : mods '=>$mods));
		
		foreach ($mods as $handle => $mod) {
			if ( isset( $mod['location'] ) ) {
				$location = $mod['location'];
				wp_deregister_script( $handle );
				if ($location != 'disabled' ) {
					wp_register_script( $handle, 
						$scripts[$handle]['filename'],
						$scripts[$handle]['dependencies'],
						$scripts[$handle]['version'],
						($location=='footer')?true:false );				
					wp_enqueue_script( $handle );
				}
			}
		}
	}
	
	public function apply_styles_mods_cb() {
		echo '<pre> IN apply_styles_mods_cb ! </pre>';
		$styles = $this->opt_enqueued_assets['styles'];
		PC::debug(array('In apply_styles_mods_cb : styles '=>$styles));
		$mods = array_column($styles, 'mods', 'handle');
		PC::debug(array('In apply_styles_mods_cb : mods '=>$mods));
		
		foreach ($mods as $handle => $mod) {
			if ( isset( $mod['location'] ) ) {
				$location = $mod['location'];
				wp_deregister_style( $handle );
				if ($location != 'disabled' ) {
					wp_register_style( $handle, 
						$styles[$handle]['filename'],
						$styles[$handle]['dependencies'],
						$styles[$handle]['version'],
						($location=='footer')?true:false );				
					wp_enqueue_style( $handle );
				}
			}
		}
	}
	
	
	
	
	
		
//		if ( !is_front_page() ) {
//			wp_dequeue_script( 'easingslider' );
//		}
//		
//		if ( !is_single() ) {
//			//PC::debug(array('Not in POST OR RECIPE'));
//			wp_dequeue_script( 'galleria' );
//			wp_dequeue_script( 'galleria-fs' );
//			wp_dequeue_script( 'galleria-fs-theme' );
//		}
//		
//		wp_dequeue_script( 'cnss_js' );
//		//wp_enqueue_script( 'cnss_js', PLUGINS_URL . '/easy-social-icons/js/cnss.js' , true );
//
//
//		//wp_dequeue_script( 'jquery-ui-sortable' );
//		//wp_dequeue_script( 'bp-confirm' );
//		wp_deregister_script( 'bp-legacy-js' );
//		wp_register_script( 'bp-legacy-js', 
//			PLUGINS_URL . '/buddypress/bp-templates/bp-legacy/js/buddypress.min.js',
//			array(),
//			false,
//			true );
//		wp_enqueue_script( 'bp-legacy-js' );


}

