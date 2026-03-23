<?php
/**
 * Plugin Name: EWEB Universal SEO Optimizer
 * Description: Advanced SEO optimization tool that can be customized for any WordPress site with project-specific configurations
 * Version: 1.0.1
 * Author: Yisus Develop
 * Author URI: https://github.com/Yisus-Develop
 * Plugin URI: https://enlaweb.co/
 * Text Domain: eweb-universal-seo
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Tested up to: 6.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * EWEB Universal SEO Optimizer - Part of the EWEB plugin suite
 * 
 * This plugin implements comprehensive SEO optimizations with project-specific configurations
 * following WordPress coding standards and EWEB naming conventions.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * EWEB_Universal_SEO_Optimizer class
 * Implements comprehensive SEO optimizations with project-specific configurations
 */
class EWEB_Universal_SEO_Optimizer {

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
        $this->load_project_config();
    }

    private function init() {
        add_action('init', array($this, 'initialize_hooks'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Load project-specific configuration
     */
    private function load_project_config() {
        // Initialize with basic site information
        $this->project_config = array(
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url(),
            'site_description' => get_bloginfo('description'),
            'primary_keywords' => array(),
            'target_audience' => 'general',
            'content_strategy' => 'blog',
            'local_seo_enabled' => false,
            'local_business_type' => '',
            'local_business_name' => get_bloginfo('name'),
            'social_profiles' => array(
                'facebook' => '',
                'twitter' => '',
                'instagram' => '',
                'linkedin' => '',
                'youtube' => '',
                'pinterest' => ''
            ),
            'tracking_enabled' => true,
            'analytics_id' => get_option('eweb_analytics_id', ''),
            'search_console_verification' => get_option('eweb_search_console_verification', ''),
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
        
        // Apply site-specific enhancements based on content
        $this->apply_content_based_enhancements();
    }
    
    private function apply_content_based_enhancements() {
        // Apply enhancements based on site content and structure
        $description = $this->project_config['site_description'];
        $post_count = wp_count_posts()->publish;
        
        // Determine if this is likely an educational site
        if (stripos($description, 'education') !== false || 
            stripos($description, 'learn') !== false || 
            stripos($description, 'course') !== false ||
            stripos($description, 'university') !== false) {
            $this->project_config['content_strategy'] = 'education';
            $this->project_config['content_enhancement_settings']['enable_author_schema'] = true;
        } 
        // Determine if this is likely an e-commerce site
        elseif ($post_count > 50 && class_exists('WooCommerce')) {
            $this->project_config['content_strategy'] = 'ecommerce';
        }
        // Determine if this is likely a blog
        elseif ($post_count > 20) {
            $this->project_config['content_strategy'] = 'blog';
        }
        
        // Update primary keywords based on site type
        if (empty($this->project_config['primary_keywords'])) {
            $keywords = array();
            
            if ($this->project_config['content_strategy'] === 'education') {
                $keywords = array('education', 'learning', 'courses', 'tutorials', 'information');
            } elseif ($this->project_config['content_strategy'] === 'ecommerce') {
                $keywords = array('shop', 'buy', 'products', 'online store', 'ecommerce');
            } else {
                $keywords = array('content', 'information', 'blog', 'website', $this->project_config['site_name']);
            }
            
            $this->project_config['primary_keywords'] = $keywords;
        }
    }

    public function initialize_hooks() {
        // Core Web Vitals optimizations
        add_action('wp_enqueue_scripts', array($this, 'optimize_assets'), 100);
        
        // SEO meta tags
        add_action('wp_head', array($this, 'add_seo_meta_tags'), 1);
        add_action('wp_head', array($this, 'add_schema_markup'), 10);
        
        // Technical SEO
        add_action('wp_head', array($this, 'add_technical_seo'), 5);
        add_action('wp_head', array($this, 'optimize_404_handling'), 20);
        
        // Content optimization
        add_filter('the_content', array($this, 'enhance_content_structure'));
        
        // URL structure optimization
        add_filter('post_link', array($this, 'customize_permalink_structure'), 10, 3);
        add_filter('page_link', array($this, 'customize_page_permalink'), 10, 2);
    }

    /**
     * Optimize assets for Core Web Vitals
     */
    public function optimize_assets() {
        // Defer non-critical JavaScript
        add_filter('script_loader_tag', array($this, 'defer_non_critical_js'), 10, 3);
        
        // Async CSS loading
        add_filter('style_loader_tag', array($this, 'async_css_loading'), 10, 2);
        
        // Lazy loading for images
        add_filter('wp_get_attachment_image_attributes', array($this, 'add_image_lazy_loading'));
    }

    public function defer_non_critical_js($tag, $handle, $src) {
        $critical_scripts = array('jquery', 'jquery-core', 'wp-embed');
        
        if (!empty($src) && !in_array($handle, $critical_scripts)) {
            if (strpos($tag, 'defer') === false && strpos($src, 'jquery') === false) {
                $tag = str_replace(' src', ' defer src', $tag);
            }
        }
        
        return $tag;
    }

    public function async_css_loading($tag, $handle) {
        $critical_styles = array('dashicons', 'admin-bar');
        
        if (!in_array($handle, $critical_styles)) {
            $tag = str_replace('media="all"', 'media="print" onload="this.media=\'all\'"', $tag);
        }
        
        return $tag;
    }

    public function add_image_lazy_loading($attr) {
        if (!isset($attr['loading'])) {
            $attr['loading'] = 'lazy';
        }
        return $attr;
    }

    /**
     * Add comprehensive SEO meta tags
     */
    public function add_seo_meta_tags() {
        global $post;
        
        $title = $this->get_optimized_title();
        $description = $this->get_optimized_description();
        $url = get_permalink();
        
        echo '<title>' . esc_html($title) . '</title>' . "\n";
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta name="robots" content="index, follow">' . "\n";
        echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
        
        // Open Graph tags
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        
        // Twitter Card tags
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    }

    private function get_optimized_title() {
        global $post;
        
        if (is_home() || is_front_page()) {
            return get_bloginfo('name') . ' - ' . get_bloginfo('description');
        } elseif (is_single() || is_page()) {
            return $post->post_title . ' | ' . get_bloginfo('name');
        } elseif (is_category()) {
            return single_cat_title('', false) . ' | ' . get_bloginfo('name');
        } elseif (is_tag()) {
            return single_tag_title('', false) . ' | ' . get_bloginfo('name');
        } else {
            return get_bloginfo('name') . ' | ' . wp_title('', false);
        }
    }

    private function get_optimized_description() {
        global $post;
        
        $description = '';
        
        if (is_home() || is_front_page()) {
            $description = get_bloginfo('description');
        } elseif (is_single() || is_page()) {
            if (!empty($post->post_excerpt)) {
                $description = $post->post_excerpt;
            } else {
                $content = strip_shortcodes(strip_tags($post->post_content));
                $description = wp_trim_words($content, 30, '...');
            }
        } elseif (is_category()) {
            $description = category_description();
        } elseif (is_tag()) {
            $description = tag_description();
        }
        
        // Ensure proper length
        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }
        
        if (empty($description)) {
            $description = get_bloginfo('description');
        }
        
        return esc_attr($description);
    }

    /**
     * Add comprehensive schema markup
     */
    public function add_schema_markup() {
        if (is_single() || is_page()) {
            global $post;
            
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => get_the_title(),
                'description' => $this->get_optimized_description(),
                'author' => array(
                    '@type' => 'Organization',
                    'name' => $this->project_config['site_name']
                ),
                'publisher' => array(
                    '@type' => 'Organization',
                    'name' => $this->project_config['site_name'],
                    'logo' => array(
                        '@type' => 'ImageObject',
                        'url' => get_site_icon_url() ?: get_template_directory_uri() . '/images/logo.png'
                    )
                ),
                'datePublished' => get_the_date('c'),
                'dateModified' => get_the_modified_date('c'),
                'mainEntityOfPage' => array(
                    '@type' => 'WebPage',
                    '@id' => get_permalink()
                )
            );
            
            if (has_post_thumbnail()) {
                $schema['image'] = array(
                    '@type' => 'ImageObject',
                    'url' => get_the_post_thumbnail_url($post, 'full'),
                    'width' => 1200,
                    'height' => 630
                );
            }
            
            echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        } elseif (is_home() || is_front_page()) {
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $this->project_config['site_name'],
                'url' => home_url(),
                'description' => get_bloginfo('description'),
                'potentialAction' => array(
                    '@type' => 'SearchAction',
                    'target' => home_url('/?s={search_term_string}'),
                    'query-input' => 'required name=search_term_string'
                )
            );
            
            echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }
    }

    /**
     * Add technical SEO elements
     */
    public function add_technical_seo() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">' . "\n";
        
        // Add verification meta tags if configured
        if (!empty($this->project_config['search_console_verification'])) {
            echo '<meta name="google-site-verification" content="' . 
                 esc_attr($this->project_config['search_console_verification']) . '">' . "\n";
        }
    }

    /**
     * Enhance content structure
     */
    public function enhance_content_structure($content) {
        if (is_single() && !is_admin()) {
            // Add structured data to content
            $enhanced_content = $content;
            
            // Add schema-related markup if needed
            if (get_post_type() === 'post' && !has_shortcode($content, 'seo-enhancement')) {
                $enhanced_content = $content . $this->get_content_enhancements();
            }
            
            return $enhanced_content;
        }
        
        return $content;
    }

    private function get_content_enhancements() {
        $enhancements = '';
        
        // Add author information if post has author
        $author_name = get_the_author_meta('display_name');
        $author_url = get_author_posts_url(get_the_author_meta('ID'));
        $enhancements .= '<div class="author-info" style="display:none;">';
        $enhancements .= '<span class="author-name" content="' . esc_attr($author_name) . '">';
        $enhancements .= '</span>';
        $enhancements .= '<span class="author-url" content="' . esc_url($author_url) . '">';
        $enhancements .= '</span>';
        $enhancements .= '</div>';
        
        return $enhancements;
    }

    /**
     * Admin menu and settings
     */
    public function add_admin_menu() {
        add_menu_page(
            'EWEB Universal SEO Settings',
            'EWEB SEO',
            'manage_options',
            'eweb-universal-seo',
            array($this, 'admin_page'),
            'dashicons-chart-line',
            30
        );
    }

    public function register_settings() {
        register_setting('eweb_universal_seo_settings', 'eweb_universal_seo_config');
        
        add_settings_section(
            'eweb_universal_seo_main',
            'SEO Configuration',
            null,
            'eweb-universal-seo'
        );
        
        add_settings_field(
            'primary_keywords',
            'Primary Keywords (comma separated)',
            array($this, 'keywords_callback'),
            'eweb-universal-seo',
            'eweb_universal_seo_main',
            array('label_for' => 'primary_keywords')
        );
        
        add_settings_field(
            'local_seo_enabled',
            'Enable Local SEO',
            array($this, 'checkbox_callback'),
            'eweb-universal-seo',
            'eweb_universal_seo_main',
            array('label_for' => 'local_seo_enabled', 'option' => 'local_seo_enabled')
        );
        
        add_settings_field(
            'analytics_id',
            'Google Analytics ID',
            array($this, 'text_callback'),
            'eweb-universal-seo',
            'eweb_universal_seo_main',
            array('label_for' => 'analytics_id', 'option' => 'analytics_id')
        );
        
        add_settings_field(
            'search_console_verification',
            'Google Search Console Verification',
            array($this, 'text_callback'),
            'eweb-universal-seo',
            'eweb_universal_seo_main',
            array('label_for' => 'search_console_verification', 'option' => 'search_console_verification')
        );
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>EWEB - Universal SEO Optimizer</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('eweb_universal_seo_settings');
                do_settings_sections('eweb-universal-seo');
                submit_button();
                ?>
            </form>
            
            <div class="card" style="margin-top: 20px; padding: 20px;">
                <h2>SEO Analysis Report</h2>
                <p>Run comprehensive SEO analysis on your site:</p>
                <button id="run-seo-analysis" class="button button-primary">Run Analysis</button>
                <div id="analysis-results" style="margin-top: 20px; display: none;">
                    <h3>Analysis Results</h3>
                    <div id="results-content"></div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#run-seo-analysis').click(function() {
                $(this).text('Running Analysis...').prop('disabled', true);
                
                $.post(ajaxurl, {
                    action: 'run_eweb_universal_seo_analysis',
                    nonce: '<?php echo wp_create_nonce("run_seo_analysis_nonce"); ?>'
                }, function(response) {
                    $('#analysis-results').show();
                    $('#results-content').html(response.data);
                    $('#run-seo-analysis').text('Run Analysis').prop('disabled', false);
                });
            });
        });
        </script>
        <?php
    }

    public function keywords_callback($args) {
        $value = isset($this->project_config['primary_keywords']) ? 
                implode(', ', (array)$this->project_config['primary_keywords']) : '';
        echo '<input type="text" id="primary_keywords" name="eweb_universal_seo_config[primary_keywords]" 
             value="' . esc_attr($value) . '" style="width: 100%;">';
        echo '<p class="description">Enter your primary keywords separated by commas</p>';
    }

    public function text_callback($args) {
        $option = $args['option'];
        $value = isset($this->project_config[$option]) ? $this->project_config[$option] : '';
        echo '<input type="text" id="' . $args['label_for'] . '" 
             name="eweb_universal_seo_config[' . $option . ']" 
             value="' . esc_attr($value) . '" style="width: 100%;">';
    }

    public function checkbox_callback($args) {
        $option = $args['option'];
        $checked = isset($this->project_config[$option]) ? checked(1, $this->project_config[$option], false) : '';
        echo '<input type="checkbox" id="' . $args['label_for'] . '" 
             name="eweb_universal_seo_config[' . $option . ']" value="1" ' . $checked . '>';
    }

    /**
     * Add AJAX handler for SEO analysis
     */
    public function add_ajax_handlers() {
        add_action('wp_ajax_run_eweb_universal_seo_analysis', array($this, 'run_seo_analysis'));
    }

    public function run_seo_analysis() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'run_seo_analysis_nonce')) {
            wp_die('Security check failed');
        }
        
        // Perform SEO analysis
        $analysis = $this->perform_seo_analysis();
        
        wp_send_json_success($analysis);
    }

    private function perform_seo_analysis() {
        $analysis = array();
        
        // Check for common SEO issues
        $analysis['title_issues'] = $this->check_title_issues();
        $analysis['meta_description_issues'] = $this->check_meta_description_issues();
        $analysis['heading_issues'] = $this->check_heading_issues();
        $analysis['broken_links'] = $this->check_broken_links();
        $analysis['page_speed'] = $this->check_page_speed_indicators();
        
        $html = '<ul>';
        foreach ($analysis as $category => $issues) {
            $html .= '<li><strong>' . ucfirst(str_replace('_', ' ', $category)) . ':</strong> ' . $issues . '</li>';
        }
        $html .= '</ul>';
        
        return $html;
    }

    private function check_title_issues() {
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_title LIKE '%title%' OR post_title = ''");
        return $count > 0 ? "$count pages have potential title issues" : "No title issues detected";
    }

    private function check_meta_description_issues() {
        // This would be more complex in a real implementation
        return "Manual check required";
    }

    private function check_heading_issues() {
        // This would require content analysis
        return "Manual check required";
    }

    private function check_broken_links() {
        // This would require link checking
        return "Use external tool for comprehensive check";
    }

    private function check_page_speed_indicators() {
        return "Optimizations implemented: Defer JS, Async CSS, Lazy load images";
    }

    /**
     * Customize permalink structure
     */
    public function customize_permalink_structure($permalink, $post, $leavename) {
        // Customize based on project configuration
        return $permalink;
    }

    public function customize_page_permalink($link, $post_id) {
        // Customize based on project configuration
        return $link;
    }
}

// Initialize the plugin
add_action('plugins_loaded', array('EWEB_Universal_SEO_Optimizer', 'get_instance'));

// Add AJAX handler
add_action('wp_ajax_run_eweb_universal_seo_analysis', array('EWEB_Universal_SEO_Optimizer::get_instance()', 'run_seo_analysis'));