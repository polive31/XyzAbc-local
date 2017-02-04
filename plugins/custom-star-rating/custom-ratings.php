<?php
/*
Plugin Name: Custom Star Rating
Plugin URI: http://goutu.org/custom-star-rating
Description: Ratings via stars in comments
Version: 1.0
Author: Pascal Olive
Author URI: http://goutu.org
License: GPL
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//*************************************************************************
//**               INITIALIZATION
//*************************************************************************

define( 'PLUGIN_PATH', plugins_url( '', __FILE__ ) );

/* Chargement des feuilles de style custom et polices */
function load_custom_rating_style_sheet() {
	//wp_enqueue_style( 'custom-ratings',  plugins_url( '/assets/custom-star-rating.css', __FILE__ ), array(), CHILD_THEME_VERSION );
	wp_enqueue_style( 'custom-ratings', PLUGIN_PATH . '/assets/custom-star-rating.css' , array(), CHILD_THEME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'load_custom_rating_style_sheet' );


//*************************************************************************
//**               COMMENTS FORM
//*************************************************************************

/* Add field 'rate' to the comments meta on submission using PHP
------------------------------------------------------------ */
function comment_ratings_php($comment_id) {
		$rating = $_POST['rating'];
		reset($rating);
		$rating_val=key($rating);
    add_comment_meta($comment_id, 'rating', $rating_val);
}
add_action('comment_post','comment_ratings_php');


/* Rating caption
-----------------------------------------------*/
function rating_caption($val) {
	switch ($val) {
		case 5:
			return __('delicious','foodiepro');
		case 4:
			return __('very good','foodiepro');
		case 3:
			return __('rather good','foodiepro');
		case 2:
			return __('not so good','foodiepro');
		case 1:
			return __('really not good','foodiepro');
		case 0:
			return __('not rated','foodiepro');
	}
}


/* Custom Comment Form with PHP
------------------------------------------------------------ */
function output_comment_form_html_php() {
	
	$post_id = get_the_id();
	
	ob_start();?>
	
<p class="rating-wrapper" id="star-rating-form">
<input type="hidden" name="postID" value="<?php echo $post_id;?>">
<input type="radio" class="rating-input" id="rating-input-1-5" name="rating[5]" value="5"/>
<label for="rating-input-1-5" class="rating-star" title="<?php echo __('delicious','foodiepro');?>"></label>
<input type="radio" class="rating-input" id="rating-input-1-4" name="rating[4]" value="4"/>
<label for="rating-input-1-4" class="rating-star" title="<?php echo __('very good','foodiepro');?>"></label>
<input type="radio" class="rating-input" id="rating-input-1-3" name="rating[3]" value="3"/>
<label for="rating-input-1-3" class="rating-star" title="<?php echo __('rather good','foodiepro');?>"></label>
<input type="radio" class="rating-input" id="rating-input-1-2" name="rating[2]" value="2"/>
<label for="rating-input-1-2" class="rating-star" title="<?php echo __('not so good','foodiepro');?>"></label>
<input type="radio" class="rating-input" id="rating-input-1-1" name="rating[1]" value="1"/>
<label for="rating-input-1-1" class="rating-star" title="<?php echo __('really not good','foodiepro');?>"></label>
</p>
<p class="comment-form-comment">
<label for="comment"><?php echo _x( 'Comment', 'noun' );?></label>
<textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
</p>


<?php

	$rating_form = ob_get_contents();
	ob_end_clean();
	
	return $rating_form;

}

/* Comment with rating input shortcode
-----------------------------------------------*/
function display_comment_form_with_rating() {
	$args = array (
		'title_reply' => __( '', 'genesis' ), //default=�Speak Your Mind�; WordPress deafult: __( 'Leave a Reply� )
		'label_submit' => __( 'Send', 'foodiepro' ), //default=�Post Comment�
		'comment_field' => output_comment_form_html_php(), 
		'logged_in_as' => '', //Default: __( 'Leave a Reply to %s� )
		'title_reply_to' => __( 'Reply Title', 'genesis' ), //Default: __( 'Leave a Reply to %s� )
		'cancel_reply_link' => __( 'Cancel', 'genesis' ) //Default: __( �Cancel reply� )
		);
	
  ob_start();
  
  //display_rating_form();
  comment_form($args);
  
  $cr_form = ob_get_contents();
  ob_end_clean();
  
  return $cr_form;
 }
add_shortcode( 'comment-rating-form', 'display_comment_form_with_rating' );


//*************************************************************************
//**               COMMENTS LIST
//*************************************************************************

// Remove the genesis_default_list_comments function
remove_action( 'genesis_list_comments', 'genesis_default_list_comments' );

// Add our own and specify our custom callback
add_action( 'genesis_list_comments', 'custom_star_rating_list_comments' );
 
function custom_star_rating_list_comments() {
    $args = array(
        'type'          => 'comment',
        'avatar_size'   => 54,
        'callback'      => 'custom_star_rating_comment',
    );
    $args = apply_filters( 'genesis_comment_list_args', $args );
    wp_list_comments( $args );
}


/* Custom Comment Template */
function custom_star_rating_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment; ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
        <div id="comment-<?php comment_ID(); ?>">
	        	<div class="comment-intro">
	        		
	            <p class="comment-author">
	                <?php echo get_avatar($comment,$size='48'); ?>
	 
	                <?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_comment_author_link()) ?>
	            </p>
	 
	            <?php if ($comment->comment_approved == '0') : ?>
	            <em><?php _e('Your comment is awaiting moderation.') ?></em>
	            <br />
	            <?php endif; ?>
	 
	            <div class="comment-meta">
	            	<a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
	            		<?php printf(__('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?>
	            		</a>
	            </div>
	            
	            <div class="comment-edit">
	            	<?php edit_comment_link(__('(Edit)'),'  ','') ?>
	 						</div>
	        	</div>
 						
            <?php
            $rating = get_comment_meta($comment->comment_ID, 'rating');
            $rating=$rating[0];
            //echo "Rating = " . $rating;?>
            
            <div class="comment-rating rating" id="stars-<?php echo $rating;?>" title="<?php echo rating_caption($rating);?>">
            </div>

 						<div class="comment-content">
            <?php comment_text() ?>
 						</div>
 
            <div class="comment-reply">
                <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
            </div>
        </div>
    </li>
    
<?php
}


?>