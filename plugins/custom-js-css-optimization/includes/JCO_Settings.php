<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class JCO_Settings {

	protected static $SIZE_SMALL = 100;
	protected static $SIZE_LARGE = 10000;
	protected $menu_slug = 'js_css_optimization';
	protected $form_action = 'jco_update_settings';
	protected $nonce = 'wp8756';
	protected $urls_to_request;
	protected $header_scripts;
	protected $header_styles;
	protected $enqueued_assets; 
//	$enqueued_assets format : 
//	array(
//			'pages' => array(
//				'slug1',
//				'slug2',
//				...
//			)
//		'scripts' => array(
//			'handle' => 'example',
//			'filename' => 'wp_content/plugins/example/example.js',
//			'location' => 'footer', 'header', 'disabled'
//			'deps' => array(
//					'handle1',
//					'handle2',
//					...
//			),
//			'mods'  => array(
//				'minify' => 'yes', 'no'
//				'location' => 'footer', 'header', 'disabled'
//				'group' => array( 
//					'name' => 'group1',
//					'index' => '0',
//				),
//			),
//		),
//		'styles' => array(
//			...
//		)
//	);

	public function __construct() {

		// Admin options page
		add_action( 'admin_menu', array($this, 'add_js_css_menu_option'));
		add_action( 'admin_init', array($this, 'jco_settings_init') );
		//add_action( 'admin_post_$this->action', array ( $this, 'update_settings_cb' ) );
		add_action( 'admin_post_' . $this->form_action, array ( $this, 'update_settings_cb' ) );

		// load assets for this page
    add_action( 'admin_enqueue_scripts', array($this,'load_admin_assets') );

		$this->urls_to_request = array(
			home_url(),
			$this->get_permalink_by_slug('bredele'),
			$this->get_permalink_by_slug('les-myrtilles'),
		);

		if ( get_option( 'jco_enqueue_recording' ) == 'on' ) {
			add_action( 'wp_head', array($this, 'record_header_assets') );
			add_action( 'wp_print_footer_scripts', array($this, 'record_footer_assets') );
		}
		else {
			remove_action( 'wp_head', array($this, 'record_header_assets') );
			remove_action( 'wp_print_footer_scripts', array($this, 'record_footer_assets') );
		}
		
		// hydrate properties with options content
		$this->enqueued_assets = get_option( 'jco_enqueued_assets' );
		if (!isset($this->enqueued_assets['pages'])) $this->enqueued_assets['pages']=array();
		if (!isset($this->enqueued_assets['scripts'])) $this->enqueued_assets['scripts']=array();
		if (!isset($this->enqueued_assets['styles'])) $this->enqueued_assets['styles']=array();

	}

	public function load_admin_assets() {
		PC::debug('In load_admin_styles');
		PC::debug( plugins_url( '/css/jco_options_page.css', __FILE__ ) );

  	wp_enqueue_style( 'jco_admin_css', plugins_url( '../assets/css/jco_options_page.css', __FILE__ ) , false, '1.0.0' );
  	//wp_enqueue_style( 'jco_admin_fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', false, '1.0.0' );
  	//wp_enqueue_style( 'jco_admin_fa', plugins_url( '../assets/fonts/font-awesome/css/font-awesome.min.css', __FILE__ ), array(), '4.7.0' );
  	//wp_enqueue_script( 'jco_admin_fa', 'https://use.fontawesome.com/96ebedc785.js', false, '1.0.0' );
  	wp_enqueue_script( 'jco_admin_js', plugins_url( '../assets/js/jco_options_page.js', __FILE__ ) , false, '1.0.0' );
	}

	public function add_js_css_menu_option() {
		$option_page_id = add_submenu_page(
      'tools.php',
      'JS & CSS Optimization',
      'JS & CSS Optimization',
      'manage_options',
      $this->menu_slug,
      array($this, 'output_options_page')
	    );

		add_action( "load-$option_page_id", array ( $this, 'load_option_page_cb' ) );
	}


	public function jco_settings_init() {
	    // register options
	    register_setting('enqueued_list_options', 'jco_enqueued_assets');
	    register_setting('enqueued_list_options', 'jco_enqueue_recording');

	    // register "general settings" section
	    add_settings_section(
	        'general_settings_section',
	        'General Settings Section',
	        array($this,'output_section_cb'),
	        'js_css_optimization'
	    );

	    // register "enqueued list" section
	    add_settings_section(
	        'enqueued_list_section',
	        'Enqueued Scripts & Styles Section',
	        array($this,'output_section_cb'),
	        'js_css_optimization'
	    );

	    // register new fields in the general settings section
	    add_settings_field(
	        'jco_enqueue_recording',
	        'Activate enqueued scripts & styles recording',
	        array($this,'jco_recording_output'),
	        'js_css_optimization',
	        'general_settings_section'
	    );

	    // register new fields in the enqueued list section
	    add_settings_field(
	        'jco_recorded_pages',
	        'Pages recorded',
	        array($this,'output_pages_list'),
	        'js_css_optimization',
	        'enqueued_list_section',
					array( 
	        	'label_for' => 'jco-recorded-pages',
	        	'class' => 'foldable' )
	    );

	    // register new fields in the enqueued list section
	    add_settings_field(
	        'jco_enqueued_scripts',
	        'Enqueued Scripts',
	        array($this,'output_scripts_list'),
	        'js_css_optimization',
	        'enqueued_list_section',
	        array( 
	        	'label_for' => 'jco-enqueued-scripts',
	        	'class' => 'foldable' )
	    );

			add_settings_field(
	        'jco_enqueued_styles',
	        'Enqueued Styles',
	        array($this,'output_styles_list'),
	        'js_css_optimization',
	        'enqueued_list_section',
	        array(
	        	'label_for' => 'jco-enqueued-styles',
	        	'class' => 'foldable' )

	    );
	}

	public function output_section_cb( $section ) {
		//PC::debug('In section callback');
	  ?>
		<h1><? echo esc_html($section['title']); ?></h1>
		<?php
	}

	public function jco_recording_output() {
		PC::debug( array('jco_enqueue_recording'=>get_option( 'jco_enqueue_recording' )) );
		$record = ( get_option( 'jco_enqueue_recording' ) == 'on')?true:false;
		$checked = $record?'checked="checked"':'';
		?>
		<label class="switch">
  	<input type="checkbox" name="jco_recording_checkbox" <?php echo $checked;?> value="on">
  	<div class="slider round"></div>
		</label>
		<?php
	}

	public function output_pages_list() {
		foreach ($this->enqueued_assets['pages'] as $slug) {
			echo '<p>' . $slug . '</p>';
		}
	}

	public function output_scripts_list() {
		$this->	output_items_list('scripts');
	}

	public function output_styles_list() {
		$this->	output_items_list('styles');
	}

	public function output_items_list( $type) {

    if (! isset ( $this->enqueued_assets[$type] ) ) return;
		$assets = $this->enqueued_assets[$type];
		PC::debug( array( 'enqueued ' . $type . ' : '=>$assets ));
		?>
		
    <table>
    	<tr>
    		<th> Handler </th>
    		<th> Dependencies </th>
    		<th> File size </th>
    		<th> Location </th>
    		<th> Minify </th>
    	</tr>
    <?php
    foreach ($assets as $handle => $asset ) {
			//PC::debug(array('Asset in output_items_list : ' => $asset));
    	$filename = $asset['filename'];

    	$deps = $asset['deps'];
	    $path = parse_url($filename, PHP_URL_PATH);
			//To get the dir, use: dirname($path)
			$path = $_SERVER['DOCUMENT_ROOT'] . $path;
	    $size = size_format( filesize($path) );

	    $location = $this->field_value( $asset, 'location');
	    $minify = $this->field_value( $asset, 'minify');
	    $asset_is_minified = ( $asset[ 'minify' ] == 'yes')?true:false; 
	    $already_minified_msg = __('This file is already minimized within its plugin', 'jco');
	    
    	?>
    	
    	<tr class="enqueued-asset <?php echo $type;?>" id="<?php echo $handle;?>">
	    	<td title="<?php echo $filename;?>"><?php echo $handle;?></td>
	    	<td><?php foreach ($deps as $dep) {echo $dep . '<br>';}?></td>
	    
	    	<td title="<?php echo $path;?>"><?php echo $size;?><?php $this->output_size_notice( $path, $asset_is_minified || $setting_is_minified );?></td>
	    	
	    	<td class="<?php echo $this->field_class( $asset, 'location');?>">
	    		<select class="setting-input location" name="<?php echo $this->field_name( $type, $handle, 'location');?>">
  					<option value="header" <?php echo ($location=='header')?'selected':'';?> >header</option>
  					<option value="footer" <?php echo ($location=='footer')?'selected':'';?> >footer</option>
  					<option value="disabled" <?php echo ($location=='disabled')?'selected':'';?>>disabled</option>
					</select>
				</td>
				
				<td class="<?php echo $this->field_class( $asset, 'minify');?>">
	    		<select class="setting-input minify" <?php echo ($asset_is_minified)?'disabled':'';?> <?php echo ($asset_is_minified)?'title="' . $already_minified_msg . '"' :'';?> name="<?php echo $this->field_name( $type, $handle, 'minify');?>">
  					<option value="no" <?php echo ($minify=='no')?'selected':'';?>  >no</option>
  					<option value="yes" <?php echo ($minify=='yes')?'selected':'';?> >yes</option>
					</select>
				</td>
    	
    	</tr>
    	<?php
    }?>
    </table>

		<?php
	}
	
	private function output_size_notice( $path, $is_minified ) {
		if ( ( filesize($path) > self::$SIZE_LARGE ) && (!$is_minified) ) {
			$msg = __('This file is large and not minized by its plugin : minification recommended', 'jco');
			?>
			
			<i class="icon-warning-sign" title="<?php echo $msg;?>"></i>
			
			<?php
		}
	}
	
	private function field_class( $asset, $field ) {
		$class = '';
		if ( isset( $asset['mods'][ $field ] ) ) {
			$class='modified';
		}
		return $class;
	}
	
	private function field_name( $type, $handle, $field ) {
		return  $type . '_' . $handle . '_' . $field;
	}
	
	private function field_value( $asset, $field ) {
		PC::debug('In Field Value for ' . $field);
		PC::debug(array('Asset : ' => $asset));
		if ( isset( $asset['mods'][ $field ] ) ) {
			$value=$asset['mods'][ $field ];
			PC::debug('Mod found !');
		}
		else {
			PC::debug('Mod not found');
			$value=$asset[ $field ];
		}
		PC::debug( array(' Field value of ' . $field . ' : ' => $value ));
		return $value;
	}

	public function output_options_page() {
	    // check user capabilities
	    if (!current_user_can('manage_options')) {
	        return;
	    }

			$redirect = menu_page_url( $this->menu_slug, FALSE );?>

	    <div class="wrap">
	        <h1><?= esc_html(get_admin_page_title()); ?></h1>
	        <div class="body">
	        </div>
	        <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
	        		<?php
	            // output security fields for the registered setting "wporg_options"
	            settings_fields('options');
	            // output setting sections and their fields
	            do_settings_sections('js_css_optimization');

	            ?>
	            <table class="button-table" col="2">
	            <tr>
								<input type="hidden" name="action" value="<?php echo $this->form_action; ?>">
								<?php wp_nonce_field( $this->form_action, $this->nonce, FALSE ); ?>
								<input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">

	            	<td><?php submit_button( 'Save Settings', 'primary', 'jco_save', true, array('tabindex'=>'1') );?> </td>
	            	<td><?php submit_button( 'Refresh enqueue list', 'secondary', 'jco_refresh', true, array('tabindex'=>'2') );?> </td>
	            	<td><?php submit_button( 'Reset everything', 'delete', 'jco_reset', true, array('tabindex'=>'3') );?> </td>
	          	</tr>
	        </form>
	    </div>
	    <?php
	}


/* FORM SUBMISSION
--------------------------------------------------------------*/

	public function update_settings_cb() {

		// check user capabilities
    if (!current_user_can('manage_options'))
        return;

    if ( ! wp_verify_nonce( $_POST[ $this->nonce ], $this->form_action ) )
        die( 'Invalid nonce.' . var_export( $_POST, true ) );
		//PC::debug('In update_settings_cb function');

		if ( isset ( $_POST[ 'jco_refresh' ] ) ) {
		   	PC::debug( 'In Form submission : REFRESH' );
		    $msg = 'refresh';
		}
		elseif ( isset ( $_POST[ 'jco_reset' ] ) ) {
		    update_option( 'jco_enqueued_assets', array() );
		    $this->enqueued_assets = array();
		    $msg = 'reset';
		}
		else {
				PC::debug( 'In Form submission : SAVE' );
				$recording = isset($_POST[ 'jco_recording_checkbox' ])?$_POST[ 'jco_recording_checkbox' ]:'off';
				update_option( 'jco_enqueue_recording', $recording);
				
				PC::debug( array('assets before submission'=> $this->enqueued_assets) );
				foreach ( $this->enqueued_assets as $type=>$assets ) {
					if ( ( $type != 'scripts' ) && ($type != 'styles') ) continue;
					foreach ( $assets as $handle=>$asset ) {
						PC::debug( array('Looping : type = ' => $type ) );
						PC::debug( array('Looping : asset = ' => $asset ) );
						PC::debug( array('Looping : handle = ' => $handle ) );
						$this->update_field($type, $handle, 'location');
						$this->update_field($type, $handle, 'minify');
					}
				}
				PC::debug( array('assets after submission'=> $this->enqueued_assets) );
				update_option( 'jco_enqueued_assets', $this->enqueued_assets);
		    $msg = 'save';
		}

		$url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );
		if ( ! isset ( $_POST['_wp_http_referer'] ) )
		    die( 'Missing target.' );

		wp_safe_redirect( $url );
		exit;
	}
	
	public function update_field( $type, $handle, $field ) {
		$input = $this->field_name($type, $handle, $field);
		if ( $_POST[ $input ] != $this->enqueued_assets[$type][$handle][$field] ) {
			$this->enqueued_assets[$type][$handle]['mods'][$field] = $_POST[ $input ];
			PC::debug( array('Asset field modified (mods) !' => $this->enqueued_assets[$type][$handle]) );
			PC::debug( array('$input' => $input ) );
			PC::debug( array('POST content for this field' => $_POST[ $input ] ) );
		}
		else {
			if ( isset( $this->enqueued_assets[$type][$handle]['mods'][$field]) ) {
				unset($this->enqueued_assets[$type][$handle]['mods'][$field]);
				PC::debug( array('Mod Field removed !' => $this->enqueued_assets[$type][$handle] ) );
			}
		}
	}

  public function load_option_page_cb() {
		//PC::debug('In load_option_page_cb function');
		if (isset ( $_GET['msg'] ) )
			add_action( 'admin_notices', array ( $this, 'render_msg' ) );
	}

	public function render_msg() {
		?>
		<div class="notice notice-success is-dismissible">
        <p><?php echo 'JCO settings update completed : ' . esc_attr( $_GET['msg'] ) ?></p>
    </div>
		<?php
	}


/* ENQUEUED SCRIPTS & STYLES MONITORING
-------------------------------------------------------*/

	public function auto_detect() {

		PC::debug('In auto detect !!!');

		foreach ($this->urls_to_request as $url) {
			$request = array(
				'url'  => $url,
				'args' => array(
					'timeout'   => 0.01,
					'blocking'  => false,
					'sslverify' => apply_filters('https_local_ssl_verify', true)
				)
			);

			wp_remote_get($request['url'], $request['args']);
		}
	}

	public function get_permalink_by_slug( $slug) {
    $permalink = null;
    $page = get_page_by_path( $slug );
    if( null != $page ) {
        $permalink = get_permalink( $page->ID );
    }
    return $permalink;
	}

	public function record_header_assets() {
		//PC::debug('In save enqueued scripts !!!');
		if (!in_array(get_permalink(), $this->enqueued_assets['pages']) ) {
			$this->enqueued_assets['pages'][] = get_permalink();
		}
		$this->record_enqueued_assets( false );
	}

	public function record_footer_assets() {
		$this->record_enqueued_assets( true );
	}

	public function record_enqueued_assets( $in_footer ) {
		PC::debug('In record enqueued assets !!!');
		global $wp_scripts;
		global $wp_styles;

		/* Select data source depending whether in header or footer */
		if ($in_footer) {
			//PC::debug('FOOTER record');
			//PC::debug(array( '$header_scripts' => $this->header_scripts ));
			$scripts=array_diff( $wp_scripts->done, $this->header_scripts );
			$styles=array_diff( $wp_styles->done, $this->header_styles );
			//PC::debug(array('$source'=>$source));
		}
		else {
			$scripts=$wp_scripts->done;
			$styles=$wp_styles->done;
			$this->header_scripts = $scripts;
			$this->header_styles = $styles;
			//PC::debug('HEADER record');
			//PC::debug(array('$source'=>$source));
		}

	  PC::debug(array('assets before update' => $this->enqueued_assets));
				
		$assets = array(
			'scripts'=>array(
					'handles'=>$scripts,
					'registered'=> $wp_scripts->registered),
			'styles'=>array(
					'handles'=>$styles,
					'registered'=> $wp_styles->registered),
			);	
			
		foreach( $assets as $type=>$asset ) {
			PC::debug( $type . ' recording');		
					
			foreach( $asset['handles'] as $handle ) {
				$obj = $asset['registered'][$handle];
				PC::debug(array('handle' => $handle));
				PC::debug( array('$obj'=>$obj) );
				$this->enqueued_assets[$type][$handle]['filename']=wp_make_link_relative( $obj->src );
				$this->enqueued_assets[$type][$handle]['location']=$in_footer?'footer':'header';
				$this->enqueued_assets[$type][$handle]['deps']=$obj->deps;
				$this->enqueued_assets[$type][$handle]['minify']=(strpos( $obj->src, '.min.' ) != false )?'yes':'no';
				PC::debug( array('enqueued asset'=>$this->enqueued_assets[$type][$handle]) );
			}
			
		}
		
//		PC::debug('Styles recording');		
//		foreach( $styles as $handle ) {
//			$obj = $wp_styles->registered [$handle];
//			PC::debug(array('handle' => $handle));
//			PC::debug( array('$obj'=>$obj) );
//			$this->enqueued_assets['styles'][$handle]['filename']=$obj->src;
//			$this->enqueued_assets['styles'][$handle]['location']=$in_footer?'footer':'header';
//			$this->enqueued_assets['styles'][$handle]['deps']=$obj->deps;
//			$this->enqueued_assets['styles'][$handle]['minify']=(strpos( $obj->src, '.min.' ) != false )?'yes':'no';
//		}
		
	  PC::debug(array('assets after update' => $this->enqueued_assets));
		update_option( 'jco_enqueued_assets', $this->enqueued_assets, true );

	}



}
