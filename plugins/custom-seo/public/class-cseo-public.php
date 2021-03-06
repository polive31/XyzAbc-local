<?php


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}


class CSEO_Public {

    public function foodiepro_do_breadcrumbs() {
        if (is_home() || is_front_page()) return;
        if (function_exists('yoast_breadcrumb')) {
            yoast_breadcrumb('<div class="breadcrumb"><span>', '</span></div>');
        }
    }

    public function yarpp_add_link_to_cornerstone_posts($params) {
        if ( is_single() && foodiepro_startsWith($params[0]['widget_id'],'yarpp_widget') ) {
            $post=get_post();

            $slug = CSEO_Assets::get_cornerstone_url( $post );

            if ($slug) {
                $url = foodiepro_get_permalink( array('slug' => $slug) );
                $params[0]['after_widget'] = '<p class="more-from-category"><a class="" id="" href="' . $url . '">' . __('More ideas', 'foodiepro') . '</a></p>';
            }

        }
        return $params;
    }

    // public function foodiepro_edit_breadcrumbs($link_output, $link)
    // {
    //     if ( $link['ptarchive']=='recipe') {
    //     }
    //     return $link_output;
    // }

    /* Exclude Multiple Taxonomies From Yoast SEO Sitemap */
    public function sitemap_exclude_taxonomy($value, $taxonomy)
    {
        $taxonomy_to_exclude = array('slider');
        if (in_array($taxonomy, $taxonomy_to_exclude)) return true;
    }

    // Capitalize SEO title
    public function wpseo_uppercase_title($title)
    {
        return ucfirst($title);
    }

    // Populate SEO meta if empty

    public function foodiepro_populate_metadesc($text)
    {
        if (empty($text)) {
            if (is_single()) {
                $text = get_the_excerpt();
            }
        }
        return $text;
    }

    // Add pinterest meta
    public function add_pinterest_meta() {
        echo '<meta name="p:domain_verify" content="c4a191084b3f5ef29b9df4a1a9f05aab" />';
    }


}
