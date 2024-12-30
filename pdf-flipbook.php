<?php
/*
Plugin Name: PDF Flip Book
Description: Converts PDF documents into responsive and accessible 3D flip books
Version: 1.0
Author: Your Name
Requires: Advanced Custom Fields Pro
*/

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Flipbook_Plugin
{
    private static $instance = null;
    private $errors = array();

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Define constants
        define('PDF_FLIPBOOK_VERSION', '1.0.0');
        define('PDF_FLIPBOOK_PATH', plugin_dir_path(__FILE__));
        define('PDF_FLIPBOOK_URL', plugin_dir_url(__FILE__));

        // Check dependencies
        add_action('admin_init', array($this, 'check_dependencies'));

        // Initialize plugin if dependencies are met
        if ($this->check_dependencies()) {
            $this->init();
        }
    }

    public function check_dependencies()
    {
        if (!class_exists('ACF')) {
            add_action('admin_notices', function () {
?>
                <div class="notice notice-error">
                    <p>PDF Flip Book plugin requires Advanced Custom Fields Pro to be installed and activated.</p>
                </div>
<?php
            });
            return false;
        }
        return true;
    }

    private function init()
    {
        // Load required files
        $this->load_files();

        // Initialize components
        add_action('init', array($this, 'initialize_components'));

        // Register activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    private function load_files()
    {
        $required_files = array(
            'admin/class-settings.php',
            'includes/class-pdf-handler.php',
            'includes/class-acf-fields.php'
        );

        foreach ($required_files as $file) {
            $file_path = PDF_FLIPBOOK_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("PDF Flipbook: File not found - " . $file_path);
                $this->errors[] = "Required file missing: $file";
            }
        }

        if (!empty($this->errors)) {
            add_action('admin_notices', function () {
                foreach ($this->errors as $error) {
                    echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
                }
            });
        }
    }

    public function initialize_components()
    {
        // Register post type
        register_post_type('flipbook', array(
            'labels' => array(
                'name' => __('Flip Books'),
                'singular_name' => __('Flip Book')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail'),
            'menu_icon' => 'dashicons-book',
            'show_in_rest' => true
        ));

        // Initialize components if files were loaded successfully
        if (empty($this->errors)) {
            new PDF_Flipbook_Settings();
            new PDF_Flipbook_ACF_Fields();
            new PDF_Flipbook_Handler();
        }
    }

    public function activate()
    {
        // Create necessary directories
        $upload_dir = wp_upload_dir();
        $flipbook_dir = $upload_dir['basedir'] . '/flipbooks';

        if (!file_exists($flipbook_dir)) {
            wp_mkdir_p($flipbook_dir);
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

    public function enqueue_scripts()
    {
        if (has_block('pdf-flipbook/viewer') || has_shortcode(get_the_content(), 'flipbook')) {
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
                'pdf-flipbook',
                PDF_FLIPBOOK_URL . 'assets/js/flipbook.js',
                array('jquery', 'three-js'),
                PDF_FLIPBOOK_VERSION,
                true
            );
        }
    }

    public function enqueue_admin_scripts($hook)
    {
        if ('settings_page_pdf_flipbook_settings' === $hook) {
            wp_enqueue_style(
                'pdf-flipbook-admin',
                PDF_FLIPBOOK_URL . 'admin/css/admin.css',
                array(),
                PDF_FLIPBOOK_VERSION
            );
        }
    }
}

// Initialize the plugin
function PDF_Flipbook()
{
    return PDF_Flipbook_Plugin::get_instance();
}

// Start the plugin
PDF_Flipbook();
