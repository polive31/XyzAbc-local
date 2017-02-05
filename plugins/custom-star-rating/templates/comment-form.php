<?php 

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/* Custom Comment Form with PHP
------------------------------------------------------------ */
function output_comment_form_html_php() {
	
	ob_start();?>
	
	<p class="rating-wrapper" id="star-rating-form">
	<!-- <input type="hidden" name="postID" value="<?php echo $post_id;?>"> -->
	<input type="radio" class="rating-input" id="rating-input-1-5" name="rating[5]" value="5"/>
	<label for="rating-input-1-5" class="rating-star" title="<?php echo rating_caption(5);?>"></label>
	<input type="radio" class="rating-input" id="rating-input-1-4" name="rating[4]" value="4"/>
	<label for="rating-input-1-4" class="rating-star" title="<?php echo rating_caption(4);?>"></label>
	<input type="radio" class="rating-input" id="rating-input-1-3" name="rating[3]" value="3"/>
	<label for="rating-input-1-3" class="rating-star" title="<?php echo rating_caption(3);?>"></label>
	<input type="radio" class="rating-input" id="rating-input-1-2" name="rating[2]" value="2"/>
	<label for="rating-input-1-2" class="rating-star" title="<?php echo rating_caption(2);?>"></label>
	<input type="radio" class="rating-input" id="rating-input-1-1" name="rating[1]" value="1"/>
	<label for="rating-input-1-1" class="rating-star" title="<?php echo rating_caption(1);?>"></label>
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



?>