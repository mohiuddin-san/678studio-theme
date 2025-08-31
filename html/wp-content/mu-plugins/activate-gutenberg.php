<?php
/**
 * Force activate Gutenberg plugin
 */
add_action('init', function() {
    if (!is_plugin_active('gutenberg/gutenberg.php')) {
        $active_plugins = get_option('active_plugins', array());
        if (!in_array('gutenberg/gutenberg.php', $active_plugins)) {
            $active_plugins[] = 'gutenberg/gutenberg.php';
            update_option('active_plugins', $active_plugins);
        }
    }
}, 1);