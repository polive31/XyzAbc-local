<?php 

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/* Custom Comment Form with PHP (called from shortcodes.php)
------------------------------------------------------------ */
function output_comment_form_html_php($id) {
	
	ob_start();?>
	
	<div class="rating-wrapper" id="star-rating-form">
	<input type="radio" class="rating-input" id="rating-input-<?php echo $id;?>-5" name="rating-<?php echo $id;?>" value="5"/>
	<label for="rating-input-<?php echo $id;?>-5" class="rating-star" title="<?php echo rating_caption(5);?>"></label>
	<input type="radio" class="rating-input" id="rating-input-<?php echo $id;?>-4" name="rating-<?php echo $id;?>" value="4"/>
	<label for="rating-input-<?php echo $id;?>-4" class="rating-star" title="<?php echo rating_caption(4);?>"></label>
	<input type="radio" class="rating-input" id="rating-input-<?php echo $id;?>-3" name="rating-<?php echo $id;?>" value="3"/>
	<label for="rating-input-<?php echo $id;?>-3" class="rating-star" title="<?php echo rating_caption(3);?>"></label>
	<input type="radio" class="rating-input" id="rating-input-<?php echo $id;?>-2" name="rating-<?php echo $id;?>" value="2"/>
	<label for="rating-input-<?php echo $id;?>-2" class="rating-star" title="<?php echo rating_caption(2);?>"></label>
	<input type="radio" class="rating-input" id="rating-input-<?php echo $id;?>-1" name="rating-<?php echo $id;?>" value="1"/>
	<label for="rating-input-<?php echo $id;?>-1" class="rating-star" title="<?php echo rating_caption(1);?>"></label>
	</div>
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