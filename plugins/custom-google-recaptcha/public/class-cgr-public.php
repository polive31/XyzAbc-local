<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class CGR_Public {

    private $V2_PUBLIC_KEY;
    private $V2_PRIVATE_KEY;
    private $V3_PUBLIC_KEY;
    private $V3_PRIVATE_KEY;

    public function __construct()
    {
        $options = get_option('cgr_keys_array');
        $this->V2_PUBLIC_KEY = $options['v2_public'];
        $this->V2_PRIVATE_KEY = $options['v2_private'];
        $this->V3_PUBLIC_KEY = $options['v3_public'];
        $this->V3_PRIVATE_KEY = $options['v3_private'];
    }

    /* =================================================================*/
    /* = DISPLAY GOOGLE RECAPTCHA
    /* =================================================================*/
    public function display($classes = '') {
        ?>
        <div class="g-recaptcha <?php echo $classes; ?>" data-sitekey="<?php echo $this->v2key('public'); ?>"></div>
        <?php
    }

    /* =================================================================*/
    /* = VERIFY GOOGLE RECAPTCHA
	/* =================================================================*/
    public function verify() {
        $retries = 4;
        if (isset($_POST['g-recaptcha-response'])) {
            $captcha = $_POST['g-recaptcha-response'];
        }
        if (!$captcha) {
            return 'missing';
        }
        do {
            $ip = $_SERVER['REMOTE_ADDR'];
            $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $this->v2key('private') . "&response=" . $captcha . "&remoteip=" . $ip);
            $responseKeys = json_decode($response, true);
            if (intval($responseKeys["success"]) !== 1) {
                $result = 'success';
            } else {
                $result = 'fail';
            }
            --$retries;
        } while ($result == 'fail' && $retries > 0);
        return $result;
    }

    /* =================================================================*/
    /* = GETTERS
	/* =================================================================*/

    public function v2key( $type='public') {
        // return ($type=='private') ? get_option('cgr_keys_array[v2_private]') : get_option('cgr_keys_array[v2_public]');
        return ($type=='private') ? $this->V2_PRIVATE_KEY : $this->V2_PUBLIC_KEY;
    }

    public function v3key( $type='public' ) {
        // return ($type=='private') ? get_option('cgr_keys_array[v3_private]') : get_option('cgr_keys_array[v3_public]');
        return ($type=='private') ? $this->V3_PRIVATE_KEY : $this->V3_PUBLIC_KEY;
    }

    /* =================================================================*/
    /* = Assets management
    /* =================================================================*/
    public function register_recaptcha_script() {
        wp_register_script('g-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), '1.0.0');
    }

    // This function is to be called by any form needing the ccf captcha
    public static function enqueue_scripts() {
        wp_enqueue_script('g-recaptcha');
    }

}
