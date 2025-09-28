<?php
/**
 * Studio Shop Name Management
 * 店舗名・支店名の自動管理システム
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ACFフィールド保存時に投稿タイトルを自動更新
 */
function auto_update_studio_shop_title($post_id) {
    // studio_shops投稿タイプのみ対象
    if (get_post_type($post_id) !== 'studio_shops') {
        return;
    }

    // 自動保存やリビジョンは除外
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    // ACFフィールドから値を取得
    $store_name_base = get_field('store_name_base', $post_id);
    $branch_name = get_field('branch_name', $post_id);

    // 少なくとも基本店舗名は必要
    if (empty($store_name_base)) {
        return;
    }

    // 新しいタイトルを構築
    $new_title = trim($store_name_base . ($branch_name ? ' ' . $branch_name : ''));

    // 現在のタイトルと異なる場合のみ更新
    $current_post = get_post($post_id);
    if ($current_post->post_title !== $new_title) {
        // 無限ループを防ぐためフックを一時的に削除
        remove_action('acf/save_post', 'auto_update_studio_shop_title', 20);

        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $new_title
        ));

        // フックを再度追加
        add_action('acf/save_post', 'auto_update_studio_shop_title', 20);
    }
}
add_action('acf/save_post', 'auto_update_studio_shop_title', 20);

/**
 * 管理画面のカスタムカラムを追加
 */
function add_studio_shop_admin_columns($columns) {
    $new_columns = array();

    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;

        // タイトルの後に新しいカラムを追加
        if ($key === 'title') {
            $new_columns['store_name_base'] = '基本店舗名';
            $new_columns['branch_name'] = '支店名';
        }
    }

    return $new_columns;
}
add_filter('manage_studio_shops_posts_columns', 'add_studio_shop_admin_columns');

/**
 * カスタムカラムの内容を表示
 */
function display_studio_shop_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'store_name_base':
            $store_name_base = get_field('store_name_base', $post_id);
            echo $store_name_base ? esc_html($store_name_base) : '—';
            break;

        case 'branch_name':
            $branch_name = get_field('branch_name', $post_id);
            echo $branch_name ? esc_html($branch_name) : '—';
            break;
    }
}
add_action('manage_studio_shops_posts_custom_column', 'display_studio_shop_admin_column_content', 10, 2);

/**
 * カスタムカラムをソート可能にする
 */
function make_studio_shop_columns_sortable($columns) {
    $columns['store_name_base'] = 'store_name_base';
    $columns['branch_name'] = 'branch_name';
    return $columns;
}
add_filter('manage_edit-studio_shops_sortable_columns', 'make_studio_shop_columns_sortable');

/**
 * 管理画面でのスタイル追加
 */
function studio_shop_admin_styles() {
    global $post_type;
    if ($post_type === 'studio_shops') {
        ?>
        <style>
        .column-store_name_base,
        .column-branch_name {
            width: 15%;
        }

        .acf-field[data-name="store_name"] {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }

        .acf-field[data-name="store_name"] .acf-label {
            color: #666;
        }

        .acf-field[data-name="store_name"] .acf-input {
            opacity: 0.7;
        }

        /* 新しいフィールドのハイライト */
        .acf-field[data-name="store_name_base"],
        .acf-field[data-name="branch_name"] {
            border-left: 4px solid #0073aa;
            padding-left: 16px;
        }

        .acf-field[data-name="store_name_base"] .acf-label label,
        .acf-field[data-name="branch_name"] .acf-label label {
            font-weight: 600;
            color: #0073aa;
        }
        </style>
        <?php
    }
}
add_action('admin_head', 'studio_shop_admin_styles');

/**
 * 管理画面で説明文を追加
 */
function studio_shop_admin_notices() {
    global $post_type, $pagenow;

    if ($post_type === 'studio_shops' && ($pagenow === 'post.php' || $pagenow === 'post-new.php')) {
        ?>
        <div class="notice notice-info" style="margin-top: 20px;">
            <p><strong>店舗名管理について:</strong></p>
            <ul style="margin-left: 20px;">
                <li>「基本店舗名」には店舗ブランド名（例: スタジオアリス）を入力</li>
                <li>「支店名」には店舗の場所（例: 新宿東口店）を入力</li>
                <li>投稿タイトルは自動的に「基本店舗名 + 支店名」で更新されます</li>
                <li>「店舗名（旧フィールド）」は既存データとの互換性用です</li>
            </ul>
        </div>
        <?php
    }
}
add_action('admin_notices', 'studio_shop_admin_notices');

/**
 * データ移行用のユーティリティ関数
 */
function migrate_existing_shop_names() {
    if (!current_user_can('administrator')) {
        return;
    }

    $shops = get_posts(array(
        'post_type' => 'studio_shops',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    $migrated = 0;

    foreach ($shops as $shop) {
        $store_name_base = get_field('store_name_base', $shop->ID);
        $branch_name = get_field('branch_name', $shop->ID);

        // まだ分離されていない場合のみ処理
        if (empty($store_name_base) && empty($branch_name)) {
            $original_name = get_field('store_name', $shop->ID) ?: $shop->post_title;

            if (!empty($original_name)) {
                // スペースで分離
                $name_parts = explode(' ', $original_name, 2);

                if (count($name_parts) === 2) {
                    update_field('store_name_base', $name_parts[0], $shop->ID);
                    update_field('branch_name', $name_parts[1], $shop->ID);
                } else {
                    update_field('store_name_base', $original_name, $shop->ID);
                }

                $migrated++;
            }
        }
    }

    return $migrated;
}

/**
 * 管理者向けマイグレーションツール
 */
function studio_shop_migration_tool() {
    if (!current_user_can('administrator')) {
        return;
    }

    if (isset($_POST['migrate_shop_names']) && wp_verify_nonce($_POST['migration_nonce'], 'migrate_shop_names')) {
        $migrated = migrate_existing_shop_names();
        echo '<div class="notice notice-success"><p>' . $migrated . '件の店舗データを移行しました。</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>店舗名データ移行ツール</h1>
        <p>既存の店舗名データを新しい形式（基本店舗名 + 支店名）に移行します。</p>

        <form method="post" onsubmit="return confirm('データ移行を実行しますか？この操作は元に戻せません。');">
            <?php wp_nonce_field('migrate_shop_names', 'migration_nonce'); ?>
            <input type="submit" name="migrate_shop_names" class="button button-primary" value="データ移行を実行">
        </form>

        <h2>現在の状況</h2>
        <?php
        $total_shops = wp_count_posts('studio_shops')->publish;
        $migrated_shops = 0;

        $shops = get_posts(array(
            'post_type' => 'studio_shops',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));

        foreach ($shops as $shop) {
            $store_name_base = get_field('store_name_base', $shop->ID);
            if (!empty($store_name_base)) {
                $migrated_shops++;
            }
        }
        ?>

        <p>総店舗数: <?php echo $total_shops; ?></p>
        <p>移行済み: <?php echo $migrated_shops; ?></p>
        <p>未移行: <?php echo $total_shops - $migrated_shops; ?></p>
    </div>
    <?php
}

/**
 * 管理者メニューに移行ツールを追加
 */
function add_studio_shop_migration_menu() {
    add_management_page(
        '店舗名データ移行',
        '店舗名データ移行',
        'manage_options',
        'studio-shop-migration',
        'studio_shop_migration_tool'
    );
}
add_action('admin_menu', 'add_studio_shop_migration_menu');