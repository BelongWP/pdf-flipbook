<?php
/*
Plugin Name: PDF Flipbook
Description: Create interactive flipbook viewers from PDF documents
Version: 1.0.2-alpha
Author: BelongWP
Requires: Advanced Custom Fields Pro
*/

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Flipbook_Plugin {
    private static $instance = null;
    
    // Static property to store settings page callback
    private static $settings_page_callback = null;

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
        // Core plugin hooks
        add_action('plugins_loaded', array($this, 'init_plugin'));
        add_action('init', array($this, 'register_post_type'));
        add_action('admin_menu', array($this, 'setup_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Store settings page callback for later use
        self::$settings_page_callback = array('PDF_Flipbook_Settings', 'render_page');
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

        // Initialize the settings class
        new PDF_Flipbook_Settings();
        new PDF_Flipbook_ACF_Fields();
        new PDF_Flipbook_Handler();
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

        // Get settings instance for the callback
        $settings = PDF_Flipbook_Settings::get_instance();
        
        add_submenu_page(
            'pdf-flipbooks',
            'PDF Flipbook Settings',
            'Settings',
            'manage_options',
            'pdf-flipbook-settings',
            array($settings, 'render_page')
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
        }
    }

    public function render_dashboard() {
        include PDF_FLIPBOOK_PATH . 'admin/views/dashboard.php';
    }

    public function render_help() {
        include PDF_FLIPBOOK_PATH . 'admin/views/help.php';
    }
}

// Initialize the plugin
function PDF_Flipbook() {
    return PDF_Flipbook_Plugin::get_instance();
}

// Start the plugin
PDF_Flipbook();