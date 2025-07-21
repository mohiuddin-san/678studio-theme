<?php
/**
 * Register Media Achievements Custom Post Type
 */

function register_media_achievements_post_type() {
    $labels = array(
        'name'                  => 'メディア実績',
        'singular_name'         => 'メディア実績',
        'menu_name'             => 'メディア実績',
        'name_admin_bar'        => 'メディア実績',
        'add_new'               => '新規追加',
        'add_new_item'          => '新規メディア実績を追加',
        'new_item'              => '新規メディア実績',
        'edit_item'             => 'メディア実績を編集',
        'view_item'             => 'メディア実績を表示',
        'all_items'             => 'すべてのメディア実績',
        'search_items'          => 'メディア実績を検索',
        'parent_item_colon'     => '親メディア実績:',
        'not_found'             => 'メディア実績が見つかりません',
        'not_found_in_trash'    => 'ゴミ箱にメディア実績が見つかりません'
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'media-achievements' ),
        'capability_type'       => 'post',
        'has_archive'           => false,
        'hierarchical'          => false,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-images-alt2',
        'supports'              => array( 'title', 'thumbnail', 'custom-fields' ),
        'show_in_rest'          => true
    );

    register_post_type( 'media_achievements', $args );
}
add_action( 'init', 'register_media_achievements_post_type' );

/**
 * Change default title placeholder
 */
function media_achievements_title_placeholder( $title ) {
    $screen = get_current_screen();
    
    if ( 'media_achievements' == $screen->post_type ) {
        $title = 'メディア名を入力';
    }
    
    return $title;
}
add_filter( 'enter_title_here', 'media_achievements_title_placeholder' );