<?php
/**
 * Register Studio Features Custom Post Type
 * スタジオ紹介記事投稿タイプ
 */

function register_studio_features_post_type() {
    $labels = array(
        'name'                  => 'スタジオ紹介記事',
        'singular_name'         => 'スタジオ紹介記事',
        'menu_name'             => 'スタジオ紹介記事',
        'name_admin_bar'        => 'スタジオ紹介記事',
        'add_new'               => '新規追加',
        'add_new_item'          => '新規スタジオ紹介記事を追加',
        'new_item'              => '新規スタジオ紹介記事',
        'edit_item'             => 'スタジオ紹介記事を編集',
        'view_item'             => 'スタジオ紹介記事を表示',
        'all_items'             => 'すべてのスタジオ紹介記事',
        'search_items'          => 'スタジオ紹介記事を検索',
        'parent_item_colon'     => '親スタジオ紹介記事:',
        'not_found'             => 'スタジオ紹介記事が見つかりません',
        'not_found_in_trash'    => 'ゴミ箱にスタジオ紹介記事が見つかりません'
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => false, // カスタムメニューで管理
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'studio-features' ),
        'capability_type'       => 'post',
        'has_archive'           => true,
        'hierarchical'          => false,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-store',
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'show_in_rest'          => true
    );

    register_post_type( 'studio_features', $args );
}
add_action( 'init', 'register_studio_features_post_type' );

/**
 * Register taxonomies for Studio Features
 */
function register_studio_features_taxonomies() {
    // スタジオエリア
    register_taxonomy('studio_area', array('studio_features'), array(
        'hierarchical'          => true,
        'labels'                => array(
            'name'              => 'スタジオエリア',
            'singular_name'     => 'スタジオエリア',
            'search_items'      => 'スタジオエリアを検索',
            'all_items'         => 'すべてのスタジオエリア',
            'parent_item'       => '親エリア',
            'parent_item_colon' => '親エリア:',
            'edit_item'         => 'スタジオエリアを編集',
            'update_item'       => 'スタジオエリアを更新',
            'add_new_item'      => '新規スタジオエリアを追加',
            'new_item_name'     => '新しいスタジオエリア名',
            'menu_name'         => 'スタジオエリア',
        ),
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'studio-area' ),
    ));
    
    // スタジオタイプ  
    register_taxonomy('studio_type', array('studio_features'), array(
        'hierarchical'          => false,
        'labels'                => array(
            'name'              => 'スタジオタイプ',
            'singular_name'     => 'スタジオタイプ',
            'search_items'      => 'スタジオタイプを検索',
            'popular_items'     => '人気のスタジオタイプ',
            'all_items'         => 'すべてのスタジオタイプ',
            'edit_item'         => 'スタジオタイプを編集',
            'update_item'       => 'スタジオタイプを更新',
            'add_new_item'      => '新規スタジオタイプを追加',
            'new_item_name'     => '新しいスタジオタイプ名',
            'menu_name'         => 'スタジオタイプ',
        ),
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'studio-type' ),
    ));
}
add_action( 'init', 'register_studio_features_taxonomies' );

/**
 * Change default title placeholder
 */
function studio_features_title_placeholder( $title ) {
    $screen = get_current_screen();
    
    if ( 'studio_features' == $screen->post_type ) {
        $title = 'スタジオ紹介記事のタイトルを入力（例：【新宿】○○フォトスタジオの魅力を徹底紹介）';
    }
    
    return $title;
}
add_filter( 'enter_title_here', 'studio_features_title_placeholder' );

/**
 * Add custom columns to admin list
 */
function studio_features_custom_columns( $columns ) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['target_studio'] = '対象スタジオ';
    $new_columns['studio_area'] = 'エリア';
    $new_columns['studio_type'] = 'タイプ';
    $new_columns['date'] = $columns['date'];
    
    return $new_columns;
}
add_filter( 'manage_studio_features_posts_columns', 'studio_features_custom_columns' );

/**
 * Populate custom columns
 */
function studio_features_custom_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'target_studio':
            $studio = get_field('target_studio', $post_id);
            if ($studio) {
                echo '<strong>' . esc_html($studio->post_title) . '</strong>';
            } else {
                echo '未設定';
            }
            break;
        case 'studio_area':
            $terms = get_the_terms( $post_id, 'studio_area' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                $term_names = array();
                foreach ( $terms as $term ) {
                    $term_names[] = $term->name;
                }
                echo implode( ', ', $term_names );
            } else {
                echo '未分類';
            }
            break;
        case 'studio_type':
            $terms = get_the_terms( $post_id, 'studio_type' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                $term_names = array();
                foreach ( $terms as $term ) {
                    $term_names[] = $term->name;
                }
                echo implode( ', ', $term_names );
            } else {
                echo '未分類';
            }
            break;
    }
}
add_action( 'manage_studio_features_posts_custom_column', 'studio_features_custom_column_content', 10, 2 );