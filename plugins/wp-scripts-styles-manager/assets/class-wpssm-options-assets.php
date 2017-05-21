<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Asset structure array(
											'handle' 
											'enqueue_index' 
											'filename' 
											'location' 
											'minify' 
											'dependents' 
											'dependencies' 
											'size' 
											'version' 
											'priority' )
-----------------------------------------------------------*/
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
	public function get_field( $type, $handle, $field) {
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
	
	public function get_mod( $type, $handle, $field) {
		$value = false;
		$asset = $this->get( $type, $handle );
		if ( $asset != false ) {
			if ( isset( $asset[$field]['mods'] )) {
				$value = $asset[$field]['mods'];
			}
		}
		return $value;
	}	
		

	public function is_mod( $type, $handle, $field ) {
		$asset = parent::get( $type, $handle );
		return ( isset( $asset[ 'mods' ][ $field ] ) );
	}		
	

/* SETTING FUNCTIONS
-----------------------------------------------------------*/

/* Asset 
-----------------------------------------------------------*/
	public function add( $type, $handle, $obj, $location ) {
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
		$args = array(
			'handle' => $handle,
			'enqueue_index' => $index,
			'filename' => $path,
			'location' => $location,
			'dependencies' => $obj->deps,
			'dependents' => array(),
			'minify' => (strpos( $obj->src, '.min.' ) != false )?'yes':'no',
			'size' => $size,
			'version' => $version,
		);						
		parent::set($args, $type, $handle);
		$this->update_priority( $type, $handle);
		$this->update_dependants( $type, $handle);
	}

	public function update_from_post( $type, $handle, $field ) {
		$is_mod=false;
		$val='';
		$input = $this->get_field_name($type, $handle, $field);
		if ( ( isset($_POST[ $input ] ) ) && ( $_POST[ $input ] != $this->get($type,$handle,$field) ) ) {
			WPSSM_Debug::log( 'Asset field modified (mods) !' , $this->get($type,$handle) );
			//WPSSM_Debug::log( 'input name', $input );
			//WPSSM_Debug::log( 'POST content for this field',$_POST[ $input ] );
			$val = esc_html($_POST[ $input ]);
			$this->set_mod_field($type,$handle,$field,$val);
			$is_mod=true;
		}
		else {
			$this->assets->unset_mod( $type, $handle, $field );
			WPSSM_Debug::log( 'Mod Field removed !' , $this->assets->get($type,$handle) );
		}
		return array($is_mod, $val);
	}
	
	
/* Asset field
-----------------------------------------------------------*/
	public function set_field( $type, $handle, $field, $value ) {
		$this->opt_enqueued_assets[$type][$handle][$field]=$value;
	}
	
	public function add_field_value( $type, $handle, $field, $value ) {
		$this->opt_enqueued_assets[$type][$handle][$field][]=$value;
	}
	
/* Asset Mod
-----------------------------------------------------------*/
	public function set_mod_field( $type, $handle, $field, $value ) {
		$this->opt_enqueued_assets[$type][$handle]['mods'][$field]=$value;
	}

	public function unset_mod( $type, $handle=false, $field=false ) {
		if ( $handle == false ) {
			foreach ( $this->get($type) as $handle=>$asset ) {
				$this->unset_mod( $type, $handle); 
			}
		}	
		elseif ( $field == false )
			if ( isset( $this->opt_enqueued_assets[$type][$handle]['mods'] ) )
				unset($this->opt_enqueued_assets[$type][$handle]['mods']); 
		else
			if ( isset( $this->opt_enqueued_assets[$type][$handle]['mods'][$field] ) )
				unset($this->opt_enqueued_assets[$type][$handle]['mods'][$field]); 
		$this->assets->update_priority( $type, $handle ); 
	}		
	

/* ASSET FIELDS UPDATE FUNCTIONS
-----------------------------------------------------------*/	

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
	
	// Update all assets 'dependants' property, based on this asset's dependencies
	public function update_dependants( $type, $handle ) {
		$dependencies = $this->get_field( $type, $handle, 'dependencies');
		foreach ($dependencies as $dep_handle) {
			//WPSSM_Debug::log(array('dependencies loop : '=>$dep_handle));
			$this->add_field_value( $type, $dep_handle, 'dependents', $handle );
		}	
	}
	


}

