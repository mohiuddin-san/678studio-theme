<?php
/**
 * Store Access Section
 * Display access information with vertical title and Google Maps
 *
 * Uses studio data helpers for access information
 */

// Get shop_id from various sources
$shop_id = 0;
if (isset($args['shop']['id'])) {
    $shop_id = $args['shop']['id'];
} elseif (isset($_GET['shop_id'])) {
    $shop_id = intval($_GET['shop_id']);
}

// Use the same shop data fetching method as page-studio-detail.php
function fetch_studio_shop_by_id_access($shop_id) {
    // ACF対応版を使用して店舗データを取得
    if (function_exists('get_studio_shop_data_acf')) {
        return get_studio_shop_data_acf($shop_id);
    }

    // フォールバック：従来のシステム
    $cache_key = 'studio_shop_' . $shop_id;
    $cached_shop = get_transient($cache_key);

    if ($cached_shop !== false) {
        return ['shop' => $cached_shop, 'error' => null];
    }

    // 統一キャッシュシステムから全ショップデータを取得
    $all_shops_data = get_cached_studio_data();

    if (isset($all_shops_data['error'])) {
        return ['shop' => null, 'error' => $all_shops_data['error']];
    }

    if (!isset($all_shops_data['shops']) || !is_array($all_shops_data['shops'])) {
        return ['shop' => null, 'error' => 'No shops data available'];
    }

    // 指定IDのショップを検索
    foreach ($all_shops_data['shops'] as $shop) {
        if (isset($shop['id']) && intval($shop['id']) === intval($shop_id)) {
            // 個別キャッシュに保存 (5分)
            set_transient($cache_key, $shop, 300);
            return ['shop' => $shop, 'error' => null];
        }
    }

    return ['shop' => null, 'error' => 'Shop not found'];
}

// Get shop data using the same method as page-studio-detail.php
$shop_result = fetch_studio_shop_by_id_access($shop_id);
if ($shop_result && !$shop_result['error'] && $shop_result['shop']) {
    $shop = $shop_result['shop'];
} elseif (isset($args['shop'])) {
    // Fallback to args
    $shop = $args['shop'];
} else {
    $shop = array();
}

// Don't render if no shop data
if (empty($shop)) {
    return;
}

// Function to extract and validate map embed content (same as page-studio-detail.php)
function get_map_embed_content_access($shop) {
    $map_url = !empty($shop['map_url']) ? trim($shop['map_url']) : '';

    if (empty($map_url)) {
        return false;
    }

    // Check if map_url contains a complete iframe tag
    if (strpos($map_url, '<iframe') !== false && strpos($map_url, '</iframe>') !== false) {
        // Fix common spacing issues in iframe tags
        $fixed_map_url = $map_url;
        $fixed_map_url = str_replace('<iframesrc=', '<iframe src=', $fixed_map_url);
        $fixed_map_url = preg_replace('/(<iframe[^>]*?)([a-z]+="[^"]*")([a-z]+=)/i', '$1$2 $3', $fixed_map_url);
        $fixed_map_url = preg_replace('/([a-z]+="[^"]*")([a-z]+=)/i', '$1 $2', $fixed_map_url);

        // Validate that it's a Google Maps iframe
        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $fixed_map_url, $matches)) {
            $src_url = $matches[1];
            if (filter_var($src_url, FILTER_VALIDATE_URL) &&
                (strpos($src_url, 'google.com/maps/embed') !== false ||
                 strpos($src_url, 'maps.google.com') !== false)) {
                return [
                    'type' => 'iframe',
                    'content' => $fixed_map_url
                ];
            }
        }
    }

    // Check if map_url is a direct Google Maps embed URL
    if (filter_var($map_url, FILTER_VALIDATE_URL) &&
        (strpos($map_url, 'google.com/maps/embed') !== false ||
         strpos($map_url, 'maps.google.com') !== false)) {
        return [
            'type' => 'url',
            'content' => $map_url
        ];
    }

    return false;
}

$map_embed_data = get_map_embed_content_access($shop);

// Debug information removed
?>

<section class="store-access-section">
    <div class="store-access-section__container">
        <!-- Vertical Title -->
        <div class="store-access-section__vertical-title">
            <span class="store-access-section__circle">●</span>
            <h2 class="store-access-section__title">アクセス</h2>
            <span class="store-access-section__title-en">Access</span>
        </div>

        <!-- Main Content Area -->
        <div class="store-access-section__main">
            <!-- Top Block: Access Title -->
            <div class="store-access-section__header">
                <!-- Access Title with Icon -->
                <div class="store-access-section__store-name">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-access-title-icon.svg" alt="" class="store-access-section__name-icon">
                    <h3 class="store-access-section__name">アクセス</h3>
                </div>
            </div>

            <!-- Bottom Block: Access Info + Google Maps -->
            <div class="store-access-section__body">
                <!-- Left: Access Information -->
                <div class="store-access-section__content">
                    <!-- Access Methods -->
                    <div class="store-access-section__access-info">
                        <!-- Access Details from Custom Fields -->
                        <div class="store-access-section__access-routes">
                            <?php
                            // アクセス詳細を取得（管理画面のアクセス詳細フィールドをそのまま表示）
                            $access_details = isset($shop['access_details']) ? $shop['access_details'] : '';

                            if (!empty($access_details)) {
                                echo '<div class="store-access-section__route-item">';
                                echo '<h5 class="store-access-section__route-title">アクセス詳細</h5>';
                                echo '<div class="store-access-section__route-details">';
                                // 改行を<br>タグに変換してそのまま表示
                                echo '<p>' . nl2br(esc_html($access_details)) . '</p>';
                                echo '</div>';
                                echo '</div>';
                            } else {
                                // フォールバック：アクセス情報がない場合
                                echo '<div class="store-access-section__route-item">';
                                echo '<h5 class="store-access-section__route-title">アクセス情報</h5>';
                                echo '<div class="store-access-section__route-details">';
                                echo '<p>詳細なアクセス情報については、お電話にてお問い合わせください。</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Google Maps -->
                <div class="store-access-section__map">
                    <?php if ($map_embed_data): ?>
                        <?php if ($map_embed_data['type'] === 'iframe'): ?>
                        <!-- Complete iframe tag from database -->
                        <div class="store-access-section__map-iframe-container">
                            <?php
                            // Output iframe with proper sanitization
                            echo wp_kses($map_embed_data['content'], [
                                'iframe' => [
                                    'src' => [],
                                    'width' => [],
                                    'height' => [],
                                    'style' => [],
                                    'allowfullscreen' => [],
                                    'loading' => [],
                                    'referrerpolicy' => [],
                                    'frameborder' => [],
                                    'marginheight' => [],
                                    'marginwidth' => [],
                                    'scrolling' => []
                                ]
                            ]);
                            ?>
                        </div>
                        <?php else: ?>
                        <!-- Direct URL to Google Maps embed -->
                        <iframe src="<?php echo esc_url($map_embed_data['content']); ?>"
                                class="store-access-section__map-iframe"
                                width="100%"
                                height="400"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Fallback: Link to Google Maps -->
                        <div class="store-access-section__map-placeholder">
                            <p>地図が利用できません</p>
                            <a href="<?php echo esc_url($shop['map_url'] ?? 'https://maps.app.goo.gl/659nXgwsXYb3dbYH7'); ?>"
                               target="_blank">Google Mapsで表示</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mobile Layout -->
<section class="store-access-section-mobile">
    <div class="store-access-section-mobile__container">
        <!-- Vertical Title -->
        <div class="store-access-section-mobile__vertical-title">
            <span class="store-access-section-mobile__circle">●</span>
            <h2 class="store-access-section-mobile__title">アクセス</h2>
            <span class="store-access-section-mobile__title-en">Access</span>
        </div>

        <!-- Access Title with Icon -->
        <div class="store-access-section-mobile__store-name">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-access-title-icon.svg" alt="" class="store-access-section-mobile__name-icon">
            <h3 class="store-access-section-mobile__name">アクセス</h3>
        </div>

        <!-- Access Information -->
        <div class="store-access-section-mobile__access-info">
            <!-- Access Details from Custom Fields -->
            <div class="store-access-section-mobile__access-routes">
                <?php
                // アクセス詳細を取得（管理画面のアクセス詳細フィールドをそのまま表示）
                $access_details = isset($shop['access_details']) ? $shop['access_details'] : '';

                if (!empty($access_details)) {
                    echo '<div class="store-access-section-mobile__route-item">';
                    echo '<h5 class="store-access-section-mobile__route-title">アクセス詳細</h5>';
                    echo '<div class="store-access-section-mobile__route-details">';
                    // 改行を<br>タグに変換してそのまま表示
                    echo '<p>' . nl2br(esc_html($access_details)) . '</p>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    // フォールバック：アクセス情報がない場合
                    echo '<div class="store-access-section-mobile__route-item">';
                    echo '<h5 class="store-access-section-mobile__route-title">アクセス情報</h5>';
                    echo '<div class="store-access-section-mobile__route-details">';
                    echo '<p>詳細なアクセス情報については、お電話にてお問い合わせください。</p>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- Google Maps -->
        <div class="store-access-section-mobile__map">
            <?php if ($map_embed_data): ?>
                <?php if ($map_embed_data['type'] === 'iframe'): ?>
                <!-- Complete iframe tag from database -->
                <div class="store-access-section-mobile__map-iframe-container">
                    <?php
                    // Output iframe with proper sanitization
                    echo wp_kses($map_embed_data['content'], [
                        'iframe' => [
                            'src' => [],
                            'width' => [],
                            'height' => [],
                            'style' => [],
                            'allowfullscreen' => [],
                            'loading' => [],
                            'referrerpolicy' => [],
                            'frameborder' => [],
                            'marginheight' => [],
                            'marginwidth' => [],
                            'scrolling' => []
                        ]
                    ]);
                    ?>
                </div>
                <?php else: ?>
                <!-- Direct URL to Google Maps embed -->
                <iframe src="<?php echo esc_url($map_embed_data['content']); ?>"
                        class="store-access-section-mobile__map-iframe"
                        width="100%"
                        height="400"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                <?php endif; ?>
            <?php else: ?>
                <!-- Fallback: Link to Google Maps -->
                <div class="store-access-section-mobile__map-placeholder">
                    <p>地図が利用できません</p>
                    <a href="<?php echo esc_url($shop['map_url'] ?? 'https://maps.app.goo.gl/659nXgwsXYb3dbYH7'); ?>"
                       target="_blank">Google Mapsで表示</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>