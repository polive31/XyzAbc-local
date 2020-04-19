<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRM_Ingredient {

    const UNITS_LIST = array(
        array('g'               , 'g'),
        array('kg'              , 'kg'),
        array('ml'              , 'ml'),
        array('cl'              , 'cl'),
        array('dl'              , 'dl'),
        array('litre'           , 'litres'),
        array('cuillère à café' , 'cuillères à café'),
        array('cuillère à soupe', 'cuillères à soupe'),
        array('bol'   		    , 'bols'),
        array('boîte'   	    , 'boîtes'),
        array('botte'   	    , 'bottes'),
        array('bouquet'   	    , 'bouquets'),
        array('branche'   	    , 'branches'),
        array('brin'   		    , 'brins'),
        array('bulbe'   	    , 'bulbes'),
        array('cube' 		    , 'cubes'),
        array('capsule' 	    , 'capsules'),
        array('cerneau' 	    , 'cerneaux'),
        array('doigt'		    , 'doigts'),
        array('étoile'		    , 'étoiles'),
        array('feuille' 	    , 'feuilles'),
        array('filet'   	    , 'filets'),
        array('goutte'		    , 'gouttes'),
        array('gousse' 		    , 'gousses'),
        array('noix'   		    , 'noix'),
        array('pavé' 		    , 'pavés'),
        array('pincée' 		    , 'pincées'),
        array('poignée'		    , 'poignées'),
        array('pointe'		    , 'pointes'),
        array('sachet'		    , 'sachets'),
        array('tasse'   	    , 'tasses'),
        array('tige'   		    , 'tiges'),
        array('tubercule'       , 'tubercules'),
        array('tranche'   	    , 'tranches'),
        array('bâton' 		    , 'bâtons'),
        array('verre'   	    , 'verres'),
    );


	/* =================================================================*/
	/* = SHORTCODES
	/* =================================================================*/

    public function display_ingredient_shortcode( $options ) {
        $options = shortcode_atts( array(
            'amount'            => '',
            'amount_normalized' => '',
            'unit'              => '',
            'ingredient'        => '',
            'notes'             => '',
            'links'             => 'no',
            'target'            => 'screen',
        ), $options );
        $html = CRM_Assets::get_template_part('ingredients', 'ingredient', array('args' => $options) );

        return $html;
    }

    public function display_ingredient_months_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => false,
            'currentmonth' => false, // displays an arrow showing the current month
        ), $atts );

        if ($atts['id']) {
            $ingredient_id = $atts['id'];
        }
        elseif (is_tax('ingredient')) {
            $ingredient_id = get_queried_object()->slug;
        }
        else
            return false;

        $ingredient_months = get_term_meta($ingredient_id, 'month', true );
        if ( empty($ingredient_months) ) return '';

        $html = '<h2>' . __('Harvest Period','crm') . '</h2>';
        $html .= '<table class="ingredient-months">';
        $html .= '<tr>';
        $i=1;

        $months = CRM_Ingredient_Month::$MONTHS;
        foreach (  $months as $month ) {
            $available = in_array( $i, $ingredient_months )?'available':'';
            $html .= '<td class="' . $available . '" title="' . $month . '">' . $month[0] . '</td>';
            $i++;
        }
        $html .= '</tr>';
        $html .= '</table>';
        return $html;
    }


    public static function get_ingredient_parts( $args, $ratio=1 ) {
        $parts = array();

        // Fraction
        $parts['fraction'] = (strpos($args['amount'], '/') === false) ? false : true;

        // Normalized amount
        if ( $parts['fraction'] ) {
            // Let's not take risks and recompute amount_normalized
            $parts['amount_normalized'] = self::normalize_amount($args['amount']);
        }
        elseif ( empty($parts['amount_normalized']) ) {
            $parts['amount_normalized'] = floatval($args['amount']);
        }

        // Amount
        $parts['amount'] = round(floatval($ratio) * floatval($parts['amount_normalized']), 1);

        // Unit
        $parts['unit'] = self::output_unit($args['unit'], $parts['amount']);

        // Of
        $parts['of'] = '';
        if ($parts['unit'] != '') {
            if (initial_is_vowel($args['ingredient']))
                $parts['of'] = _x('of ', 'vowel', 'crm');
            else
                $parts['of'] = _x('of ', 'consonant', 'crm');
        }

        // Taxonomy
        $taxonomy = get_term_by('name', $args['ingredient'], 'ingredient');
        $parts['tax'] = ($taxonomy && is_object($taxonomy)) ? $taxonomy->slug : false;

        // Ingredient Plural Name
        $plural = $parts['tax'] ? WPURP_Taxonomy_MetaData::get('ingredient', $parts['tax'], 'plural') : '';
        $parts['plural'] = is_array($plural) ? false : $plural;

        // Ingredient Name (singular or plural depending on the unit & amount)
        $parts['ingredient']=($parts['plural'] && self::is_plural($parts['amount'], $parts['unit'])) ? $parts['plural'] : $args['ingredient'];

        return $parts;
    }

    /* =================================================================*/
    /* = HELPERS
    /* =================================================================*/

    /**
     * get_units
     *
     * @param  mixed $plural
     * @return array
     */
    public static function get_units( $plural=false ) {
        $column=$plural?1:0;
        return array_column(self::UNITS_LIST,$column);
        // echo '<pre>' . print_r( self::$UNITS ) . '</pre>';
    }

    /**
     *
     *
     * @param  mixed $name
     * @param  mixed $amount
     * @return void
     */
    public static function output_unit( $name, $amount ) {
        if ( floatval($amount) <= 1 ) return $name;

        $index = array_search( $name, self::get_units( false ), true );

        $plural = ($index!==false)?self::UNITS_LIST[$index][1]:$name;
        return $plural;
    }

    public static function is_plural( $amount, $unit ) {
    	$plural = $amount > 1 || $unit != '' || (empty($amount) && empty($unit));
    	return $plural;
    }


	/* =================================================================*/
	/* = CALLBACKS
	/* =================================================================*/
    public function query_current_month_ingredients( $args, $instance) {
        $args['meta_query'] = array(
                'key'     => 'mykey',     // Adjust to your needs!
                'value'   => 'myvalue',   // Adjust to your needs!
                'compare' => '=',         // Default
        );
    }

	/* =================================================================*/
	/* = AJAX CALLBACKS
    /* =================================================================*/
    public function ajax_ingredient_preview()
    {
        if (!check_ajax_referer('preview_ingredient', 'security', false)) {
            echo('Nonce not recognized');
            die();
        }

        if (!is_user_logged_in()) {
            echo('User not logged-in');
            die();
        }

        if (!isset($_POST['ingredient_data'])) return false;
        $params = array();
        parse_str($_POST['ingredient_data'], $params);
        if (is_array($params)) {
            $params=reset($params);
            if (is_array($params)) {
                $ingredient=reset($params);
            }
            else {
                echo('reset($params) is not an array');
                die();
            }
        }
        else {
            echo('$params is not an array');
            die();
        }

        if (!is_array($ingredient)) {
            echo('No ingredient provided');
            die();
        }

        if (!isset($ingredient['ingredient']) || empty($ingredient['ingredient']) ) {
            echo('Ingredient name not provided');
            die();
        }

        if (empty($_POST['recipe_id'])) {
            echo('No recipe id provided');
            die();
        }

        $ingredient['links'] = 'no';
        // $ingredient_save = self::save($args);

        $target='screen';
        $args=compact('ingredient','target');
        $ingredient_preview = CRM_Assets::get_template_part('ingredients', 'ingredient', $args );
        if (!$ingredient_preview) {
            echo('Ingredient display failed' );
            die();
        }
        else {
            wp_send_json_success(array(
                // 'ingredientSave' => $ingredient_save,
                'msg' => $ingredient_preview ));
        }

    }

	/* =================================================================*/
	/* = MATH HELPERS
    /* =================================================================*/

    /**
     * Get normalized amount. 0 if not a valid amount.
     *
     * @param $amount       Amount to be normalized
     * @return int
     */
    public static function normalize_amount($amount)
    {
        if (is_null($amount) || trim($amount) == '') {
            return 0;
        }

        // Replace unicode fractions
        $unicode_map = array(
            '00BC' => ' 1/4', '00BD' => ' 1/2', '00BE' => ' 3/4', '2150' => ' 1/7',
            '2151' => ' 1/9', '2152' => ' 1/10', '2153' => ' 1/3', '2154' => ' 2/3',
            '2155' => ' 1/5', '2156' => ' 2/5', '2157' => ' 3/5', '2158' => ' 4/5',
            '2159' => ' 1/6', '215A' => ' 5/6', '215B' => ' 1/8', '215C' => ' 3/8',
            '215D' => ' 5/8', '215E' => ' 7/8'
        );

        foreach ($unicode_map as $unicode => $normal) {
            $amount = preg_replace('/\x{' . $unicode . '}/u', $normal, $amount);
        }

        // Treat " to " as a dash for ranges
        $amount = str_ireplace(' to ', '-', $amount);

        $amount = preg_replace("/[^\d\.\/\,\s-–—]/", "", $amount); // Only keep digits, comma, point, forward slash, space and dashes

        // Replace en and em dash with a normal dash
        $amount = str_replace('–', '-', $amount);
        $amount = str_replace('—', '-', $amount);

        // if( WPUltimateRecipe::option( 'recipe_adjustable_servings_hyphen', '1' ) != '1' ) {
        // $amount = str_replace( '-', ' ', $amount );
        // }

        // Only take first part if we have a dash (e.g. 1-2 cups)
        $parts = explode('-', $amount);
        $amount = $parts[0];

        // If spaces treat as separate amounts to be added (e.g. 2 1/2 cups = 2 + 1/2)
        $parts = explode(' ', $amount);

        $float = 0.0;
        foreach ($parts as $amount) {
            $separator = self::find_separator($amount);

            switch ($separator) {
                case '/':
                    $amount = str_replace('.', '', $amount);
                    $amount = str_replace(',', '', $amount);
                    $parts = explode('/', $amount);

                    $denominator = floatval($parts[1]);
                    if ($denominator == 0) {
                        $denominator = 1;
                    }

                    $float += floatval($parts[0]) / $denominator;
                    break;
                case '.':
                    $amount = str_replace(',', '', $amount);
                    $float += floatval($amount);
                    break;
                case ',':
                    $amount = str_replace('.', '', $amount);
                    $amount = str_replace(',', '.', $amount);
                    $float += floatval($amount);
                    break;
                default:
                    $float += floatval($amount);
            }
        }

        return $float;
    }

    /**
     * Pick a separator for the amount
     * Examples:
     * 1/2 => /
     * 1.123,42 => ,
     * 1,123.42 => .
     *
     * @param $string
     * @return string
     */
    private static function find_separator($string)
    {
        $slash = strrpos($string, '/');
        $point = strrpos($string, '.');
        $comma = strrpos($string, ',');

        if ($slash) {
            return '/';
        } else {
            if (!$point && !$comma) {
                return '';
            } else if (!$point && $comma) {
                return ',';
            } else if ($point && !$comma) {
                return '.';
            } else if ($point > $comma) {
                return '.';
            } else {
                return ',';
            }
        }
    }


}
