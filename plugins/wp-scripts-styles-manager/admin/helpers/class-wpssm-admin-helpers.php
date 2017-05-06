<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WPSSM_Admin_Helpers {

	
	protected function get_field_name( $type, $handle, $field ) {
		return  $type . '_' . $handle . '_' . $field;
	}
	
	protected function get_field_value( $asset, $field ) {
		//WPSSM_Debug::log('In Field Value for ' . $field);
		//WPSSM_Debug::log(array('Asset : ' => $asset));
		if ( isset( $asset['mods'] ) && (isset( $asset['mods'][ $field ] ) ) ) {
			$value=$asset['mods'][ $field ];
			//WPSSM_Debug::log('Mod found !');
		}
		else {
			//WPSSM_Debug::log('Mod not found');
			$value=$asset[ $field ];
		}
		//WPSSM_Debug::log( array(' Field value of ' . $field . ' : ' => $value ));
		return $value;
	}

	protected function get_field_value( $asset, $field ) {
		//WPSSM_Debug::log('In Field Value for ' . $field);
		//WPSSM_Debug::log(array('Asset : ' => $asset));
		if ( isset( $asset['mods'] ) && (isset( $asset['mods'][ $field ] ) ) ) {
			$value=$asset['mods'][ $field ];
			//WPSSM_Debug::log('Mod found !');
		}
		else {
			//WPSSM_Debug::log('Mod not found');
			$value=$asset[ $field ];
		}
		//WPSSM_Debug::log( array(' Field value of ' . $field . ' : ' => $value ));
		return $value;
	}
	
		
	protected function is_modified( $asset, $field ) {
		if ( isset( $asset['mods'][ $field ] ) ) {
			return 'modified';
		}
	}


}

