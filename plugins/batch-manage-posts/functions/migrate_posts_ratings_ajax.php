<?php
/*
Description: Administrator shortcodes for Goutu
Author: Pascal Olive
Author URI: http://goutu.org
*/

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

	
/* =================================================================*/
/* =               BATCH MIGRATE POST RATINGS
/* =================================================================*/

function ajax_migrate_ratings() {
	
	PC::debug( array('In AJAX MIGRATE RATINGS') );
	echo "<p>Batch Migrate Ratings script started...</p>";
	
	$post_type = get_ajax_arg('post-type');
	$include = get_ajax_arg('include',__('Limit to posts','batch-manage-posts'));
	
	if ( !(is_secure('MigrateRatings' . 'migrate') ) ) exit;
			

	PC::debug( array('Nonce check PASSED') );
	//PC:debug( array('$value after explode : '=>$value) );
		

	//$post_type_object = get_post_type_object($post_type);
	//$label = $post_type_object->label;

//	$cats = CustomStarRatings::getRatingCats( true );
//	PC::debug( array('$cats : '=> $cats) );

	$include = ($include=='all')?'':$include;

	$posts = get_posts(array('include'=>$include, 'post_type'=> $post_type, 'post_status'=> 'publish', 'suppress_filters' => false, 'posts_per_page'=>-1));

	foreach ($posts as $post) {

	  $user_ratings = get_post_meta($post->ID, 'recipe_user_ratings', false);
		//if ( empty($user_rating) && empty($user_ratings) ) continue;
		if ( !empty($user_ratings) ) {
			delete_post_meta($post->ID, 'user_ratings', '');
			
			PC::debug( array('$Post title : '=> $post->post_title ) );
			PC::debug( array('$user_ratings : '=> $user_ratings) );
				
			echo sprintf("Post : %s",$post->post_title);
			echo "<br>";
			print_r($user_ratings,false );
			echo "<br>";
			echo "----------------------------";
			echo "<br>";
			
			$rating_global = 0;
			
			foreach ( $user_ratings as $user_rating ) {
				$ip = $user_rating['ip'];
				$user = $user_rating['user'];
				$rating = $user_rating['rating'];
				add_post_meta($post->ID, 'user_ratings', $user_rating);
				PC::debug( array('$user_rating : '=> $user_rating) );
				$rating_global += $rating;
			}
			
			PC::debug( array('$rating_global : '=> $rating_global ) );
			PC::debug( array('count : '=> count($user_ratings) ) );
			$rating_global = $rating_global/count($user_ratings);
			update_post_meta($post->ID, 'user_rating_rating', $rating_global);
			update_post_meta($post->ID, 'user_rating_global', $rating_global);

			echo sprintf("APRES MIGRATION : %s",$post->post_title);
			echo "<br>";
		  $user_ratings = get_post_meta($post->ID, 'user_ratings', false);
			print_r($user_ratings,false );
			echo "<br>";
		  $user_rating = get_post_meta($post->ID, 'user_rating_rating', true);
			echo sprintf("user_rating_rating : %s", $user_rating );
			echo "<br>";
		  $user_rating = get_post_meta($post->ID, 'user_rating_global', true);
			echo sprintf("user_rating_global : %s", $user_rating );
			echo "<br>";
			echo "----------------------------";
			echo "<br>";
		

		}
		
		else {
			add_post_meta($post->ID, 'user_rating_global', '0');

		}
		
	  
	}
}
