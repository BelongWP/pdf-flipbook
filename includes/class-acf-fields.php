<?php
function get_document_type_choices() {
    $options = get_option('pdf_flipbook_settings', array());
    $choices = array();
    
    if (isset($options['document_types'])) {
        foreach ($options['document_types'] as $type) {
            $choices[$type['type']] = ucfirst($type['type']);
        }
    }
    
    return $choices;
}

function register_flipbook_acf_fields() {
    acf_add_local_field_group(array(
        'key' => 'group_flipbook_settings',
        'title' => 'Flip Book Settings',
        'fields' => array(
            array(
                'key' => 'field_document_type',
                'label' => 'Document Type',
                'name' => 'document_type',
                'type' => 'select',
                'choices' => get_document_type_choices(),
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
                'key' => 'field_pdf_file',
                'label' => 'PDF File',
                'name' => 'pdf_file',
                'type' => 'file',
                'return_format' => 'array',
                'mime_types' => 'pdf',
                'required' => 1
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
    ));
}
