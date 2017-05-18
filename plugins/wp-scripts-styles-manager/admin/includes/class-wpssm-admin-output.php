<?php

class WPSSM_Admin_Output {
 	
	use Utilities;	
 	
 	/* Class local attributes */
 	private $type;
 	private $asset_notice;

 	/* Class arguments */
 	private $plugin_name;
 	private $sizes;
 	private $form_action;
 	private $nonce;
 	
 	/* Objects */
 	private $assets;
  
  public function __construct( WPSSM_Options_Assets $assets, $args ) {
		WPSSM_Debug::log('*** In WPSSM_Admin_Output __construct ***' );		  	
  	$this->hydrate_args( $args );
  	$this->assets = new WPSSM_Assets_Display( $assets, $args );
  	$this->type = $this->assets->get_display_attr('type');
  }


/* OPTIONS PAGE
--------------------------------------------------------------*/	

	public function options_page( $page_slug ) {
	    // check user capabilities
	    if (!current_user_can('manage_options')) return;
			$referer = menu_page_url( $this->plugin_name, FALSE  );
			//$page_slug = $this->config_settings_pages[$this->active_tab]['slug'];
			?>

	    <div class="wrap">
	        <h1><?= esc_html(get_admin_page_title()); ?></h1>
	        
						<h2 class="nav-tab-wrapper">
						<a href="?page=<?php echo $this->plugin_name;?>&tab=general" class="nav-tab <?php echo $this->type == 'general' ? 'nav-tab-active' : ''; ?>">General Settings</a>
						<a href="?page=<?php echo $this->plugin_name;?>&tab=scripts" class="nav-tab <?php echo $this->type == 'scripts' ? 'nav-tab-active' : ''; ?>">Scripts</a>
						<a href="?page=<?php echo $this->plugin_name;?>&tab=styles" class="nav-tab <?php echo $this->type == 'styles' ? 'nav-tab-active' : ''; ?>">Styles</a>
						</h2>
	        
		        <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
	        		<?php	
							$this->form_buttons($referer);
							settings_fields( $page_slug );
							do_settings_sections( $page_slug );
							$this->form_buttons($referer);
	        		?>	
	        
		        </form>  
	    </div>
	    <?php
	}
		
	protected function form_buttons($referer) { 
		?>
		<!-- Output form buttons -->
	  <table class="button-table" col="2">
	    <tr>
				<input type="hidden" name="action" value="<?php echo $this->form_action; ?>">
				<?php wp_nonce_field( $this->form_action, $this->nonce, FALSE ); ?>
				<input type="hidden" name="_wpssm_http_referer" value="<?php echo $referer; ?>">
				<input type="hidden" name="_wpssm_active_tab" value="<?php echo $this->type; ?>">

	    	<td><?php submit_button( 'Save ' . $this->type . ' settings', 'primary', 'wpssm_save', true, array('tabindex'=>'1') );?> </td>
	    	<?php if ($this->type != 'general') { ?>
	    	<td><?php submit_button( 'Reset ' . $this->type . ' settings', 'secondary', 'wpssm_reset', true, array('tabindex'=>'2') );?> </td>
	    	<?php } ?>
	    	<td><?php submit_button( 'Delete everything', 'delete', 'wpssm_delete', true, array('tabindex'=>'3') );?> </td>
	  	</tr>
	  </table>
	<?php 
	}
	
  
/* COMMON 
--------------------------------------------------------------------*/
	public function section_headline( $section ) {
		//WPSSM_Debug::log('In section callback');
	}
    


/* GENERAL SETTINGS PAGE
--------------------------------------------------------------------*/
	public function pages_list() {
		if ( $this->type != 'general') return false;
		//WPSSM_Debug::log('In WPSSM_Output pages_list(), $this->assets ', $this->assets->get_assets('pages') );
		foreach ($this->assets->get('pages') as $page) {
			echo '<p>' . $page[0] . ' on ' . $page[1] . '</p>';
		}
	}
	

	public function toggle_switch( $input_name, $value ) {
		WPSSM_Debug::log( 'in output toggle switch for ' . $input_name , $value);
		$checked = ( $value == 'on')?'checked="checked"':'';
		?>
		<label class="switch">
  	<input type="checkbox" name="<?php echo $input_name;?>_checkbox" <?php echo $checked;?> value="on">
  	<div class="slider round"></div>
		</label>
		<?php
	}
	

/* SCRIPTS AND STYLES PAGES
--------------------------------------------------------------------*/  

	public function header_items_list() {
		$this->items_list( 'header' );
	}
	
	public function footer_items_list() {
		$this->items_list( 'footer' );
	}
	
	public function async_items_list() {
		$this->items_list( 'async' );
	}
	
	public function disabled_items_list() {
		$this->items_list( 'disabled' );
	}

	public function items_list( $location ) {
		$sort_list = $this->assets->get_sort_list( $location );
		WPSSM_Debug::log('In WPSSM_Output items_list() $sorted_list : ', $sort_list);			
		?><table class="enqueued-assets"><?php
			$this->item_headline();
	    foreach ($sort_list as $handle => $priority ) {
				WPSSM_Debug::log('Asset in WPSSM_Output->items_list() loop for ' . $location . ' : ', $handle );			
				$this->item_content( $location, $handle );  
	    }
    ?></table><?php
	}
	

	public function item_headline() {
		?>
    	<tr>
    		<th> handle </th>
    		<th> priority </th>
    		<!--<th> Dependencies </th>-->
    		<th> Dependents </th> 
    		<th> File size </th>
    		<th> Location </th>
    		<th> Minify </th>
    	</tr>	
		<?php
	}

	public function item_content( $location, $handle ) {
    	$asset = $this->assets->get_displayed( $location, $handle );
    	$filename 		= $asset['filename'];
    	$dependencies = $asset['dependencies'];
    	$dependents 	= $asset['dependents'];
    	$priority 		= $asset['priority'];
    	$location 		= $asset['location'];
    	$minify 			= $asset['minify'];
    	$size 				= $asset['size'];
	    	
	    $asset_is_minified = ( $minify == 'yes')?true:false; 
	    $already_minified_msg = __('This file is already minimized within its plugin', 'jco');
	    
	    
		?>
		   	<tr class="enqueued-asset <?php echo $this->type;?>" id="<?php echo $handle;?>">
	    	<td class="handle" title="<?php echo $filename;?>"><?php echo $handle;?><?php $this->asset_notice( $handle );?></td>
	    	
	    	<td><?php echo $priority;?></td>
	    	
	    	<td class="dependents"><?php foreach ($dependents as $dep) {echo $dep . '<br>';}?></td>
	    	
	    	<td class="size" title="<?php echo $filename;?>"><?php echo size_format( $size );?></td>
	    	
	    	<td class="location <?php echo $this->classmod( $handle, 'location');?>">
	    		<select data-dependencies='<?php echo json_encode($dependencies);?>' data-dependents='<?php echo json_encode($dependents);?>' id="<?php echo $handle;?>" class="asset-setting location <?php echo $this->type;?>" name="<?php echo $this->get_field_name($type, $handle, 'location');?>">
  					<option value="header" <?php echo ($location=='header')?'selected':'';?> >header</option>
  					<option value="footer" <?php echo ($location=='footer')?'selected':'';?> >footer</option>
  					<option value="async" <?php echo ($location=='async')?'selected':'';?> >asynchronous</option>
  					<option value="disabled" <?php echo ($location=='disabled')?'selected':'';?>>disabled</option>
					</select>
				</td>
				
				<td class="minify <?php echo $this->classmod( $handle, 'minify');?>">
	    		<select id="<?php echo $handle;?>" class="asset-setting minify <?php echo $this->type;?>" <?php echo ($asset_is_minified)?'disabled':'';?> title="<?php echo ($asset_is_minified)?$already_minified_msg:'';?>" name="<?php echo $this->get_field_name($type, $handle, 'minify');?>">
  					<option value="no" <?php echo ($minify=='no')?'selected':'';?>  >no</option>
  					<option value="yes" <?php echo ($minify=='yes')?'selected':'';?> >yes</option>
					</select>
				</td>
    	
    	</tr>
		<?php
	}


/* HELPERS
--------------------------------------------------------------*/		
	private function classmod( $handle, $field ) {
		return $this->assets->is_mod( $this->type, $handle, $field )?'modified':'';
	}



/* ASSET WARNING/ADVICE NOTICES
--------------------------------------------------------------*/		
	
	private function asset_notice( $handle ) {
		
		$size= $this->assets->get_value( $this->type, $handle, 'size');
		//WPSSM_Debug::log(array('size : '=>$size));
		$is_minified = $this->assets->get_value( $this->type, $handle, 'minify') == 'yes';
		//WPSSM_Debug::log(array('is_minified: '=>$is_minified));
		$in_footer = ( $this->assets->get_value( $this->type, $handle, 'location') == 'footer');
		
		$this->reset_asset_notice();
		if (!$is_minified) {
			if ( $size > $this->sizes['large'] ) {
				$level = 'issue';
				$msg = __('This file is large and not minified : minification highly recommended', 'jco');	
				$this->enqueue_asset_notice( $msg, $level);
			}
			elseif ( $size != 0 ) {
				$level = 'warning';
				$msg = __('This file is not minified : minification recommended', 'jco');	
				$this->enqueue_asset_notice( $msg, $level);
			}
		}

		if ( ( $size > $this->sizes['large'] ) && ( !$in_footer ) ) {
			$level = 'issue';
			$msg = __('Large files loaded in the header will slow down page display : make asynchronous, loading in footer or at least conditional enqueue recommended', 'jco');			
			$this->enqueue_asset_notice( $msg, $level);
		}	
		
		if ( ( $size < $this->sizes['small'] ) && (!isset( $asset['in_group']) ) ) {
			$level = 'warning';
			$msg = __('This file is small and requires a specific http request : it is recommended to inline it, or to group it with other files', 'jco');			
			$this->enqueue_asset_notice( $msg, $level);
		}	
		echo $this->asset_notice;		
	}
	
	public function get_field_name( $type, $handle, $field ) {
		return  $type . '_' . $handle . '_' . $field;
	}	

	private function reset_asset_notice() {
		$this->asset_notice='';
	}
	
	private function enqueue_asset_notice( $msg, $level) {
		if ($msg != '') {
			$this->asset_notice .= '<i class="user-notification" id="' . $level . '" title="' . $msg . '"></i>';
		}		
	}
	
	
}