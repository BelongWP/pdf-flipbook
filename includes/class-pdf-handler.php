<?php
function get_flipbook_path_structure($document_type) {
    $options = get_option('pdf_flipbook_settings', array());
    
    if (isset($options['document_types'])) {
        foreach ($options['document_types'] as $type) {
            if ($type['type'] === $document_type) {
                return $type['path'];
            }
        }
    }
    
    return '{type}/{year}/{month}'; // Default fallback
}

function parse_flipbook_path($post_id) {
    $document_type = get_field('document_type', $post_id);
    $path_structure = get_flipbook_path_structure($document_type);
    
    // Get all possible variables
    $vars = array(
        '{year}' => get_field('year', $post_id) ?: date('Y'),
        '{month}' => get_field('month', $post_id) ?: date('m'),
        '{issue}' => get_field('issue', $post_id),
        '{title}' => get_the_title($post_id),
        '{id}' => $post_id,
        '{type}' => $document_type
    );
    
    // Replace variables in path
    $path = str_replace(array_keys($vars), array_values($vars), $path_structure);
    
    // Clean up the path
    $path = sanitize_file_name($path);
    $path = rtrim($path, '/');
    
    return $path;
}

function handle_flipbook_file_upload($post_id) {
    if (get_post_type($post_id) !== 'flipbook') {
        return;
    }

    // Get the PDF file array from ACF
    $pdf_file = get_field('pdf_file', $post_id);
    if (!$pdf_file || !isset($pdf_file['ID'])) {
        return;
    }

    $upload_dir = wp_upload_dir();
    $path = parse_flipbook_path($post_id);
    $full_path = $upload_dir['basedir'] . '/' . $path;
    
    // Create directory if it doesn't exist
    if (!file_exists($full_path)) {
        wp_mkdir_p($full_path);
    }
    
    // Get original file
    $original_file = get_attached_file($pdf_file['ID']);
    if (!file_exists($original_file)) {
        return;
    }

    // Create new file path
    $file_name = sanitize_file_name(basename($original_file));
    $new_file_path = $full_path . '/' . $file_name;

    // Move file to new location
    if (!copy($original_file, $new_file_path)) {
        error_log("Failed to copy file to: {$new_file_path}");
        return;
    }

    // Update file location in database
    update_post_meta($post_id, '_flipbook_file_path', $new_file_path);
    update_post_meta($post_id, '_flipbook_file_url', str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $new_file_path));
}

add_action('acf/save_post', 'handle_flipbook_file_upload', 20);
