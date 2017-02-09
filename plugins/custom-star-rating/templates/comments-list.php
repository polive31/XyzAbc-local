<?php


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	            		<?php printf(__('%1$s at %2$s','custom-star-rating'), get_comment_date(),  get_comment_time()) ?>
	            	</a>
	            	<?php //$meta = get_comment_meta( $comment->comment_ID );?>
	            	<?php //echo '<pre>'; ?>
	            	<?php //echo 'COMMENT META'; ?>
	            	<?php //print_r( $meta ); ?>
	            	<?php //print_r( get_post_meta( get_the_id() ) ); ?>           	
	            	<?php //print_r( get_comment_meta( $comment->comment_ID )['recipe_user_ratings'] ); ?>
	            	<?php //print_r( get_comment_meta( $comment->comment_ID )['recipe_user_ratings_rating'] ); ?>
	            	<?php //echo '</pre>'; ?>
	            </div>
	            
	            <div class="comment-edit">
	            	<?php edit_comment_link(__('(Edit)'),'  ','') ?>
	 						</div>
	        	</div>
 						
            <?php echo do_shortcode('[display-star-rating source="comment"]');?>         

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