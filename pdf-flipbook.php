<?php
/*
Plugin Name: PDF Flipbook
Description: Create interactive flipbook viewers from PDF documents
Version: 1.0.5-alpha
Author: BelongWP
Requires: Advanced Custom Fields Pro
*/

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Flipbook_Plugin {
    private static $instance = null;
    private $settings;
    private $acf_fields;
    private $pdf_handler;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Define constants
        define('PDF_FLIPBOOK_VERSION', '1.0.0');
        define('PDF_FLIPBOOK_PATH', plugin_dir_path(__FILE__));
        define('PDF_FLIPBOOK_URL', plugin_dir_url(__FILE__));

        // Load components and register hooks
        $this->load_dependencies();
        $this->register_hooks();
    }

    private function load_dependencies() {
        // Load required files
        require_once PDF_FLIPBOOK_PATH . 'includes/class-pdf-handler.php';
        require_once PDF_FLIPBOOK_PATH . 'includes/class-acf-fields.php';
        require_once PDF_FLIPBOOK_PATH . 'admin/class-settings.php';
    }

    private function register_hooks() {
        // Core initialization hooks
        add_action('plugins_loaded', array($this, 'init_plugin'));
        add_action('init', array($this, 'register_post_type'));
        
        // Admin-specific hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'setup_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_links'));
        }
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function init_plugin() {
        // Check for ACF Pro
        if (!class_exists('ACF')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-error">
                    <p>PDF Flipbook requires Advanced Custom Fields Pro to be installed and activated.</p>
                </div>
                <?php
            });
            return;
        }

        // Initialize components via singleton pattern
        $this->settings = new PDF_Flipbook_Settings();
        $this->acf_fields = new PDF_Flipbook_ACF_Fields();
        $this->pdf_handler = new PDF_Flipbook_Handler();
    }

    public function register_post_type() {
        register_post_type('flipbook', array(
            'labels' => array(
                'name' => 'PDF Flipbooks',
                'singular_name' => 'PDF Flipbook',
                'add_new' => 'Add New Flipbook',
                'add_new_item' => 'Add New PDF Flipbook',
                'edit_item' => 'Edit PDF Flipbook',
                'new_item' => 'New PDF Flipbook',
                'view_item' => 'View PDF Flipbook',
                'search_items' => 'Search PDF Flipbooks',
                'not_found' => 'No PDF flipbooks found',
                'not_found_in_trash' => 'No PDF flipbooks found in trash',
                'menu_name' => 'PDF Flipbooks'
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-book-alt',
            'show_in_menu' => false,
            'rewrite' => array('slug' => 'flipbooks'),
        ));
    }

    public function setup_admin_menu() {
        // Add main menu
        add_menu_page(
            'PDF Flipbooks',
            'PDF Flipbooks',
            'manage_options',
            'pdf-flipbooks',
            array($this, 'render_dashboard'),
            'dashicons-book-alt',
            20
        );

        // Add submenus
        add_submenu_page(
            'pdf-flipbooks',
            'All Flipbooks',
            'All Flipbooks',
            'manage_options',
            'edit.php?post_type=flipbook'
        );

        add_submenu_page(
            'pdf-flipbooks',
            'Add New Flipbook',
            'Add New',
            'manage_options',
            'post-new.php?post_type=flipbook'
        );

        add_submenu_page(
            'pdf-flipbooks',
            'PDF Flipbook Settings',
            'Settings',
            'manage_options',
            'pdf-flipbook-settings',
            array($this->settings, 'render_page')
        );

        add_submenu_page(
            'pdf-flipbooks',
            'PDF Flipbook Help',
            'Help & Documentation',
            'manage_options',
            'pdf-flipbook-help',
            array($this, 'render_help')
        );
    }

    public function admin_scripts($hook) {
        if (strpos($hook, 'pdf-flipbook') !== false || get_post_type() === 'flipbook') {
            wp_enqueue_style(
                'pdf-flipbook-admin',
                PDF_FLIPBOOK_URL . 'admin/css/admin.css',
                array(),
                PDF_FLIPBOOK_VERSION
            );

            wp_enqueue_script(
                'pdf-flipbook-admin',
                PDF_FLIPBOOK_URL . 'admin/js/admin.js',
                array('jquery'),
                PDF_FLIPBOOK_VERSION,
                true
            );
        }
    }

    public function enqueue_scripts() {
        if (has_shortcode(get_the_content(), 'flipbook')) {
            wp_enqueue_style(
                'pdf-flipbook',
                PDF_FLIPBOOK_URL . 'assets/css/flipbook.css',
                array(),
                PDF_FLIPBOOK_VERSION
            );

            wp_enqueue_script(
                'three-js',
                'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js',
                array(),
                '128',
                true
            );

            wp_enqueue_script(
                'pdf-js',
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js',
                array(),
                '2.9.359',
                true
            );

            wp_enqueue_script(
                'pdf-flipbook',
                PDF_FLIPBOOK_URL . 'assets/js/flipbook.js',
                array('jquery', 'three-js', 'pdf-js'),
                PDF_FLIPBOOK_VERSION,
                true
            );
        }
    }

    public function render_dashboard() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include PDF_FLIPBOOK_PATH . 'admin/views/dashboard.php';
    }

    public function render_help() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include PDF_FLIPBOOK_PATH . 'admin/views/help.php';
    }

    public function add_plugin_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=pdf-flipbooks') . '">Dashboard</a>',
            '<a href="' . admin_url('admin.php?page=pdf-flipbook-help') . '">Documentation</a>'
        );
        return array_merge($plugin_links, $links);
    }

    // Activation hook callback
    public static function activate() {
        // Create necessary directories
        $upload_dir = wp_upload_dir();
        $flipbook_dir = $upload_dir['basedir'] . '/flipbooks';
        
        if (!file_exists($flipbook_dir)) {
            wp_mkdir_p($flipbook_dir);
        }

        // Create .htaccess for directory protection
        $htaccess_file = $flipbook_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Options -Indexes\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }

        // Initialize default settings
        $default_settings = array(
            'document_types' => array(
                array(
                    'type' => 'newsletter',
                    'path' => 'newsletter/{year}/{month}/{issue}'
                )
            )
        );
        add_option('pdf_flipbook_settings', $default_settings);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    // Deactivation hook callback
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function PDF_Flipbook() {
    return PDF_Flipbook_Plugin::get_instance();
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('PDF_Flipbook_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('PDF_Flipbook_Plugin', 'deactivate'));

// Start the plugin
PDF_Flipbook();