<?php
if (!defined('ABSPATH')) {
    exit;
}

class PDF_Flipbook_Settings {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_init', array($this, 'settings_init'));
        add_action('init', array($this, 'load_textdomain'));
    }

    public function load_textdomain() {
        load_plugin_textdomain('pdf-flipbook', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function settings_init() {
        register_setting('pdf_flipbook', 'pdf_flipbook_settings');

        add_settings_section(
            'pdf_flipbook_paths_section', 
            'Document Path Settings', 
            array($this, 'settings_section_callback'), 
            'pdf_flipbook'
        );

        add_settings_field(
            'document_types', 
            'Document Types', 
            array($this, 'document_types_render'), 
            'pdf_flipbook', 
            'pdf_flipbook_paths_section'
        );
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="pdf-flipbook-settings-intro">
                <p>Configure how your PDF flipbooks are organized. Define document types and their path structures using the variables below:</p>
                <ul>
                    <li><code>{year}</code> - Document year (YYYY)</li>
                    <li><code>{month}</code> - Document month (MM)</li>
                    <li><code>{issue}</code> - Issue number</li>
                    <li><code>{title}</code> - Document title</li>
                    <li><code>{id}</code> - Document ID</li>
                </ul>
            </div>

            <form action='options.php' method='post'>
                <?php
                settings_fields('pdf_flipbook');
                do_settings_sections('pdf_flipbook');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function settings_section_callback() {
        echo '<p>Add and configure different document types and their path structures.</p>';
    }

    public function document_types_render() {
        $options = get_option('pdf_flipbook_settings', array());
        $document_types = isset($options['document_types']) ? $options['document_types'] : array(
            array('type' => 'newsletter', 'path' => 'newsletter/{year}/{month}/{issue}')
        );
        ?>
        <div id="document-types-container">
            <?php foreach ($document_types as $index => $type): ?>
            <div class="document-type-row">
                <p>
                    <input type="text" 
                           name="pdf_flipbook_settings[document_types][<?php echo $index; ?>][type]" 
                           value="<?php echo esc_attr($type['type']); ?>" 
                           placeholder="Type (e.g., newsletter, report)"
                           class="regular-text">
                    <input type="text" 
                           name="pdf_flipbook_settings[document_types][<?php echo $index; ?>][path]" 
                           value="<?php echo esc_attr($type['path']); ?>" 
                           placeholder="Path structure (e.g., newsletter/{year}/{month})"
                           class="large-text">
                    <button type="button" class="button remove-type">Remove</button>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button" id="add-document-type">Add Document Type</button>

        <script>
            jQuery(document).ready(function($) {
                var container = $('#document-types-container');
                var index = container.children().length;

                $('#add-document-type').on('click', function() {
                    var row = $('<div class="document-type-row"><p>' +
                        '<input type="text" name="pdf_flipbook_settings[document_types][' + index + '][type]" ' +
                        'placeholder="Type (e.g., newsletter, report)" class="regular-text"> ' +
                        '<input type="text" name="pdf_flipbook_settings[document_types][' + index + '][path]" ' +
                        'placeholder="Path structure (e.g., newsletter/{year}/{month})" class="large-text"> ' +
                        '<button type="button" class="button remove-type">Remove</button>' +
                        '</p></div>');
                    container.append(row);
                    index++;
                });

                $(document).on('click', '.remove-type', function() {
                    $(this).closest('.document-type-row').remove();
                });
            });
        </script>
        <?php
    }
}