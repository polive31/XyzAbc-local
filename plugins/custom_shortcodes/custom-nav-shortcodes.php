<?php
/*
Plugin Name: Custom Navigation Shortcodes
Plugin URI: http://goutu.org/
Description: Taxonomy and navigation custom shortcodes
Version: 1.0
Author: Pascal Olive
Author URI: http://goutu.org
License: GPL
*/

// Add a shortcode that executes our function
add_shortcode('ct-terms', 'list_terms_taxonomy');

/* =================================================================*/
/* =                    TAXONOMY LIST SHORTCODE     
/* =================================================================*/

function list_terms_taxonomy( $atts ) {
	static $dropdown_cnt;
	extract( shortcode_atts( array(
		'dropdown' => 'false',
		'taxonomy' => 'category',
		'label' => '',
		'select_msg' => __( 'Select...', 'foodiepro' ),
		'all_msg' => '',
		'depth' => 1,
		'child_of' => 0,
		'exclude' => '',
		'index_title' => '',
		'index_path' => ''
	), $atts ) );


	$html = '';
// Extraction of taxonomy from current url
	$all_url='#';
	if ($taxonomy == 'url') {
		$obj = get_queried_object();
		$taxonomy = $obj -> taxonomy;
		if ($taxonomy == 'cuisine') {
			// extract term of depth = 1
			$parent = $obj -> parent;
			$current = $obj -> term_id;
			if ($parent==0) {
				$child_of = $current;}
			else {
				$child_of = $parent;
				$parent_meta = get_term_by('id', $parent, 'cuisine');
				if ($parent_meta != false) $all_msg = $parent_meta->name;
				$all_url = add_query_arg( 'cuisine', $parent_meta->slug, home_url() );
			}
		}
	}

 //arguments for function wp_list_categories
	$args = array( 
		'taxonomy' => $taxonomy,
		'child_of' => $child_of,
		'depth' => $depth,
		'exclude' => $exclude,
		'orderby'  => 'slug',
		//'title_li' => '',
		'echo' => false
	);
	
	if ($dropdown=='true') {	
		$dropdown_id = $taxonomy . ++$dropdown_cnt;
		
		$html = '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . $label . '</label>';

		$args['show_option_none'] = $select_msg;
		$args['show_option_all'] = $all_msg;
		$args['option_none_value'] = 'none';
		$args['selected'] = 'none';
		$args['id'] = $dropdown_id;
		$args['name'] = $dropdown_id;
		$args['value_field'] = 'slug';
		
		$html .= wp_dropdown_categories( $args );
		
		// Get taxonomy slug from taxonomy ID
		$tax_meta = get_taxonomy( $taxonomy );
		if ($tax_meta != false) 
			$tax_slug = $tax_meta->rewrite['slug'];
			
		ob_start();
		?>
		
		<script type='text/javascript'>
		/* <![CDATA[ */
		(function() {
		 var <?php echo $dropdown_id;?>_dropdown = document.getElementById( "<?php echo esc_js( $dropdown_id );?>" );
		 function on_<?php echo $dropdown_id;?>_Change() {
		  var choice = <?php echo $dropdown_id;?>_dropdown.options[ <?php echo $dropdown_id;?>_dropdown.selectedIndex ].value;
			if ( choice !="none" ) {
				  location.href = "<?php echo home_url() . '/' . $tax_slug . '/';?>" + choice;
			}
			if ( choice =="0" ) {
				  location.href = "<?php echo $all_url;?>";
			}
		 }
			<?php echo $dropdown_id;?>_dropdown.onchange = on_<?php echo $dropdown_id;?>_Change;
		})();
		/* ]]> */
		</script>
		
		<?php
		$html .= ob_get_contents();
    ob_end_clean();

	}
	
	else {

	 	$html = '<ul class="menu">';
	 	// wrap it in unordered list 

		$html .= wp_list_categories($args);	

		if ($index_title!='')
			$html .= '<li class="ct-index-url"> <a class="back-link" href="' . site_url($index_path) . '">' . $index_title . '</a></li>';
	 
	 	$html .= '</ul>';
		
	}

 // Return the output
 	return $html;
 
}




/* =================================================================*/
/* =                    INDEX LINKS GENERATION   
/* =================================================================*/

function add_index_link($atts) {
	 //Inside the function we extract parameter of our shortcode
	extract( shortcode_atts( array(
		'back' => 'false',
	), $atts ) );
	

	if ($back!='true'):
		
		$obj = get_queried_object();
		$tax_id = $obj -> taxonomy;
		$parent = $obj -> parent;
		$current = $obj -> term_id;

		switch ($tax_id) {
	    case 'course':
				$url = "/recettes/plats";
				//$msg = "De l'ap�ritif au dessert";
				$msg = __('Courses', 'foodiepro');
				break;
	    case 'season':
				$url = "/recettes/saisons";
				//$msg = "Cuisine de saisons";
				$msg = __('Seasons', 'foodiepro');
				break;
	    case 'occasion':
				$url = "/recettes/occasions";
				//$msg = "En toutes occasions";
				$msg = __('Occasions', 'foodiepro');
				break;
	    case 'diet':
				$url = "/recettes/regimes";
				//$msg = "R�gimes et di�t�tique";
				$msg = __('Diets', 'foodiepro');
				break;
	    case 'cuisine':
	    	$url="Parent" . $parent;
	    	if ($parent == 9996 || $current == 9996) {
	    		$url = "/recettes/regions";
					//$msg = "Cuisines de r�gions";
					$msg = __('France', 'foodiepro');}
	    	else {
	    		$url = "/recettes/monde";
					//$msg = "Cuisines du monde";
					$msg = __('World', 'foodiepro');}
	    	break;
	    case 'category':
				$url = "/blogs";
				$msg = __('All blogs', 'foodiepro');
				break;	
		}
		
	else:
			$url = 'javascript:history.back()';
			$msg = __('Previous page','foodiepro');
	endif;
	
	$output = '<ul class="menu"> <li> <a class="back-link" href="' . $url . '">' . $msg . '</a> </li> </menu>';
	return $output;
}
add_shortcode('index-link', 'add_index_link'); 

/* =================================================================*/
/* =                    PERMALINK SHORTCODE     
/* =================================================================*/

function add_permalink_shortcode($atts) {
	extract(shortcode_atts(array(
		'id' => 1,
		'text' => ""  // default value if none supplied
    ), $atts));
    
    if ($text) {
        $url = get_permalink($id);
        return "<a href='$url'>$text</a>";
    } else {
	   return get_permalink($id);
	}
}
add_shortcode('permalink', 'add_permalink_shortcode');


?>