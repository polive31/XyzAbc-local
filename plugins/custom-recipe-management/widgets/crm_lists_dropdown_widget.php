<?php

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');


class CRM_Lists_Dropdown_Widget extends WP_Widget {

    public function __construct()
    {
        parent::__construct(
            'CRM_Lists_Dropdown_Widget',
            __( 'Recipe Lists Dropdown Widget', 'crm' ),
            array(
                'description' => __( 'Dropdown with cookbook recipes lists.', 'crm' )
            )
        );
    }

    public function widget( $args, $instance ) {
        // global $wp;

        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $args['before_widget'];
        echo $args['before_title'] . $title . $args['after_title'];

        $lists = get_query_var( 'list', false );

        ?>

        <label class="screen-reader-text" for="sort_dropdown"><?php echo $title;?></label>
        <!-- <div class="dropdown-select"> -->
        <select name="sort_dropdown" id="sort_dropdown" class="dropdown-select postform">

        <option value="none" class="separator"><?php echo __('Filter your favorite recipes...', 'crm');?></option>
        <option class="level-0" <?= $lists=='favorites'?'selected':''; ?> value="?list=favorites"><?php echo __('My favorite recipes', 'crm');?></option>
        <option class="level-0" <?= $lists=='wishlist'?'selected':''; ?>  value="?list=wishlist"><?php echo __('Recipes in my wishlist', 'crm');?></option>

        </select>


        <script type="text/javascript">
            /* <![CDATA[ */
            (function() {
                var dropdown=document.getElementById("sort_dropdown");
                function onDropDownChange() {
                    var choice = dropdown.options[dropdown.selectedIndex].value;
                    if ( choice != "none" ) {
                        location.href="<?= esc_url( strtok($_SERVER["REQUEST_URI"], '?') ); ?>?"+encodeURI(choice);
                    }
                }
                dropdown.onchange=onDropDownChange;
            })();
            /* ]]> */
        </script>

    <?php
	// Output end
	echo $args['after_widget'];
    }


    // Widget Backend
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) )
            $title = $instance[ 'title' ];
        else
            $title = __( 'New title', 'crm' );
    // Widget admin form
    ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
    <?php
    }

    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }

} // Class wpb_widget ends here

// Register and load the widget
function crm_lists_dropdown_widget_init() {
    register_widget( 'CRM_Lists_Dropdown_Widget' );
}
