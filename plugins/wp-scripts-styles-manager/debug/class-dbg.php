<?php
/**
 * Plugin Name: PHP Output To Console
 * * Author: Better WP Solutions
 * License:     GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WPSSM_Debug {
		
	const ON = true;
	const PHP_CONSOLE = true;
  private static $output = '';
	
	public function __construct() {
		//add_action( 'print_footer_scripts', array($this, 'output_debug_buffer') );
		//add_action( 'admin_print_footer_scripts', array($this, 'output_debug_buffer') );
	}
	
	public static function output_debug_buffer() {
		?>
		<script id="DBGlog" type="text/javascript">
			<?php echo self::$output;?>
		</script>
		<?php
		//PC::debug(array('In wp head output_buffer :'=>self::$output));
	}
	
	public static function error($code, $msg, $file, $line) {
		self::log($msg, false, $code);
	}

	public static function log($msg, $var=false, $type='DEBUG' ) {
		if (!self::ON) return;	
		if ( self::PHP_CONSOLE && class_exists( 'PC' )) {
			if ($var==false) PC::debug($msg);
			else PC::debug(array($msg=>$var));
		}
		elseif ( !self::PHP_CONSOLE ) {
			if ( defined( 'DOING_AJAX' ) || strstr($_SERVER['REQUEST_URI'], 'admin-post.php') ) return; //Prevents blocking of post submissions/ajax requests
			if ( !is_array($var) ) $var = '"' . $var . '"';
			if ( is_array($var) ) $var = json_encode($var);
			$style=( $type=='DEBUG' )?'blue':'red';
			self::$output='console.log("%c' . $type . '%c ' . $msg . '", "border-radius:4px;padding:2px 4px;background:' . $style . ';color:white", "color:blue");';  
			self::$output.='console.log(' . $var . ');';
			self::output_debug_buffer();
		}
	}
	
}