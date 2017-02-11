<?php
/*
Plugin Name: Admin Shortcodes
Plugin URI: http://goutu.org/
Description: Administrator shortcodes for Goutu
Version: 1.0
Author: Pascal Olive
Author URI: http://goutu.org
License: GPL
*/


// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

	
/* =================================================================*/
/* =               TEST SHORTCODE
/* =================================================================*/

add_shortcode('test-shortcode', 'test_shortcode');

function test_shortcode() {
	echo 'TEST SHORTCODE';
}
	
	
/* =================================================================*/
/* =               BATCH UPDATE USER RATINGS
/* =================================================================*/

add_shortcode('batch-update-meta', 'batch_update_meta');

/* Batch update user_ratings_ratings custom field */
function batch_update_meta($atts) {
	$a = shortcode_atts( array(
		'post-type' => 'recipe',
		'post-id' => '0',
		'key' => 'user_rating',
		'new-key' => '',
		'value' => '0',//can be scalar or array of space-separated $key/$value pairs
		'cmd' => 'add',//replace, delete, rename
	), $atts );
	
	static $script_id; // allows several shortcodes on the same page
	++$script_id;
	
	echo "<h3>BATCH UPDATE META SHORTCODE</h3>";
	
	$key = $a['key'];
	echo sprintf("<b>Key</b> = %s",$key);
	echo "<br>";
	
	$new_key = $a['new-key'];
	echo sprintf("<b>New key</b> = %s",$new_key);
	echo "<br>";

  $post_type = $a['post-type'];
	echo sprintf("<b>Post type</b> = %s",$post_type);
	echo "<br>";

	if ( is_array( $a['post-id'] ) ) {
		$post_id = explode($a['post-id'], ' ');
		echo "<b>Limited to posts </b> = " . print_r( $post_id );
		echo "<br>";
	}
	else {
		$post_id = $a['post-id'];
		echo sprintf("<b>Limited to posts </b> = %s",$post_id);
		echo "<br>";
	}

	$value = $a['value'];
	echo sprintf("<b>Value</b> = %s",$value);
	echo "<br>";

	$cmd = $a['cmd'];
	
	$style='';
	if ($cmd=='delete') $style='background-color:red';
	if ($cmd=='update') $style='background-color:brown';
	
	ob_start();?>
	
	<div id = "center">
	<form action="." method="post">
	<input style="<?php echo $style;?>" type="submit" name="Submit_<?php echo $script_id?>" value="<?php echo $cmd;?>">
	</form>
	</div>
	<br>
	
	<?php
	$form=ob_get_contents();
	ob_end_clean();
	
	echo $form;
	
	if (isset($_POST['Submit_' . $script_id])) {
			
			if ( is_array($value) )
				$value = extractKeyValuePairs( $value );
			else
				$value = $a['value'];
			
			//PC:debug( array('$value after explode : '=>$value) );
				
			echo "<p>Batch Update Meta script started...</p>";
		  
		  $post_type_object = get_post_type_object($post_type);
		  $label = $post_type_object->label;

		  $posts = get_posts(array('post_type'=> $post_type, 'post_status'=> 'publish', 'suppress_filters' => false, 'posts_per_page'=>-1));

		  foreach ($posts as $post) {
		  	if ( ! in_array($post->ID, $post_id) ) continue;
		    $meta_value = get_post_meta($post->ID, $key, True);
		    switch ($cmd) {
		    	case 'add':
		    		if ( empty($meta_value) ) add_post_meta($post->ID, $key, $value);
		    		echo sprintf("%s=%s added to %s",$key,$value,$post->post_title) . "<br>"; //Prints updated after ran.
		    		break;
		    	case 'rename':
		    		if ( ! empty($meta_value) ) {
		    			update_post_meta($post->ID, $new_key, $meta_value);
		    			delete_post_meta($post->ID, $key);
		    			echo sprintf("%s renamed to %s in %s",$key,$new_key,$post->post_title) . "<br>"; //Prints updated after ran.
		    		}
		    		else {
		    			update_post_meta($post->ID, $new_key, '0');
		    			echo sprintf("Key %s not found in %s. Updated %s to '0'.",$key, $post->post_title, $newkey);
		    		}
						$new_value = get_post_meta($post->ID, $new_key, True);
						PC::debug(array('Value for renamed key' => $new_value));
		    		break;
		    	case 'replace':
		    		update_post_meta($post->ID, $key, $value);
		    		echo sprintf("%s updated to %s in %s",$key,$value,$post->post_title) . "<br>"; //Prints updated after ran.
		    		break;
		    	case 'delete':
		    		if ( ! empty($meta_value) ) delete_post_meta($post->ID, $key);
		    		echo sprintf("%s key deleted in %s",$key,$post->post_title) . "<br>"; //Prints updated after ran.
		    		break;
		    }
		  }
	}
	
}

function confirmChoice() {
	echo __("Are you sure you want to do this?  Type 'yes' to continue: ");
	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	if (trim($line) != __('yes')) {
	    echo "ABORTING!\n";
	    return false;
	}
	fclose($handle);
	echo "\n"; 
	echo __('Thank you, continuing...') . "\n";
	return true;
}

function extractKeyValuePairs($string, $delimiter = ' ') {
    $params = explode($delimiter, $string);
    $pairs = [];
    for ($i = 0; $i < count($params); $i++) {
        $pairs[$params[$i]] = $params[++$i];
    }

    return $pairs;
}

?>