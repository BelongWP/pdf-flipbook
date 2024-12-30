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
            'title' => 'Flip Book Settings',
            'fields' => array(
                array(
                    'key' => 'field_document_type',
                    'label' => 'Document Type',
                    'name' => 'document_type',
                    'type' => 'select',
                    'choices' => $this->get_document_type_choices(),
                    'required' => 1,
                ),
                array(
                    'key' => 'field_year',
                    'label' => 'Year',
                    'name' => 'year',
                    'type' => 'number',
                    'min' => 1900,
                    'max' => 2100,
                    'default_value' => date('Y'),
                ),
                array(
                    'key' => 'field_month',
                    'label' => 'Month',
                    'name' => 'month',
                    'type' => 'select',
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
                ),
                array(
                    'key' => 'field_custom_path',
                    'label' => 'Custom Path',
                    'name' => 'custom_path',
                    'type' => 'text',
                    'instructions' => 'Enter a custom path (e.g., "reports/annual/2024")',
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
                array(
                    'key' => 'field_pdf_file',
                    'label' => 'PDF File',
                    'name' => 'pdf_file',
                    'type' => 'file',
                    'return_format' => 'array',
                    'mime_types' => 'pdf',
                    'required' => 1
                ),
                array(
                    'key' => 'field_animation_speed',
                    'label' => 'Page Turn Speed',
                    'name' => 'animation_speed',
                    'type' => 'range',
                    'min' => 500,
                    'max' => 2000,
                    'step' => 100,
                    'default_value' => 1000,
                ),
                array(
                    'key' => 'field_show_thumbnails',
                    'label' => 'Show Page Thumbnails',
                    'name' => 'show_thumbnails',
                    'type' => 'true_false',
                    'ui' => 1,
                    'default_value' => 1,
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
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
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
}