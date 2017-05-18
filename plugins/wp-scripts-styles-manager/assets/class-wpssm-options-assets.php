<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WPSSM_Options_Assets extends WPSSM_Options {	

	use Utilities;	

	const OPT_KEY = 'wpssm_enqueued_assets';

  /* Recording attributes */
	protected $header_scripts;
	protected $header_styles;

	public function __construct( $args ) {
		WPSSM_Debug::log('*** In WPSSM_Options_Assets __construct ***' );		
  	$this->hydrate_args( $args );	
		$opt_proto = array( 
									'pages'=>array(), 
									'scripts'=>array(), 
									'styles'=>array());	
		parent::__construct( self::OPT_KEY, $opt_proto );
		WPSSM_Debug::log('In WPSSM_Options_Assets __construct() $this->get() ', $this->get() );
	}
	
	
/* GETTER FUNCTIONS	
--------------------------------------------------------------------------*/	

	
	/* 	Retrieves the most up-to-date value of a field within an asset
			Whether the original value or the one after modification          
	---------------------------------------------------------------------*/
	public function get_value( $type, $handle, $field) {
		WPSSM_Debug::log('In WPSSM_Options_Assets get_value() ' . $type . ' ' . $handle . ' ' . $field);
		$value = false;
		$asset = $this->get( $type, $handle );
//		if ( $get == false ) 
//			$get=array();
		if ( $asset != false ) {
			if ( isset( $asset[$field]['mods'] )) 
				$value = $asset[$field]['mods'];
			elseif ( isset( $asset[$field] )) 
				$value = $asset[$field];
		}
		WPSSM_Debug::log('In WPSSM_Options_Assets get() after ', $get);
		return $value;
	}


//	public function get_field_value( $type, $handle, $field ) {
//		//WPSSM_Debug::log('In Field Value for ' . $field);
//		//WPSSM_Debug::log(array('Asset : ' => $asset));
//		$value = false;
//		if ( !isset( $this->opt_enqueued_assets[$type][$handle] ) ) return false ;
//		$asset = $this->opt_enqueued_assets[$type][$handle];
//		
//		if ( isset( $asset['mods'][ $field ] ) ) return $asset['mods'][ $field ];
//		elseif ( isset( $asset[ $field ] )) 
//			return $asset[ $field ];
//		else
//			return false;
//	}
	
	public function is_mod( $type, $handle, $field ) {
		$asset = parent::get( $type, $handle );
		return ( isset( $asset[ 'mods' ][ $field ] ) );
	}		
	


/* SETTING FUNCTIONS
-----------------------------------------------------------*/
	public function set_field_value( $type, $handle, $field, $value ) {
		$this->opt_enqueued_assets[$type][$handle][$field]=$value;
	}
	
	public function add_mod( $type, $handle, $field, $value ) {
		$this->opt_enqueued_assets[$type][$handle]['mods'][$field]=$value;
	}
	
	
	public function remove_mod_field( $type, $handle, $field ) {
		if ( !isset($this->opt_enqueued_assets[$type][$handle]['mods'][$field]) ) return false; 
		unset($this->opt_enqueued_assets[$type][$handle]['mods'][$field]); 
		$this->assets->update_priority( $type, $handle ); 
	}		
	
	public function reset_asset( $type, $handle ) {
		unset($this->opt_enqueued_assets[$type][$handle]['mods']); 
		$this->assets->update_priority( $type, $handle ); 	
	}

	public function reset_assets( $type ) {
		foreach ( $this->opt_enqueued_assets[$type] as $handle=>$asset ) {
			unset( $this->opt_enqueued_assets[$type][$handle]['mods'] ); 
			$this->update_priority( $type, $handle ); 
		}
	}	

	public function update_priority( $type, $handle ) {
		$location = $this->get_field_value( $type, $handle, 'location');
		
		if ( $location != 'disabled' ) {
			$minify = $this->get_field_value( $type, $handle, 'minify');
			$size = $this->get_field_value( $type, $handle, 'size');
			$score = ( $location == 'header' )?1000:0;
			//WPSSM_Debug::log(array('base after location'=>$score));
			$score += ( $size >= self::SIZE_LARGE )?500:0; 	
			$score += ( ($minify == 'no') && ( $size != 0 ))?200:0;
			//WPSSM_Debug::log(array('base after minify'=>$score));
			$score += ( $size <= self::SIZE_SMALL )?100:0; 	
			//WPSSM_Debug::log(array('base after size'=>$score));
			if ( $size >= self::SIZE_LARGE ) 
				$normalizer = self::SIZE_MAX;
			elseif ( $size <= self::SIZE_SMALL )
				$normalizer = self::SIZE_SMALL;
			else 
				$normalizer = self::SIZE_LARGE;
			//WPSSM_Debug::log(array('normalizer'=>$normalizer));
			$score += $size/$normalizer*100; 	
			//WPSSM_Debug::log(array('score'=>$score));
		}
		else 
			$score = 0;
			
		$this->set_field_value( $type, $handle, 'priority', $score);
	}		

	

/* RECORDING FUNCTIONS
-----------------------------------------------------------*/

	public function record( $in_footer ) {
		WPSSM_Debug::log('In record enqueued assets');
		global $wp_scripts;
		global $wp_styles;

		/* Select data source depending whether in header or footer */
		if ($in_footer) {
			//WPSSM_Debug::log('FOOTER record');
			//WPSSM_Debug::log(array( '$header_scripts' => $this->header_scripts ));
			$scripts=array_diff( $wp_scripts->done, $this->header_scripts );
			$styles=array_diff( $wp_styles->done, $this->header_styles );
			//WPSSM_Debug::log(array('$source'=>$source));
		}
		else {
			$this->opt_enqueued_assets['pages'][get_permalink()] = array(get_permalink(), current_time( 'mysql' ));
			$scripts=$wp_scripts->done;
			$styles=$wp_styles->done;
			$this->header_scripts = $scripts;
			$this->header_styles = $styles;
			//WPSSM_Debug::log('HEADER record');
			//WPSSM_Debug::log(array('$source'=>$source));
		}
	  //WPSSM_Debug::log(array('assets before update' => $this->opt_enqueued_assets));
				
		$assets = array(
			'scripts'=>array(
					'handles'=>$scripts,
					'registered'=> $wp_scripts->registered),
			'styles'=>array(
					'handles'=>$styles,
					'registered'=> $wp_styles->registered),
			);
				
		WPSSM_Debug::log( array( '$assets' => $assets ) );		
			
		foreach( $assets as $type=>$asset ) {
			WPSSM_Debug::log( $type . ' recording');		
					
			foreach( $asset['handles'] as $index => $handle ) {
				$obj = $asset['registered'][$handle];
				$path = strtok($obj->src, '?'); // remove any query parameters
				
				if ( strpos( $path, 'wp-' ) != false) {
					$path = wp_make_link_relative( $path );
					$uri = $_SERVER['DOCUMENT_ROOT'] . $path;
					$size = filesize( $uri );
					$version = $obj->ver;
				}
				else {
					$path = $obj->src;
					$version = $obj->ver;
					$size = 0;
				}
				
				// Update current asset properties
				$this->opt_enqueued_assets[$type][$handle] = array(
					'handle' => $handle,
					'enqueue_index' => $index,
					'filename' => $path,
					'location' => $in_footer?'footer':'header',
					'dependencies' => $obj->deps,
					'dependents' => array(),
					'minify' => (strpos( $obj->src, '.min.' ) != false )?'yes':'no',
					'size' => $size,
					'version' => $version,
				);
				// Update current asset priority
				$priority = $this->assets->update_priority( $type, $handle );
				// Update all dependancies assets properties
				foreach ($obj->deps as $dep_handle) {
					//WPSSM_Debug::log(array('dependencies loop : '=>$dep_handle));
					$this->opt_enqueued_assets[$type][$dep_handle]['dependents'][]=$handle;
				}
			}
		}
	  WPSSM_Debug::log(array('assets after update' => $this->opt_enqueued_assets));
	  if ( $in_footer )	hydrate_option( self::OPT_ASSETS , $this->opt_enqueued_assets, true );
	}


}
