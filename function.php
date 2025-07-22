<?php
/**
 * 678 Studio functions and definitions
 *
 * @package 678Studio
 * @version 1.0
 */

// Load theme CSS and JS
function theme_enqueue_assets() {
    wp_enqueue_style('678studio-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'theme_enqueue_assets');

// Add theme support
add_theme_support('title-tag');
add_theme_support('post-thumbnails');
add_theme_support('menus');

// Register menu
function theme_register_menus() {
    register_nav_menu('header-menu', __('Header Menu', '678studio'));
}
add_action('init', 'theme_register_menus');
add_shortcode('xserver_gallery_full', function () {
    // full code as I gave earlier (FTP connect, folder list, image loop)
});
add_action('init', 'theme_register_menus');
