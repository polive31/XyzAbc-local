<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/* Rating caption
------------------------------------------------------------*/
function rating_caption($val) {
	switch ($val) {
		case 5:
			return __('Delicious','custom-star-rating');
		case 4:
			return __('Very good','custom-star-rating');
		case 3:
			return __('Rather good','custom-star-rating');
		case 2:
			return __('Not so good','custom-star-rating');
		case 1:
			return __('Really not good','custom-star-rating');
		case 0:
			return __('Not rated','custom-star-rating');
	}
}


/* Add new user rating */
function add_user_rating( $user_ratings, $new_rating_val) {
	$user_ip = get_user_ip();
	$nb_users = count( $user_ratings ) + 1;
	$user_ratings[] = array(
		'user' => $nb_users,
		'ip'=>$user_ip,
		'rating'=> $new_rating_val,
	);
	return '';
}


/* Calculate average user rating
-------------------------------------------------------------*/
function get_rating_stats( $user_ratings) {
  $votes = count( $user_ratings );
  $total = 0;
  $avg_rating = 0;
  $stars = 0;
  $half_star = false;

  foreach( $user_ratings as $user_rating )
  	{$total += $user_rating['rating'];}

  if( $votes !== 0 ) {
      $avg_rating = $total / $votes; // TODO Just an average for now, implement some more functions later
      $stars = floor( $rating );
      if( $avg_rating - $stars >= 0.5 ) {
          $half_star = true;}
      $avg_rating = round( $avg_rating, 2 );
  }
  
  return array(
      'votes' => $votes,
      'rating' => $avg_rating,
      'stars' => $stars,
      'half_star' => $half_star,
  );
}


/* Get the user ip
-------------------------------------------------------------*/
// Source: http://stackoverflow.com/questions/6717926/function-to-get-user-ip-address
function get_user_ip() {
  foreach( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
    if( array_key_exists( $key, $_SERVER ) === true ) {
      foreach( array_map( 'trim', explode( ',', $_SERVER[$key] ) ) as $ip ) {
        if( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
            return $ip;
        }
      }
    }
  }
  return 'unknown';
}



?>