<?php
/**
 * Common functions for API endpoints
 */

/**
 * Generate image URL based on environment
 * @param string $filename
 * @return string Full URL to the image
 */
function generate_image_url($filename) {
    // Check if we're in a local environment (Docker or WP-CLI)
    $is_local = false;
    
    // Check HTTP_HOST if available (web requests)
    if (isset($_SERVER['HTTP_HOST'])) {
        $is_local = ($_SERVER['HTTP_HOST'] === 'localhost:8080' || $_SERVER['HTTP_HOST'] === 'localhost');
    }
    
    // Check DOCUMENT_ROOT for Docker environment (WP-CLI doesn't have HTTP_HOST)
    if (!$is_local && isset($_SERVER['DOCUMENT_ROOT'])) {
        $is_local = (strpos($_SERVER['DOCUMENT_ROOT'], '/var/www/html') === 0);
    }
    
    // Check for WP_HOME constant (WordPress environment)
    if (!$is_local && defined('WP_HOME')) {
        $is_local = (strpos(WP_HOME, 'localhost') !== false);
    }
    
    if ($is_local) {
        return 'http://localhost:8080/studio_shop_galary/' . $filename;
    } else {
        return 'https://678photo.com/studio_shop_galary/' . $filename;
    }
}

/**
 * Get upload directory path
 * @return string Upload directory path
 */
function get_upload_directory() {
    return $_SERVER['DOCUMENT_ROOT'] . '/studio_shop_galary/';
}
?>