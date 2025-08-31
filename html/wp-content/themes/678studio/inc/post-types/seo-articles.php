<?php
/**
 * Register SEO Articles Custom Post Type
 * SEO戦略記事投稿タイプ
 */

function register_seo_articles_post_type() {
    $labels = array(
        'name'                  => 'ブログ記事',
        'singular_name'         => 'ブログ記事',
        'menu_name'             => 'ブログ記事',
        'name_admin_bar'        => 'ブログ記事',
        'add_new'               => '新規追加',
        'add_new_item'          => '新しいブログ記事を追加',
        'new_item'              => '新しいブログ記事',
        'edit_item'             => 'ブログ記事を編集',
        'view_item'             => 'ブログ記事を表示',
        'all_items'             => 'すべてのブログ記事',
        'search_items'          => 'ブログ記事を検索',
        'parent_item_colon'     => '親ブログ記事:',
        'not_found'             => 'ブログ記事が見つかりません',
        'not_found_in_trash'    => 'ゴミ箱にブログ記事が見つかりません'
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'articles' ),
        'capability_type'       => 'post',
        'has_archive'           => true,
        'hierarchical'          => false,
        'menu_position'         => 7,
        'menu_icon'             => 'dashicons-chart-line',
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'show_in_rest'          => true
    );

    register_post_type( 'seo_articles', $args );
}
add_action( 'init', 'register_seo_articles_post_type' );

/**
 * Register taxonomies for SEO Articles
 */
function register_seo_articles_taxonomies() {
    // カテゴリー
    register_taxonomy('article_category', array('seo_articles'), array(
        'hierarchical'          => true,
        'labels'                => array(
            'name'              => 'カテゴリー',
            'singular_name'     => 'カテゴリー',
            'search_items'      => 'カテゴリーを検索',
            'all_items'         => 'すべてのカテゴリー',
            'parent_item'       => '親カテゴリー',
            'parent_item_colon' => '親カテゴリー:',
            'edit_item'         => 'カテゴリーを編集',
            'update_item'       => 'カテゴリーを更新',
            'add_new_item'      => '新規カテゴリーを追加',
            'new_item_name'     => '新しいカテゴリー名',
            'menu_name'         => 'カテゴリー',
        ),
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'article-category' ),
    ));
    
    // タグ
    register_taxonomy('article_tag', array('seo_articles'), array(
        'hierarchical'          => false,
        'labels'                => array(
            'name'              => 'タグ',
            'singular_name'     => 'タグ',
            'search_items'      => 'タグを検索',
            'popular_items'     => '人気のタグ',
            'all_items'         => 'すべてのタグ',
            'edit_item'         => 'タグを編集',
            'update_item'       => 'タグを更新',
            'add_new_item'      => '新規タグを追加',
            'new_item_name'     => '新しいタグ名',
            'menu_name'         => 'タグ',
        ),
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'article-tag' ),
    ));
}
add_action( 'init', 'register_seo_articles_taxonomies' );

/**
 * Change default title placeholder
 */
function seo_articles_title_placeholder( $title ) {
    $screen = get_current_screen();
    
    if ( 'seo_articles' == $screen->post_type ) {
        $title = 'ブログ記事のタイトルを入力（例：東京で還暦祝いの記念写真を撮影する完全ガイド）';
    }
    
    return $title;
}
add_filter( 'enter_title_here', 'seo_articles_title_placeholder' );

/**
 * Add custom columns to admin list
 */
function seo_articles_custom_columns( $columns ) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['article_category'] = 'カテゴリー';
    $new_columns['article_tag'] = 'タグ';
    $new_columns['content_strategy'] = '戦略';
    $new_columns['date'] = $columns['date'];
    
    return $new_columns;
}
add_filter( 'manage_seo_articles_posts_columns', 'seo_articles_custom_columns' );

/**
 * Populate custom columns
 */
function seo_articles_custom_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'content_strategy':
            $strategy = get_field('content_strategy', $post_id);
            if ($strategy) {
                $strategies = array(
                    'comparison' => '比較記事',
                    'guide' => '完全ガイド',
                    'ranking' => 'ランキング',
                    'howto' => 'ハウツー',
                    'trend' => 'トレンド',
                );
                echo isset($strategies[$strategy]) ? $strategies[$strategy] : $strategy;
            } else {
                echo '未設定';
            }
            break;
        case 'article_category':
            $terms = get_the_terms( $post_id, 'article_category' );
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
        case 'article_tag':
            $terms = get_the_terms( $post_id, 'article_tag' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                $term_names = array();
                foreach ( $terms as $term ) {
                    $term_names[] = $term->name;
                }
                echo implode( ', ', $term_names );
            } else {
                echo '未設定';
            }
            break;
    }
}
add_action( 'manage_seo_articles_posts_custom_column', 'seo_articles_custom_column_content', 10, 2 );