<?php

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');


/* =================================================================*/
/* =                 MY FRIENDS WIDGET																	                       =*/
/* =================================================================*/


add_action( 'widgets_init', function(){
     register_widget( 'BP_My_Friends' );
});	

/**
 * Adds BP_My_Friends widget.
 */
class BP_My_Friends extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'bp_my_friends', // Base ID
			__('(Buddypress) My Friends', 'text_domain'), // Name
			array( 'description' => __( 'Displays a chosen number of my friends', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	
     	echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
		/* Code Start */
		
		if ( bp_has_members( 'type=newest&max=6&user_id=' . bp_loggedin_user_id() ) & is_user_logged_in() ) {
			echo	'<div class="avatar-block">';
			
			while ( bp_members() ) {
				bp_the_member();  
				echo '<div class="item-avatar">';
					//echo '<a href="' . bp_get_member_permalink() . '" title="' . bp_core_get_user_displayname(bp_get_member_user_id()) . '">';
					echo '<a href="' . bp_get_member_permalink() . '" title="' . bp_core_get_username(bp_get_member_user_id()) . '">';
					echo bp_member_avatar('type=full&width=82&height=82&id=square');
					echo '</a>';
				echo '</div>';
			}

			echo '</div>';
		}
		
		/* Code End */
		
		echo '<div class="clear"></div>';
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class BP_My_Friends



/* =================================================================*/
/* =                LATEST REGISTERED MEMBERS WIDGET               =*/
/* =================================================================*/

add_action( 'widgets_init', function(){
     register_widget( 'BP_Latest' );
});	

/**
 * Adds BP_Latest widget.
 */
class BP_Latest extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'bp_latest', // Base ID
			__('(Buddypress) Custom latest registered members', 'text_domain'), // Name
			array( 'description' => __( 'Displays latest registered members', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	
     	echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
		/* Code start */
		
		if ( bp_has_members( 'type=newest&max=' . $instance['limit'] ) ) {    
			echo '<div class="avatar-block">';
			while ( bp_members() ) {
				bp_the_member();
				
				/* Exclude admins from the output */
				$user_id = bp_get_member_user_id(); 
	   		$user = new WP_User( $user_id );
	   		if ( $user->roles[0] != 'administrator' && $user->roles[0] != 'pending') {
					echo '<div class="item-avatar">';
					//echo '<a href="' . bp_get_member_permalink() . '" title="' . bp_core_get_user_displayname(bp_get_member_user_id()) . '">';
					echo '<a href="' . bp_get_member_permalink() . '" title="' . bp_core_get_username(bp_get_member_user_id()) . '">';
					/*echo bp_member_avatar('type=full&width=100&height=100&id=square');*/
					echo bp_member_avatar('type=full&id=square');
					echo '</a>';
					echo '</div>';
				}
				
			}
			echo '</div>';
		}
		/* Code end */
		
	echo '<div class="clear"></div>';	
	echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		if ( isset( $instance[ 'title' ] ) ) 
			$title = $instance[ 'title' ];
		else 
			$title = __( 'New title', 'text_domain' );
		
		if ( isset( $instance[ 'limit' ] ) ) 
			$limit = $instance[ 'limit' ];
		else 
			$limit = 8;
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>">
				<?php _e( 'Number of users to show', 'foodiepro' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" step="1" min="-1" value="<?php echo (int)( $instance['limit'] ); ?>" />
	</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : '';

		return $instance;
	}

} // class BP_Latest


/* =================================================================*/
/* =                   WELCOME  WIDGET																	                       =*/
/* =================================================================*/
	
add_action( 'widgets_init', function(){
     register_widget( 'BP_Welcome' );
});	

/**
 * Adds BP_Welcome widget.
 */
class BP_Welcome extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'BP_Welcome', // Base ID
			__('(Buddypress) Welcome', 'text_domain'), // Name
			array( 'description' => __( 'Displays gravatar and name of current loggued-in user', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	
     	echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
		if ( is_user_logged_in() ) {
			
				echo '<div class="bp-login-widget-user-avatar">';
					echo '<a href="' . bp_loggedin_user_domain() . '">';
						echo bp_loggedin_user_avatar( 'type=full&width=150&height=150' ); 
					echo '</a>';
					//echo bp_core_get_userlink( bp_loggedin_user_id() );
				echo '</div>';
		}
		echo '<div class="clear"></div>';
		
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class BP_Welcome




/* =================================================================*/
/* =                 CUSTOM LOGIN WIDGET								     							                       =*/
/* =================================================================*/

/**
 * From : BuddyPress Core Login Widget.
 *
 * @package BuddyPress
 * @subpackage Core
 * @since 1.9.0
 */

add_action( 'widgets_init', function(){
     register_widget( 'BP_Custom_Login_Widget' );
});	 
 
 
class BP_Custom_Login_Widget extends WP_Widget {
	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {
		parent::__construct(
			false,
			_x( '(BuddyPress) Custom Log In', 'Title of the login widget', 'buddypress' ),
			array(
				'description'                 => __( 'Show a Log In form to logged-out visitors, and a Log Out link to those who are logged in, with full resolution avatar.', 'buddypress' ),
				'classname'                   => 'widget_bp_custom_login_widget buddypress widget',
				'customize_selective_refresh' => true,
			)
		);
	}
	/**
	 * Display the login widget.
	 *
	 * @since 1.9.0
	 *
	 * @see WP_Widget::widget() for description of parameters.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget settings, as saved by the user.
	 */
	public function widget( $args, $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		/**
		 * Filters the title of the Login widget.
		 *
		 * @since 1.9.0
		 * @since 2.3.0 Added 'instance' and 'id_base' to arguments passed to filter.
		 *
		 * @param string $title    The widget title.
		 * @param array  $instance The settings for the particular instance of the widget.
		 * @param string $id_base  Root ID for all widgets of this type.
		 */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		echo $args['before_widget'];
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; ?>

		<?php if ( is_user_logged_in() ) : 
			echo '<div class="wrap" id="logged-in">';?>

			<?php
			/**
			 * Fires before the display of widget content if logged in.
			 *
			 * @since 1.9.0
			 */
			do_action( 'bp_before_login_widget_loggedin' ); ?>

			<div class="bp-login-widget-user-avatar">
				<a href="<?php echo bp_loggedin_user_domain(); ?>">
					<?php bp_loggedin_user_avatar( 'type=full&width=150&height=150' ); ?>
				</a>
			</div>

			<div class="bp-login-widget-user-links">
				<div class="bp-login-widget-user-link"><?php echo bp_core_get_userlink( bp_loggedin_user_id() ); ?></div>
				<div class="bp-login-widget-user-logout"><a class="logout" href="<?php echo wp_logout_url( bp_get_requested_url() ); ?>"><?php _e( 'Log Out', 'buddypress' ); ?></a></div>
			</div>

			<?php
			/**
			 * Fires after the display of widget content if logged in.
			 *
			 * @since 1.9.0
			 */
			do_action( 'bp_after_login_widget_loggedin' ); 
			echo '</div>';?>

		<?php else : 
			echo '<div class="wrap" id="logged-out">';?>

			<?php
			/**
			 * Fires before the display of widget content if logged out.
			 *
			 * @since 1.9.0
			 */
			do_action( 'bp_before_login_widget_loggedout' ); ?>

			<form name="bp-login-form" id="bp-login-widget-form" class="standard-form" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
				<label for="bp-login-widget-user-login"><?php _e( 'Username', 'buddypress' ); ?></label>
				<input type="text" name="log" id="bp-login-widget-user-login" class="input" value="" />

				<label for="bp-login-widget-user-pass"><?php _e( 'Password', 'buddypress' ); ?></label>
				<input type="password" name="pwd" id="bp-login-widget-user-pass" class="input" value="" <?php bp_form_field_attributes( 'password' ) ?> />

				<div class="forgetmenot"><label for="bp-login-widget-rememberme"><input name="rememberme" type="checkbox" id="bp-login-widget-rememberme" value="forever" /> <?php _e( 'Remember Me', 'buddypress' ); ?></label></div>

				<input type="submit" name="wp-submit" id="bp-login-widget-submit" value="<?php esc_attr_e( 'Log In', 'buddypress' ); ?>" />

				<?php if ( bp_get_signup_allowed() ) : ?>

					<span class="bp-login-widget-register-link"><a href="<?php echo esc_url( bp_get_signup_page() ); ?>" title="<?php esc_attr_e( 'Register for a new account', 'buddypress' ); ?>"><?php _e( 'Register', 'buddypress' ); ?></a></span>

				<?php endif; ?>

				<?php
				/**
				 * Fires inside the display of the login widget form.
				 *
				 * @since 2.4.0
				 */
				do_action( 'bp_login_widget_form' ); ?>

			</form>

			<?php
			/**
			 * Fires after the display of widget content if logged out.
			 *
			 * @since 1.9.0
			 */
			do_action( 'bp_after_login_widget_loggedout' ); 
			echo '</div>';?>
			
		 
		<?php endif;
		
		echo $args['after_widget'];
	}
	/**
	 * Update the login widget options.
	 *
	 * @since 1.9.0
	 *
	 * @param array $new_instance The new instance options.
	 * @param array $old_instance The old instance options.
	 * @return array $instance The parsed options to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance             = $old_instance;
		$instance['title']    = isset( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
	/**
	 * Output the login widget options form.
	 *
	 * @since 1.9.0
	 *
	 * @param array $instance Settings for this widget.
	 * @return void
	 */
	public function form( $instance = array() ) {
		$settings = wp_parse_args( $instance, array(
			'title' => '',
		) ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'buddypress' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" /></label>
		</p>

		<?php
	}
}