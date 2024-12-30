<?php
if (!defined('ABSPATH')) {
    exit;
}

class PDF_Flipbook_Settings {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
    }

    public function add_admin_menu() {
        add_options_page(
            'PDF Flipbook Settings', 
            'PDF Flipbook', 
            'manage_options', 
            'pdf_flipbook_settings', 
            array($this, 'options_page')
        );
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

    public function settings_section_callback() {
        echo '<p>Configure path structures for different document types. Use the following variables in your paths:</p>';
        echo '<ul>';
        echo '<li><code>{year}</code> - Year (YYYY)</li>';
        echo '<li><code>{month}</code> - Month (MM)</li>';
        echo '<li><code>{issue}</code> - Issue number</li>';
        echo '<li><code>{title}</code> - Document title</li>';
        echo '<li><code>{id}</code> - Document ID</li>';
        echo '</ul>';
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

    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
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
}