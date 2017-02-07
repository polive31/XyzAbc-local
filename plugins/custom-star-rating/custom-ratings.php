<?php
/*
Plugin Name: Custom Star Rating
Plugin URI: http://goutu.org/custom-star-rating
Description: Ratings via stars in comments
Version: 1.0
Author: Pascal Olive
Author URI: http://goutu.org
License: GPL
Text Domain: custom-star-rating
Domain Path: ./lang
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//*************************************************************************
//**               INITIALIZATION
//*************************************************************************

//if ( is_single() ) {
if ( true ) {

	define( 'PLUGIN_PATH', plugins_url( '', __FILE__ ) );

	require 'helpers/functions.php';
	require 'templates/comments-list.php';
	require 'templates/comment-form.php';
	require 'shortcodes/shortcodes.php';


	/* Chargement des feuilles de style custom et polices */
	function load_custom_rating_style_sheet() {
		//wp_enqueue_style( 'custom-ratings',  plugins_url( '/assets/custom-star-rating.css', __FILE__ ), array(), CHILD_THEME_VERSION );
		wp_enqueue_style( 'custom-ratings', PLUGIN_PATH . '/assets/custom-star-rating.css' , array(), CHILD_THEME_VERSION );
	}
	add_action( 'wp_enqueue_scripts', 'load_custom_rating_style_sheet' );

	/* Chargement du text domain */
	function custom_star_rating_load_textdomain() {
		load_plugin_textdomain( 'custom-star-rating', false, 'custom-star-rating/lang/' );
	}
	add_action('plugins_loaded', 'custom_star_rating_load_textdomain');

}


//*************************************************************************
//**               POST & COMMENTS UPDATE
//*************************************************************************

/* Add field 'rate' to the comments meta on submission using PHP
------------------------------------------------------------ */
add_action('comment_post','update_comment_post_meta_php',10,3);

function update_comment_post_meta_php($comment_id, $comment_approved,$comment) {
	
	//PC::debug('In comment post !');
	//PC::debug($comment);
	$post_id = $comment['comment_post_ID'];
	//PC::debug(array('Post ID :'=>$post_id));

	// Retrieve new rating
	$rating = $_POST['rating'];
	reset($rating);
	$rating_val=key($rating);
	
	// Update comment meta with new rating
	add_comment_meta($comment_id, 'rating', $rating_val);

	// Update post meta with new rating table & rating stats
	//PC::magic_tag($post_id);
	
	$user_ip = get_user_ip();
	PC::debug(array('User IP :'=>$user_ip));
	
	$user_ratings = get_post_meta( $post_id, 'user_ratings' );
	PC::debug(array('User Ratings Table :'=>$user_ratings));

	if ( !empty($user_ratings) )
		$new_user_id = count( $user_ratings ) + 1;
	else {
		$new_user_id = 1;
	}

	$new_user_rating = array(
		'user' => $new_user_id,
		'ip'=>$user_ip,
		'rating'=> $rating_val,
	);
	PC::debug(array('New User Rating :'=>$new_user_rating ) );
	add_post_meta($post_id, 'user_ratings', $new_user_rating);
	
	$user_ratings[]=$new_user_rating;
	PC::debug(array('User Ratings table :'=>$user_ratings ) );
	
	$stats = get_rating_stats( $user_ratings );
	PC::debug(array('Stats :'=>$stats) );
	
	update_post_meta($post_id, 'user_rating_stats', $stats);
}


/* Add ratings default value on post save 
-------------------------------------------------------------*/ 
add_action( 'save_post', 'wpurp_add_default_rating', 10, 2 );
function wpurp_add_default_rating( $id, $post ) {
 	if ( ! wp_is_post_revision($post->ID) ) {
 		PC::debug('Default rating add');
 		
 		$init_table = array(
			'votes'=>'0',							
			'rating'=>'0',							
			'stars'=>'0',
			'half'=>false,
		);
 		
		update_post_meta($post->ID, 'user_rating_stats', $init_table);
 	}
}


//*************************************************************************
//**               COMMENTS LIST
//*************************************************************************

// Remove the genesis_default_list_comments function
remove_action( 'genesis_list_comments', 'genesis_default_list_comments' );

// Add our own and specify our custom callback
add_action( 'genesis_list_comments', 'custom_star_rating_list_comments' );
function custom_star_rating_list_comments() {
	if ( is_singular('recipe') ) {
		$args = array(
		    'type'          => 'comment',
		    'avatar_size'   => 54,
		    'callback'      => 'custom_star_rating_comment',
		);
		$args = apply_filters( 'genesis_comment_list_args', $args );		
	}
	wp_list_comments( $args );
}


?>