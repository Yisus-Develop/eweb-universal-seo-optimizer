<?php
/**
 * EWEB Universal SEO Config Handler
 * Generic configuration system that adapts to any WordPress site
 * 
 * This file provides the generic configuration system that can be customized
 * per project without requiring site-specific file names.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * EWEB_Universal_SEO_Config_Handler class
 * Handles generic configuration that adapts to any WordPress site
 */
class EWEB_Universal_SEO_Config_Handler {

    private static $instance = null;
    private $project_config = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    private function init() {
        add_action('init', array($this, 'load_project_config'));
    }

    public function load_project_config() {
        // Generic configuration based on current site
        $this->project_config = array(
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url(),
            'site_description' => get_bloginfo('description'),
            'primary_keywords' => $this->get_site_specific_keywords(),
            'target_audience' => $this->get_target_audience(),
            'content_strategy' => $this->get_content_strategy(),
            'local_seo_enabled' => $this->should_enable_local_seo(),
            'local_business_type' => $this->get_local_business_type(),
            'local_business_name' => get_bloginfo('name'),
            'social_profiles' => $this->get_social_profiles(),
            'tracking_enabled' => true,
            'analytics_id' => get_option('eweb_analytics_id', ''),
            'search_console_verification' => get_option('eweb_search_console_verification', ''),
            'site_verification_codes' => array(
                'bing' => get_option('eweb_bing_verification', ''),
                'pinterest' => get_option('eweb_pinterest_verification', ''),
                'yandex' => get_option('eweb_yandex_verification', '')
            ),
            'custom_og_settings' => array(
                'og_image_default' => get_site_icon_url() ?: get_template_directory_uri() . '/images/default-og.jpg',
                'og_type' => 'website',
                'og_locale' => get_locale()
            ),
            'content_enhancement_settings' => array(
                'add_related_content' => true,
                'enable_author_schema' => true,
                'add_breadcrumb_schema' => true,
                'use_custom_breadcrumb' => true
            ),
            'technical_seo_settings' => array(
                'enable_amp' => false,
                'enable_rss_optimization' => true,
                'optimize_images' => true,
                'minify_css_js' => true,
                'enable_caching_hints' => true
            ),
            'crawl_optimization' => array(
                'optimize_robots_txt' => true,
                'optimize_sitemap' => true,
                'enable_hreflang' => false,
                'optimize_internal_linking' => true
            ),
            'monitoring_settings' => array(
                'track_core_web_vitals' => true,
                'monitor_broken_links' => true,
                'check_schema_errors' => true,
                'performance_reporting' => true
            )
        );
        
        // Allow custom configuration per project
        $custom_config = get_option('eweb_universal_seo_config', array());
        $this->project_config = array_merge($this->project_config, $custom_config);
        
        // Apply the configuration
        add_filter('eweb_universal_seo_config', array($this, 'get_config'));
    }

    public function get_config($config) {
        return array_merge($config, $this->project_config);
    }

    private function get_site_specific_keywords() {
        // Generic keywords based on site characteristics
        $keywords = array();
        $description = get_bloginfo('description');
        
        if (stripos($description, 'shop') !== false || stripos($description, 'store') !== false) {
            $keywords = array('online store', 'ecommerce', 'shopping', 'buy online');
        } elseif (stripos($description, 'blog') !== false || stripos($description, 'news') !== false) {
            $keywords = array('blog', 'news', 'articles', 'information');
        } elseif (stripos($description, 'education') !== false || stripos($description, 'learn') !== false) {
            $keywords = array('education', 'learning', 'courses', 'tutorials');
        } else {
            $keywords = array('content', 'information', 'blog', 'website');
        }
        
        return apply_filters('eweb_default_keywords', $keywords);
    }

    private function get_target_audience() {
        $description = get_bloginfo('description');
        
        if (stripos($description, 'business') !== false) {
            return 'business professionals';
        } elseif (stripos($description, 'education') !== false) {
            return 'students and educators';
        } elseif (stripos($description, 'health') !== false) {
            return 'health conscious individuals';
        } else {
            return 'general audience';
        }
    }

    private function get_content_strategy() {
        $post_count = wp_count_posts();
        if ($post_count->publish > 100) {
            return 'blog';
        } elseif (get_option('woocommerce_status') == 'active') {
            return 'ecommerce';
        } else {
            return 'static';
        }
    }

    private function should_enable_local_seo() {
        $address_info = get_option('eweb_local_address', '');
        return !empty($address_info);
    }

    private function get_local_business_type() {
        $description = get_bloginfo('description');
        
        if (stripos($description, 'restaurant') !== false) {
            return 'Restaurant';
        } elseif (stripos($description, 'hotel') !== false) {
            return 'Hotel';
        } elseif (stripos($description, 'clinic') !== false) {
            return 'MedicalBusiness';
        } else {
            return 'LocalBusiness';
        }
    }

    private function get_local_business_name() {
        return get_bloginfo('name');
    }

    private function get_social_profiles() {
        return array(
            'facebook' => get_option('eweb_facebook_url', ''),
            'twitter' => get_option('eweb_twitter_url', ''),
            'instagram' => get_option('eweb_instagram_url', ''),
            'linkedin' => get_option('eweb_linkedin_url', ''),
            'youtube' => get_option('eweb_youtube_url', ''),
            'pinterest' => get_option('eweb_pinterest_url', '')
        );
    }
}

// Initialize the config handler
add_action('plugins_loaded', array('EWEB_Universal_SEO_Config_Handler', 'get_instance'));