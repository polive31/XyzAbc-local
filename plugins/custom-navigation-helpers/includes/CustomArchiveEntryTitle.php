<?php

/* Archive Entry Titles customization
   Inherits from CustomArchive
--------------------------------------------*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomArchiveEntryTitle extends CustomArchive {


	public function __construct() {
		parent::__construct();
		add_filter( 'genesis_post_title_output', array($this,'archive_title' ), 15 );		
	}
	

	/* Customize entry title in the archive pages
	-----------------------------------------------------------------------------*/
	public function archive_title($title) {

		
		/* Display start rating below entry */
		if ( is_tax() || is_search() ) {
				$title .= do_shortcode('[display-star-rating category="global" display="minimal"]');
				//echo 'User rating global = ' . get_post_meta( get_the_ID(), 'user_rating_global', true );
		};

		if ( is_tax('cuisine', array('france', 'europe', 'asie', 'afrique', 'amerique-nord', 'amerique-sud') ) ) {
			$post_id = get_the_ID();
			$origin = wp_get_post_terms( $post_id, 'cuisine', array("fields" => "names"));
			$diet = wp_get_post_terms( $post_id, 'diet');
			$season = wp_get_post_terms( $post_id, 'season');

			$title = $this->output_tags($origin[0], $diet[0]->slug, '', $season[0]->slug) . $title;
		};
		
		if ( is_tax('course') ) {
			$post_id = get_the_ID();
			
			$occasion = wp_get_post_terms( $post_id, 'occasion');
			$season = wp_get_post_terms( $post_id, 'season');
			$diet = wp_get_post_terms( $post_id, 'diet');
			
			$title = $this->output_tags('', $diet[0]->slug, $occasion, $season[0]->slug) . $title;
		};	
		
		if ( is_tax('season') ) {
			$post_id = get_the_ID();
			
			$diet = wp_get_post_terms( $post_id, 'diet');
			$occasion = wp_get_post_terms( $post_id, 'occasion');
			
			$title = $this->output_tags('', $diet[0]->slug, $occasion, '') . $title;
		};
		
		if ( is_tax('diet') ) {
			$post_id = get_the_ID();
			
			$season = wp_get_post_terms( $post_id, 'season');
			$occasion = wp_get_post_terms( $post_id, 'occasion');
			
			$title = $this->output_tags('', '', $occasion, $season[0]->slug) . $title;
		};						

		if ( is_tax('occasion') ) {
			$post_id = get_the_ID();
			$diet = wp_get_post_terms( $post_id, 'diet');
			$season = wp_get_post_terms( $post_id, 'season');
			
			$title = $this->output_tags( '', $diet[0]->slug, '', $season[0]->slug) . $title;
		};	

		if ( is_tax('ingredient') ) {
			
			$post_id = get_the_ID();
			$origin = wp_get_post_terms( $post_id, 'cuisine', array("fields" => "names"));
			$diet = wp_get_post_terms( $post_id, 'diet');
			$season = wp_get_post_terms( $post_id, 'season');
			$occasion = wp_get_post_terms( $post_id, 'occasion');
			
			$title = $this->output_tags( $origin[0], $diet[0]->slug, $occasion, $season[0]->slug) . $title;
		};					

		return $title;
	}


	public function output_tags($origin, $diet, $occasion, $season) {
		
		$season_msg = __('Seasonal', 'foodiepro');
		$fest_msg = __('Festive', 'foodiepro');
		//$veg_msg = __('Veggie', 'foodiepro');
		$veg_msg = '<i class="fa fa-leaf" aria-hidden="true"></i>';
		$tags = '';
		
		$left_id=0;
		$right_id=0;
		

		if ( $this->is_veg($diet) ) {
			//$tags .= '<div class="overlay" id="veg">' . $veg_msg . '</div>';
			$tags .= '<div class="overlay" id="veg">' . $veg_msg . '</div>';
			$right_id++;
		}
		
		if ( $origin!='' ) {
			$tags .= '<div class="overlay" id="left' . $left_id . '">' . $origin . '</div>';
			$left_id++;		
		}
		
		if ($this->is_season($season)) {
			$tags .= '<div class="overlay col-' . $season[0] . '" id="left' . $left_id . '">' . $season_msg . '</div>';
			$left_id++;
		}
		
		//print_r($occasion);
		if ($this->is_fest($occasion)) {
			$tags .= '<div class="overlay" id="left' . $left_id . '">' . $fest_msg . '</div>';
			$left_id++;
		}	
		
		return $tags;
	}



}











