<?php
if (!defined('ABSPATH')) {
    exit;
}

class PDF_Flipbook_ACF_Fields {
    public function __construct() {
        add_action('acf/init', array($this, 'register_fields'));
    }

    public function register_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_flipbook_settings',
            'title' => 'PDF Flipbook Settings',
            'fields' => array(
                // Instructions Message
                array(
                    'key' => 'field_instructions',
                    'label' => 'PDF Flipbook Setup',
                    'name' => 'instructions',
                    'type' => 'message',
                    'message' => '<h3>Creating Your PDF Flipbook</h3>
                        <ol>
                            <li>Enter a title above</li>
                            <li>Upload your PDF file below</li>
                            <li>Choose how you want to organize this document</li>
                            <li>Configure display settings</li>
                            <li>Click Publish when ready</li>
                        </ol>',
                    'wrapper' => array(
                        'class' => 'pdf-flipbook-instructions'
                    ),
                ),

                // PDF Upload Section
                array(
                    'key' => 'field_pdf_section',
                    'label' => 'PDF Document',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_pdf_file',
                    'label' => 'PDF File',
                    'name' => 'pdf_file',
                    'type' => 'file',
                    'required' => 1,
                    'return_format' => 'array',
                    'library' => 'all',
                    'mime_types' => 'pdf',
                    'instructions' => 'Select or upload the PDF file you want to convert into a flipbook.',
                ),

                // Organization Section
                array(
                    'key' => 'field_organization_section',
                    'label' => 'Document Organization',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_document_type',
                    'label' => 'Document Type',
                    'name' => 'document_type',
                    'type' => 'select',
                    'required' => 1,
                    'choices' => $this->get_document_type_choices(),
                    'default_value' => 'custom',
                    'instructions' => 'Choose how you want to organize this document.',
                ),

                // Newsletter Fields
                array(
                    'key' => 'field_newsletter_group',
                    'label' => 'Newsletter Details',
                    'name' => 'newsletter_details',
                    'type' => 'group',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_document_type',
                                'operator' => '==',
                                'value' => 'newsletter',
                            ),
                        ),
                    ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_year',
                            'label' => 'Year',
                            'name' => 'year',
                            'type' => 'number',
                            'required' => 1,
                            'min' => 1900,
                            'max' => 2100,
                            'default_value' => date('Y'),
                        ),
                        array(
                            'key' => 'field_month',
                            'label' => 'Month',
                            'name' => 'month',
                            'type' => 'select',
                            'required' => 1,
                            'choices' => array(
                                '01' => 'January',
                                '02' => 'February',
                                '03' => 'March',
                                '04' => 'April',
                                '05' => 'May',
                                '06' => 'June',
                                '07' => 'July',
                                '08' => 'August',
                                '09' => 'September',
                                '10' => 'October',
                                '11' => 'November',
                                '12' => 'December'
                            ),
                            'default_value' => date('m'),
                        ),
                        array(
                            'key' => 'field_issue',
                            'label' => 'Issue Number',
                            'name' => 'issue',
                            'type' => 'text',
                            'required' => 1,
                            'placeholder' => 'e.g., 123 or Vol-4',
                        ),
                    ),
                ),

                // Custom Path Field
                array(
                    'key' => 'field_custom_path',
                    'label' => 'Custom Path',
                    'name' => 'custom_path',
                    'type' => 'text',
                    'instructions' => 'Enter the desired path for this document (e.g., "reports/annual/2024" or "manuals/products")',
                    'required' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_document_type',
                                'operator' => '==',
                                'value' => 'custom',
                            ),
                        ),
                    ),
                ),

                // Display Settings Section
                array(
                    'key' => 'field_display_section',
                    'label' => 'Display Settings',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_animation_speed',
                    'label' => 'Page Turn Animation Speed',
                    'name' => 'animation_speed',
                    'type' => 'range',
                    'instructions' => 'Adjust how quickly pages turn (in milliseconds)',
                    'min' => 500,
                    'max' => 2000,
                    'step' => 100,
                    'default_value' => 1000,
                    'append' => 'ms',
                ),
                array(
                    'key' => 'field_show_thumbnails',
                    'label' => 'Page Thumbnails',
                    'name' => 'show_thumbnails',
                    'type' => 'true_false',
                    'instructions' => 'Show thumbnail navigation for quick page access',
                    'ui' => 1,
                    'ui_on_text' => 'Show',
                    'ui_off_text' => 'Hide',
                    'default_value' => 1,
                ),

                // Usage Instructions Section
                array(
                    'key' => 'field_usage_section',
                    'label' => 'Usage Instructions',
                    'type' => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key' => 'field_usage_instructions',
                    'label' => 'How to Use This Flipbook',
                    'type' => 'message',
                    'message' => $this->get_usage_instructions(),
                    'new_lines' => 'wpautop',
                    'esc_html' => 0,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'flipbook',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'acf_after_title',
            'style' => 'seamless',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => array(
                'permalink',
                'the_content',
                'excerpt',
                'custom_fields',
                'discussion',
                'comments',
                'revisions',
                'slug',
                'author',
                'format',
                'page_attributes',
                'featured_image',
                'categories',
                'tags',
                'send-trackbacks',
            ),
        ));
    }

    private function get_document_type_choices() {
        $options = get_option('pdf_flipbook_settings', array());
        $choices = array(
            'custom' => 'Custom Path'
        );
        
        if (isset($options['document_types'])) {
            foreach ($options['document_types'] as $type) {
                if (!empty($type['type'])) {
                    $choices[$type['type']] = ucfirst($type['type']);
                }
            }
        }
        
        return $choices;
    }

    private function get_usage_instructions() {
        $instructions = '
        <div class="pdf-flipbook-usage-instructions">
            <h3>Using Your Flipbook</h3>
            
            <h4>Shortcode</h4>
            <p>Once published, use this shortcode to display your flipbook:</p>
            <code>[flipbook id="' . get_the_ID() . '"]</code>
            
            <h4>Example Usage</h4>
            <ul>
                <li>Add to a page: Paste the shortcode into any page or post content</li>
                <li>Add to a template: Use <code>echo do_shortcode(\'[flipbook id="' . get_the_ID() . '"]\');</code></li>
                <li>Add to a widget: Paste the shortcode into a text/HTML widget</li>
            </ul>
            
            <h4>Display Options</h4>
            <ul>
                <li>Navigation: Users can turn pages using arrow keys, buttons, or by clicking/dragging pages</li>
                <li>Thumbnails: If enabled, shows a thumbnail strip for quick navigation</li>
                <li>Mobile: Responsive design works on all devices</li>
            </ul>
        </div>';

        return $instructions;
    }
}