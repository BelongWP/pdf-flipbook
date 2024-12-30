<?php
/*
Plugin Name: PDF Flipbook
Description: Create interactive flipbook viewers from PDF documents
Version: 1.0.1-alpha
Author: BelongWP
Requires: Advanced Custom Fields Pro
*/

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Flipbook_Plugin {
    private static $instance = null;
    private $errors = array();

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

        // Initialize plugin
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'setup_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Add helpful links
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_links'));
    }

    public function init() {
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

        // Register post type
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
            'show_in_menu' => false, // We'll add this under our custom menu
        ));

        // Load required files
        require_once PDF_FLIPBOOK_PATH . 'includes/class-pdf-handler.php';
        require_once PDF_FLIPBOOK_PATH . 'includes/class-acf-fields.php';
        require_once PDF_FLIPBOOK_PATH . 'admin/class-settings.php';

        // Initialize components
        new PDF_Flipbook_Handler();
        new PDF_Flipbook_ACF_Fields();
        new PDF_Flipbook_Settings();
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
            array($this->settings, 'render_settings')
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
        // Add admin styles and scripts
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
        ?>
        <div class="wrap pdf-flipbook-dashboard">
            <h1>PDF Flipbook Dashboard</h1>
            
            <div class="pdf-flipbook-quick-actions">
                <a href="<?php echo admin_url('post-new.php?post_type=flipbook'); ?>" class="button button-primary">
                    Create New Flipbook
                </a>
                <a href="<?php echo admin_url('edit.php?post_type=flipbook'); ?>" class="button">
                    View All Flipbooks
                </a>
                <a href="<?php echo admin_url('admin.php?page=pdf-flipbook-settings'); ?>" class="button">
                    Configure Settings
                </a>
            </div>

            <div class="pdf-flipbook-getting-started">
                <h2>Getting Started</h2>
                <ol>
                    <li>Click "Create New Flipbook" to start a new PDF flipbook</li>
                    <li>Give your flipbook a title and upload your PDF file</li>
                    <li>Choose a document type and configure the path settings</li>
                    <li>Publish your flipbook</li>
                    <li>Use the shortcode [flipbook id="X"] to display it on any page</li>
                </ol>
            </div>

            <?php
            // Show recent flipbooks
            $recent_flipbooks = get_posts(array(
                'post_type' => 'flipbook',
                'posts_per_page' => 5,
                'orderby' => 'date',
                'order' => 'DESC'
            ));

            if ($recent_flipbooks): ?>
                <div class="pdf-flipbook-recent">
                    <h2>Recent Flipbooks</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Shortcode</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_flipbooks as $flipbook): ?>
                                <tr>
                                    <td><?php echo esc_html($flipbook->post_title); ?></td>
                                    <td><code>[flipbook id="<?php echo $flipbook->ID; ?>"]</code></td>
                                    <td><?php echo get_the_date('', $flipbook->ID); ?></td>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($flipbook->ID); ?>">Edit</a> |
                                        <a href="<?php echo get_permalink($flipbook->ID); ?>">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_help() {
        ?>
        <div class="wrap pdf-flipbook-help">
            <h1>PDF Flipbook Documentation</h1>

            <div class="pdf-flipbook-help-section">
                <h2>Creating a New Flipbook</h2>
                <ol>
                    <li><strong>Create New:</strong> Click "Add New" in the PDF Flipbooks menu</li>
                    <li><strong>Title:</strong> Give your flipbook a meaningful title</li>
                    <li><strong>Upload PDF:</strong> Upload or select your PDF file</li>
                    <li><strong>Document Type:</strong> Choose how you want to organize this document</li>
                    <li><strong>Path Settings:</strong> Configure where the file will be stored</li>
                    <li><strong>Display Options:</strong> Adjust animation speed and thumbnail settings</li>
                    <li><strong>Publish:</strong> Click Publish to save your flipbook</li>
                </ol>
            </div>

            <div class="pdf-flipbook-help-section">
                <h2>Using Shortcodes</h2>
                <p>To display a flipbook in any post or page, use the shortcode:</p>
                <code>[flipbook id="X"]</code>
                <p>Replace "X" with your flipbook's ID number (found in the All Flipbooks list).</p>
            </div>

            <div class="pdf-flipbook-help-section">
                <h2>Document Types and Paths</h2>
                <p>You can organize your flipbooks using custom path structures:</p>
                <ul>
                    <li><code>{year}</code> - The document year</li>
                    <li><code>{month}</code> - The document month</li>
                    <li><code>{issue}</code> - Issue number (for newsletters)</li>
                    <li><code>{title}</code> - The flipbook title</li>
                    <li><code>{id}</code> - The flipbook ID</li>
                </ul>
                <p>Example path: <code>newsletters/{year}/{month}/{issue}</code></p>
            </div>
        </div>
        <?php
    }

    public function add_plugin_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=pdf-flipbooks') . '">Dashboard</a>',
            '<a href="' . admin_url('admin.php?page=pdf-flipbook-help') . '">Documentation</a>'
        );
        return array_merge($plugin_links, $links);
    }
}

// Initialize the plugin
function PDF_Flipbook() {
    return PDF_Flipbook_Plugin::get_instance();
}

PDF_Flipbook();