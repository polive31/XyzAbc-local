<?php

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');


/* =================================================================*/
/* =                 MY FRIENDS WIDGET			
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

