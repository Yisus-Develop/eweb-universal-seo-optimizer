<?php
/**
 * EWEB Project-Specific Configuration Template
 * 
 * This is a template file for project-specific configurations.
 * To use this for your specific site:
 * 
 * 1. Copy this file to your theme's directory or create a child plugin
 * 2. Customize the values according to your specific site needs
 * 3. Rename it to your preference (e.g., theme-seo-config.php)
 * 4. Include it in your theme's functions.php or in your custom plugin
 * 
 * Do not include your actual site name in the file to maintain reusability.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Example of how to customize configuration for any site
// This function can be adapted for any WordPress site without naming the file after the site

function configure_eweb_seo_for_current_site() {
    // Configuration array that adapts to the current site
    $current_site_config = array(
        // These values would be adjusted for your specific site
        'primary_keywords' => array(
            // Add your site-specific primary keywords here
            // Example: 'yoursite keyword 1', 'yoursite keyword 2', 'yoursite keyword 3'
        ),
        'secondary_keywords' => array(
            // Add your site-specific secondary keywords here
        ),
        'target_audience' => array(
            // Define your target audience
            // Example: 'your target demographic'
        ),
        'content_strategy' => 'blog', // 'blog', 'ecommerce', 'education', 'static'
        'local_seo_enabled' => false, // Set to true if you have local business info
        'local_business_type' => '', // e.g., 'Restaurant', 'MedicalBusiness', 'LocalBusiness'
        'local_business_name' => get_bloginfo('name'), // Usually should match site name
        'social_profiles' => array(
            'facebook' => '', // Add your social URLs
            'twitter' => '',
            'instagram' => '',
            'linkedin' => '',
            'youtube' => '',
            'pinterest' => ''
        ),
        'tracking_enabled' => true,
        'analytics_id' => '', // Add your Google Analytics ID
        'search_console_verification' => '', // Add your verification code
        'site_verification_codes' => array(
            'bing' => '', // Add verification codes as needed
            'pinterest' => '',
            'yandex' => ''
        ),
        'custom_og_settings' => array(
            'og_image_default' => get_site_icon_url() ?: get_template_directory_uri() . '/images/og-default.jpg',
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
            'enable_hreflang' => false, // Set to true if multilingual
            'optimize_internal_linking' => true
        ),
        'monitoring_settings' => array(
            'track_core_web_vitals' => true,
            'monitor_broken_links' => true,
            'check_schema_errors' => true,
            'performance_reporting' => true
        )
    );
    
    // Apply the configuration
    add_filter('eweb_universal_seo_config', function($config) use ($current_site_config) {
        return array_merge($config, $current_site_config);
    });
    
    // Add site-specific schema markup (this can be customized based on site purpose)
    add_action('wp_head', function() {
        if (is_front_page() || is_home()) {
            // This schema can be customized based on your site type
            // For example, if it's an educational site:
            $site_schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Organization', // Change this based on your site type: 'EducationalOrganization', 'LocalBusiness', 'Corporation', etc.
                'name' => get_bloginfo('name'),
                'url' => home_url('/'),
                'logo' => get_site_icon_url() ?: get_template_directory_uri() . '/images/logo.png',
                'description' => get_bloginfo('description'),
                'sameAs' => array(
                    // Add your social profile URLs here
                )
            );
            
            echo '<script type="application/ld+json">' . json_encode($site_schema, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }
    }, 15);

    // Custom title structure if needed
    add_filter('the_title', function($title, $id = null) {
        if (is_admin()) {
            return $title;
        }
        
        // Add your custom title logic here if needed
        // For example: return $title . ' - ' . get_bloginfo('name');
        
        return $title;
    }, 10, 2);

    // Custom meta descriptions if needed
    add_filter('wpseo_metadesc', function($desc) {
        if (is_front_page() || is_home()) {
            // Return site-specific description here
            // return 'Your custom home description here';
        }
        return $desc;
    }, 10, 1);
}

// Uncomment the line below when you customize this file for your site
// add_action('init', 'configure_eweb_seo_for_current_site');