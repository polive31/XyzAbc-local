<?php 


// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');
	
class CustomNavigationShortcodes extends CustomArchive {
	
	public function __construct() {
		parent::__construct();
		add_shortcode('index-link', array($this,'add_index_link')); 
		add_shortcode('ct-terms', array($this,'list_terms_taxonomy'));
		add_shortcode('permalink', array($this,'add_permalink_shortcode'));
		add_shortcode('share-title', array($this,'display_share_title')); 
		add_filter( 'query_vars', array($this,'archive_filter_queryvars') );		

	}
	

	/* Custom query variable for taxonomy filter
	--------------------------------------------- */		
	function archive_filter_queryvars($vars) {
	  $vars[] = 'filter';
	  $vars[] .= 'filter_term';
	  return $vars;
	}
	
	/* Share Title Output
	--------------------------------------------- */		
	public function display_share_title() {
		if (is_singular()) {
			if (is_singular('recipe')) 
				$msg=__('Share this recipe','foodiepro');
			else
				$msg=__('Share this post','foodiepro');
			$html = '<h3 class="share-title">' . $msg . '</h3>';
		}
		return $html;
	}


	/* =================================================================*/
	/* =                   ADD INDEX LINK  
	/* =================================================================*/


	public function add_index_link($atts) {
		 //Inside the function we extract parameter of our shortcode
		extract( shortcode_atts( array(
			'back' => 'false',
		), $atts ) );
		
		//PHP_Debug::log(' In index-link shortcode');
		
		$url='';				
		$msg='';				
	
		if ( ($back!='true') && !is_search() ) {
			
			$obj = get_queried_object();
			$tax_id = $obj -> taxonomy;
			$parent_id = $obj -> parent;
			$parent = get_term_by('id', $parent_id,'cuisine');
			////PHP_Debug::log(array('Parent id = ', $parent_id));
			$parent_slug = ($parent)?$parent->slug:'';
			$parent_name = ($parent)?$parent->name:'';
			////PHP_Debug::log(array('Parent slug = ', $parent_slug));
			////PHP_Debug::log(array('Parent name = ', $parent_name));
			$current_slug = $obj -> slug;
			////PHP_Debug::log(array('Current taxonomy = ', $tax_id));
			////PHP_Debug::log(array('Current slug = ', $current_slug));

			switch ($tax_id) {
		    case 'course':
					$url = "/accueil/recettes/plats";
					$msg = __('Courses', 'foodiepro');
					break;
		    case 'season':
					$url = "/accueil/recettes/saisons";
					$msg = __('Seasons', 'foodiepro');
					break;
		    case 'occasion':
					$url = "/accueil/recettes/occasions";
					$msg = __('Occasions', 'foodiepro');
					break;
		    case 'diet':
					$url = "/accueil/recettes/regimes";
					$msg = __('Diets', 'foodiepro');
					break;
		    case 'cuisine':
		    	if ( $current_slug=='france' ) {
		    		$url = "/accueil/recettes/regions";
						$msg = __('France', 'foodiepro');
					}
		    	elseif ( $parent_slug=='france' ) {
		    		$url = '/origine/france';
						$msg = __('France', 'foodiepro');
					}					
		    	elseif (!empty($parent_slug)) {
		    		$url = '/origine/' . $parent_slug;
		    		$msg = $this->get_cuisine_caption($parent_name);
		    	}
		    	else {
		    		$url = "/accueil/recettes/monde";
						$msg = __('World', 'foodiepro');
					}
		    	break;
		    case 'category':
					$url = "/accueil/articles";
					$msg = __('All posts', 'foodiepro');
					break;	
			}
		}
			
		else {
				$url = 'javascript:history.back()';
				$msg = __('Previous page','foodiepro');
		}
		
		$output = '<ul class="menu"> <li> <a class="back-link" href="' . $url . '">' . $msg . '</a> </li> </menu>';
		return $output;
	}


	/* =================================================================*/
	/* = TAXONOMY LIST SHORTCODE     
	/* =================================================================*/

	public function list_terms_taxonomy( $atts ) {
		static $dropdown_cnt;
		extract( shortcode_atts( array(
			'dropdown' => 'false',
			'taxonomy' => 'category',
			'filter' => 'false',
			'select_msg' => __( 'Select...', 'foodiepro' ),
			'all_msg' => '',
			'depth' => 1,
			'child_of' => 0,
			'exclude' => '',
			'index_title' => '',
			'index_path' => ''
		), $atts ) );

		$filter = ($filter == 'true');

		$html = '';
		
/* arguments for function wp_list_categories
------------------------------------------------------------------------*/
	// Source taxonomy
		$all_url='#';
		
		$obj = get_queried_object();
		//echo var_dump( $obj );
		$tax_slug = $obj->taxonomy;
		$tax = get_taxonomy($tax_slug);
		//$term_name = $obj->name;
		$term_slug = $obj->slug;		
		//echo sprintf( '$tax_slug = %s <br>', $tax_slug);
			
			
	// Output taxonomy and parent term			
		if ($tax_slug == 'cuisine') { // $tax_slug will stay cuisine
			if ($obj->parent != 0) // term has a parent => either country or region archive
				$child_of = $obj->parent; // wp_list_categories will use parent to filter
			else // term has no parent => either continent or france
				$child_of = $obj->term_id; // wp_list_categories will use current term to filter
		}
	

	// Arguments for wp_list_categories	
		$args = array( 
			'taxonomy'	=> $tax_slug,
			'child_of'	=> $child_of,
			'depth' 		=> $depth,
			'exclude' 	=> $exclude,
			'orderby' 	=> 'slug',
			'echo' 			=> false
		);
		
		if ($dropdown=='true') {	
			$dropdown_id = $taxonomy . ++$dropdown_cnt;
			
			$html = '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '"> . $label . </label>';

			$args['show_option_none'] = $select_msg;
			//$args['show_option_all'] = $all_msg;
			$args['show_option_all'] = '';
			$args['option_none_value'] = 'none';
			$args['selected'] = get_query_var('fterm');
			$args['id'] = $dropdown_id;
			$args['name'] = $dropdown_id;
			$args['class'] = 'dropdown-select';
			$args['value_field'] = 'slug';
			
			$html .= wp_dropdown_categories( $args );
				
			ob_start();
			?>
			
			<script type='text/javascript'>
			/* <![CDATA[ */
			(function() {
			 var <?php echo $dropdown_id;?>_dropdown = document.getElementById( "<?php echo esc_js( $dropdown_id );?>" );
			 function on_<?php echo $dropdown_id;?>_Change() {
			  var choice = <?php echo $dropdown_id;?>_dropdown.options[ <?php echo $dropdown_id;?>_dropdown.selectedIndex ].value;
				if ( choice !="none" ) {
					  <?php echo 'location.href = "' . home_url() . '/?' . $tax_slug . '=" + choice'; ?>
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


	/* Output permalink of a given post id
	------------------------------------------------------*/

	public function add_permalink_shortcode($atts) {
		extract(shortcode_atts(array(
			'id' => false,
			'html' => false, // html markup or url only
			'text' => ""  // default value if none supplied
	    ), $atts));
	
		if (! $id) $url=$_SERVER['REQUEST_URI'];
		else $url=get_permalink($id);
			
    if ($html) return "<a href='$url'>$text</a>";
    else return $url;
	}



}



