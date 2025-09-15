<?php
/**
 * Studio Shops Custom Post Type
 * 
 * @package 678studio
 */

// Register Studio Shops Custom Post Type
function register_studio_shops_post_type() {
    $labels = array(
        'name'                  => _x('スタジオ店舗', 'Post Type General Name', '678studio'),
        'singular_name'         => _x('スタジオ店舗', 'Post Type Singular Name', '678studio'),
        'menu_name'             => __('スタジオ店舗', '678studio'),
        'name_admin_bar'        => __('スタジオ店舗', '678studio'),
        'archives'              => __('店舗一覧', '678studio'),
        'attributes'            => __('店舗属性', '678studio'),
        'parent_item_colon'     => __('親店舗:', '678studio'),
        'all_items'             => __('すべての店舗', '678studio'),
        'add_new_item'          => __('新規店舗を追加', '678studio'),
        'add_new'               => __('新規追加', '678studio'),
        'new_item'              => __('新規店舗', '678studio'),
        'edit_item'             => __('店舗を編集', '678studio'),
        'update_item'           => __('店舗を更新', '678studio'),
        'view_item'             => __('店舗を表示', '678studio'),
        'view_items'            => __('店舗一覧を表示', '678studio'),
        'search_items'          => __('店舗を検索', '678studio'),
        'not_found'             => __('店舗が見つかりません', '678studio'),
        'not_found_in_trash'    => __('ゴミ箱に店舗が見つかりません', '678studio'),
        'featured_image'        => __('メイン画像', '678studio'),
        'set_featured_image'    => __('メイン画像を設定', '678studio'),
        'remove_featured_image' => __('メイン画像を削除', '678studio'),
        'use_featured_image'    => __('メイン画像として使用', '678studio'),
        'insert_into_item'      => __('店舗に挿入', '678studio'),
        'uploaded_to_this_item' => __('この店舗にアップロード', '678studio'),
        'items_list'            => __('店舗リスト', '678studio'),
        'items_list_navigation' => __('店舗リストナビゲーション', '678studio'),
        'filter_items_list'     => __('店舗リストをフィルター', '678studio'),
    );
    
    $args = array(
        'label'                 => __('スタジオ店舗', '678studio'),
        'description'           => __('678スタジオの提携店舗情報', '678studio'),
        'labels'                => $labels,
        'supports'              => array('title', 'thumbnail', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-store',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'studios',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'               => false,
        'show_in_rest'          => true,
    );
    
    register_post_type('studio_shops', $args);
}
add_action('init', 'register_studio_shops_post_type', 0);

// Add Prefecture Taxonomy
function register_studio_prefecture_taxonomy() {
    $labels = array(
        'name'                       => _x('都道府県', 'Taxonomy General Name', '678studio'),
        'singular_name'              => _x('都道府県', 'Taxonomy Singular Name', '678studio'),
        'menu_name'                  => __('都道府県', '678studio'),
        'all_items'                  => __('すべての都道府県', '678studio'),
        'parent_item'                => __('親都道府県', '678studio'),
        'parent_item_colon'          => __('親都道府県:', '678studio'),
        'new_item_name'              => __('新規都道府県名', '678studio'),
        'add_new_item'               => __('新規都道府県を追加', '678studio'),
        'edit_item'                  => __('都道府県を編集', '678studio'),
        'update_item'                => __('都道府県を更新', '678studio'),
        'view_item'                  => __('都道府県を表示', '678studio'),
        'separate_items_with_commas' => __('都道府県をカンマで区切る', '678studio'),
        'add_or_remove_items'        => __('都道府県を追加または削除', '678studio'),
        'choose_from_most_used'      => __('よく使われる都道府県から選択', '678studio'),
        'popular_items'              => __('人気の都道府県', '678studio'),
        'search_items'               => __('都道府県を検索', '678studio'),
        'not_found'                  => __('見つかりません', '678studio'),
        'no_terms'                   => __('都道府県なし', '678studio'),
        'items_list'                 => __('都道府県リスト', '678studio'),
        'items_list_navigation'      => __('都道府県リストナビゲーション', '678studio'),
    );
    
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'rewrite'                    => array(
            'slug'         => 'prefecture',
            'with_front'   => false,
            'hierarchical' => true,
        ),
        'show_in_rest'               => true,
    );
    
    register_taxonomy('studio_prefecture', array('studio_shops'), $args);
}
add_action('init', 'register_studio_prefecture_taxonomy', 0);

// Initialize Japanese prefectures
function initialize_prefectures() {
    $prefectures = array(
        '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
        '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
        '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
        '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
        '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
        '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
        '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
    );
    
    foreach ($prefectures as $prefecture) {
        if (!term_exists($prefecture, 'studio_prefecture')) {
            wp_insert_term($prefecture, 'studio_prefecture');
        }
    }
}
add_action('init', 'initialize_prefectures', 10);

// Flush rewrite rules on activation
function studio_shops_rewrite_flush() {
    register_studio_shops_post_type();
    register_studio_prefecture_taxonomy();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'studio_shops_rewrite_flush');