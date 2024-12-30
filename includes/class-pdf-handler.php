<?php
if (!defined('ABSPATH')) {
    exit;
}

class PDF_Flipbook_Handler {
    public function __construct() {
        add_action('acf/save_post', array($this, 'handle_file_upload'), 20);
        add_shortcode('flipbook', array($this, 'render_shortcode'));
    }

    public function get_path_structure($document_type) {
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

    public function parse_path($post_id) {
        $document_type = get_field('document_type', $post_id);
        
        if ($document_type === 'custom') {
            $custom_path = get_field('custom_path', $post_id);
            if (!empty($custom_path)) {
                return trim($custom_path, '/');
            }
        }
        
        $path_structure = $this->get_path_structure($document_type);
        
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
        return rtrim($path, '/');
    }

    public function handle_file_upload($post_id) {
        if (get_post_type($post_id) !== 'flipbook') {
            return;
        }

        // Get the PDF file array from ACF
        $pdf_file = get_field('pdf_file', $post_id);
        if (!$pdf_file || !isset($pdf_file['ID'])) {
            return;
        }

        $upload_dir = wp_upload_dir();
        $path = $this->parse_path($post_id);
        $full_path = $upload_dir['basedir'] . '/' . $path;
        
        // Create directory if it doesn't exist
        if (!file_exists($full_path)) {
            wp_mkdir_p($full_path);
            
            // Create .htaccess to protect directory
            $htaccess = $full_path . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Options -Indexes\n");
            }
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
            error_log("PDF Flipbook: Failed to copy file to: {$new_file_path}");
            return;
        }

        // Update file location in database
        update_post_meta($post_id, '_flipbook_file_path', $new_file_path);
        update_post_meta($post_id, '_flipbook_file_url', str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $new_file_path));
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts);

        if (!$atts['id']) {
            return '';
        }

        // Get file URL from custom meta
        $file_url = get_post_meta($atts['id'], '_flipbook_file_url', true);
        if (!$file_url) {
            return '';
        }

        $speed = get_field('animation_speed', $atts['id']) ?: 1000;
        $thumbnails = get_field('show_thumbnails', $atts['id']);

        ob_start();
        ?>
        <div class="flipbook-container" 
             data-pdf="<?php echo esc_url($file_url); ?>"
             data-speed="<?php echo esc_attr($speed); ?>"
             data-thumbnails="<?php echo esc_attr($thumbnails); ?>">
            <div class="flipbook-viewport">
                <div class="flipbook"></div>
            </div>
            <div class="flipbook-controls">
                <button class="prev-page">Previous</button>
                <span class="page-number">Loading...</span>
                <button class="next-page">Next</button>
            </div>
            <?php if ($thumbnails): ?>
            <div class="flipbook-thumbnails"></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}