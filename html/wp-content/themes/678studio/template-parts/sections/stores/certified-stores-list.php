<?php
/**
 * Certified Stores List Section
 * ロクナナハチ撮影認定店舗一覧セクション
 */

// 既存のヘルパー関数を使用（store-search-results.phpと同じ）
function get_minimum_plan_price($shop) {
    if (empty($shop['photo_plans'])) {
        return null;
    }

    $min_price = PHP_INT_MAX;
    foreach ($shop['photo_plans'] as $plan) {
        if (!empty($plan['plan_price']) && is_numeric($plan['plan_price'])) {
            $min_price = min($min_price, (int)$plan['plan_price']);
        }
    }

    return $min_price === PHP_INT_MAX ? null : $min_price;
}

function get_minimum_plan_duration($shop) {
    if (empty($shop['photo_plans'])) {
        return null;
    }

    $min_duration = PHP_INT_MAX;
    foreach ($shop['photo_plans'] as $plan) {
        if (!empty($plan['plan_duration']) && is_numeric($plan['plan_duration'])) {
            $min_duration = min($min_duration, (int)$plan['plan_duration']);
        }
    }

    return $min_duration === PHP_INT_MAX ? null : $min_duration;
}

// 認定店舗データを取得（検索パラメータを考慮）
function fetch_certified_shops() {
    // 検索パラメータを取得
    $search_keyword = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $search_prefecture = isset($_GET['prefecture']) ? sanitize_text_field($_GET['prefecture']) : '';

    if (function_exists('get_all_studio_shops_data')) {
        $data = get_all_studio_shops_data();
    } else {
        $data = get_cached_studio_data();
    }

    if (isset($data['error'])) {
        return ['shops' => [], 'error' => $data['error']];
    }

    // 認定店舗のみフィルタリング + 検索条件を適用
    $certified_shops = [];
    foreach ($data['shops'] as $shop) {
        // 認定店舗チェック
        if (empty($shop['is_certified_store'])) {
            continue;
        }

        // キーワード検索のフィルタリング
        if (!empty($search_keyword)) {
            $search_targets = [
                $shop['name'] ?? '',
                $shop['address'] ?? '',
                $shop['store_introduction'] ?? '',
                $shop['prefecture'] ?? ''
            ];

            $match_found = false;
            foreach ($search_targets as $target) {
                if (stripos($target, $search_keyword) !== false) {
                    $match_found = true;
                    break;
                }
            }

            if (!$match_found) {
                continue;
            }
        }

        // 都道府県検索のフィルタリング
        if (!empty($search_prefecture)) {
            $shop_prefecture = $shop['prefecture'] ?? '';
            if ($shop_prefecture !== $search_prefecture) {
                continue;
            }
        }

        $certified_shops[] = $shop;
    }

    return ['shops' => $certified_shops, 'error' => null];
}

$certified_data = fetch_certified_shops();
$certified_shops = $certified_data['shops'];
?>

<section class="certified-stores-list">
    <div class="certified-stores-list__container">
        <!-- 縦書きタイトル（Stickyエリア） -->
        <div class="certified-stores-list__vertical-title">
            <span class="certified-stores-list__circle">●</span>
            <h2 class="certified-stores-list__title">Certified Store</h2>
        </div>

        <!-- メインコンテンツエリア -->
        <div class="certified-stores-list__main">
        <!-- ヘッダー部分 -->
        <div class="certified-stores-list__header">
            <div class="certified-stores-list__title-wrapper">
                <h1 class="certified-stores-list__main-title">
                    ロクナナハチ撮影認定店舗
                    <div class="certified-stores-list__badge">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.5 10L9.16667 11.6667L12.5 8.33333M18.3333 10C18.3333 14.6024 14.6024 18.3333 10 18.3333C5.39763 18.3333 1.66667 14.6024 1.66667 10C1.66667 5.39763 5.39763 1.66667 10 1.66667C14.6024 1.66667 18.3333 5.39763 18.3333 10Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="certified-stores-list__badge-text">認証済み</span>
                    </div>
                </h1>
            </div>
            <p class="certified-stores-list__subtitle">
                ロクナナハチ撮影用の、撮影・ヘアメイクの技術講習を<br>
                受講した店舗を認定店舗としています
            </p>
        </div>

        <!-- 装飾要素 -->
        <div class="certified-stores-list__decorations">
            <div class="certified-stores-list__wave-1">
                <svg width="57" height="33" viewBox="0 0 57 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class="wave-line-1" d="M0 1C5.70965 1 5.70965 8.99 11.4128 8.99C17.116 8.99 17.1225 1 22.8321 1C28.5418 1 28.5418 8.99 34.2514 8.99C39.9611 8.99 39.9611 1 45.6707 1C51.3803 1 51.3803 8.99 57.09 8.99" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                    <path class="wave-line-2" d="M0 14C5.70965 14 5.70965 21.99 11.4128 21.99C17.116 21.99 17.1225 14 22.8321 14C28.5418 14 28.5418 21.99 34.2514 21.99C39.9611 21.99 39.9611 14 45.6707 14C51.3803 14 51.3803 21.99 57.09 21.99" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                    <path class="wave-line-3" d="M0 26C5.70965 26 5.70965 33.99 11.4128 33.99C17.116 33.99 17.1225 26 22.8321 26C28.5418 26 28.5418 33.99 34.2514 33.99C39.9611 33.99 39.9611 26 45.6707 26C51.3803 26 51.3803 33.99 57.09 33.99" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                </svg>
            </div>
            <div class="certified-stores-list__wave-2">
                <svg width="120" height="150" viewBox="0 0 120 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- 複数の波線要素 -->
                    <path class="wave-2-line-1" d="M0 10C10 10 10 20 20 20C30 20 30 10 40 10C50 10 50 20 60 20" stroke="#F39556" stroke-width="1.5" stroke-miterlimit="10"/>
                    <path class="wave-2-line-2" d="M0 25C10 25 10 35 20 35C30 35 30 25 40 25C50 25 50 35 60 35" stroke="#F39556" stroke-width="1.5" stroke-miterlimit="10"/>
                    <path class="wave-2-line-3" d="M0 40C10 40 10 50 20 50C30 50 30 40 40 40C50 40 50 50 60 50" stroke="#F39556" stroke-width="1.5" stroke-miterlimit="10"/>
                </svg>
            </div>
        </div>

        <!-- 店舗カードグリッド -->
        <div class="certified-stores-list__cards">
            <?php if (!empty($certified_shops)): ?>
                <?php foreach ($certified_shops as $shop): ?>
                <div class="certified-store-card" onclick="location.href='<?php echo home_url('/studio-detail/?shop_id=' . $shop['id']); ?>'" style="cursor: pointer;">
                    <!-- 店舗画像 -->
                    <div class="certified-store-card__image">
                        <?php
                        $image_src = '';
                        if (!empty($shop['main_image'])) {
                            if (strpos($shop['main_image'], 'data:image') === 0) {
                                $image_src = $shop['main_image'];
                            } else {
                                $image_src = esc_url($shop['main_image']);
                            }
                        } elseif (!empty($shop['image_urls']) && !empty($shop['image_urls'][0])) {
                            if (strpos($shop['image_urls'][0], 'data:image') === 0) {
                                $image_src = $shop['image_urls'][0];
                            } else {
                                $image_src = esc_url($shop['image_urls'][0]);
                            }
                        } else {
                            $image_src = get_template_directory_uri() . '/assets/images/cardpic-sample.jpg';
                        }
                        ?>
                        <img src="<?php echo $image_src; ?>" alt="<?php echo esc_attr($shop['name'] ?? 'スタジオ写真'); ?>">
                    </div>

                    <!-- 店舗情報 -->
                    <div class="certified-store-card__content">
                        <!-- 認証バッジ -->
                        <div class="certified-store-card__badge">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 8L7.33333 9.33333L10 6.66667M14.6667 8C14.6667 11.6819 11.6819 14.6667 8 14.6667C4.31809 14.6667 1.33333 11.6819 1.33333 8C1.33333 4.31809 4.31809 1.33333 8 1.33333C11.6819 1.33333 14.6667 4.31809 14.6667 8Z" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="certified-store-card__badge-text">
                                <?php
                                if (function_exists('get_shop_display_name')) {
                                    $names = get_shop_display_name($shop, 'separated');
                                    if (!empty($names['branch'])) {
                                        echo esc_html($names['store']);
                                    } else {
                                        echo esc_html($names['store']);
                                    }
                                } else {
                                    echo esc_html($shop['name'] ?? '店舗名');
                                }
                                ?>
                            </span>
                        </div>

                        <!-- 店舗名 -->
                        <div class="certified-store-card__name">
                            <?php
                            if (function_exists('get_shop_display_name')) {
                                $names = get_shop_display_name($shop, 'separated');
                                if (!empty($names['branch'])) {
                                    echo '<div class="certified-store-card__branch-name">' . esc_html($names['branch']) . '</div>';
                                }
                            } else {
                                echo '<div class="certified-store-card__branch-name">' . esc_html($shop['name'] ?? '店舗名') . '</div>';
                            }
                            ?>
                        </div>

                        <!-- 所在地 -->
                        <div class="certified-store-card__location">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 8.66667C9.10457 8.66667 10 7.77124 10 6.66667C10 5.5621 9.10457 4.66667 8 4.66667C6.89543 4.66667 6 5.5621 6 6.66667C6 7.77124 6.89543 8.66667 8 8.66667Z" stroke="#666666" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 1.33333C6.14348 1.33333 4.36301 2.07083 3.05025 3.38359C1.7375 4.69636 1 6.47681 1 8.33333C1 9.76667 1.31333 10.8933 2.08 11.88L8 14.6667L13.92 11.88C14.6867 10.8933 15 9.76667 15 8.33333C15 6.47681 14.2625 4.69636 12.9497 3.38359C11.637 2.07083 9.85652 1.33333 8 1.33333Z" stroke="#666666" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>
                                <?php
                                $prefecture_display = '';
                                $address = $shop['address'] ?? '';
                                if (!empty($address) && preg_match('/^(.+?[都道府県])/u', $address, $matches)) {
                                    $prefecture_display = $matches[1];
                                } else if (!empty($shop['prefecture'])) {
                                    $prefecture_display = $shop['prefecture'];
                                }
                                echo esc_html($prefecture_display ?: 'N/A');
                                ?>
                            </span>
                        </div>

                        <!-- 価格・時間情報 -->
                        <div class="certified-store-card__info">
                            <?php $min_price = get_minimum_plan_price($shop); ?>
                            <?php if ($min_price): ?>
                            <div class="certified-store-card__info-item">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8 14.6667C11.6819 14.6667 14.6667 11.6819 14.6667 8C14.6667 4.31809 11.6819 1.33333 8 1.33333C4.31809 1.33333 1.33333 4.31809 1.33333 8C1.33333 11.6819 4.31809 14.6667 8 14.6667Z" stroke="#F39556" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M5.33333 6C5.33333 5.44772 5.78105 5 6.33333 5H9.66667C10.219 5 10.6667 5.44772 10.6667 6V7.33333H5.33333V6Z" stroke="#F39556" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 9.33333V11" stroke="#F39556" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span><?php echo number_format($min_price); ?>円〜</span>
                            </div>
                            <?php endif; ?>

                            <?php $min_duration = get_minimum_plan_duration($shop); ?>
                            <?php if ($min_duration): ?>
                            <div class="certified-store-card__info-item">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8 14.6667C11.6819 14.6667 14.6667 11.6819 14.6667 8C14.6667 4.31809 11.6819 1.33333 8 1.33333C4.31809 1.33333 1.33333 4.31809 1.33333 8C1.33333 11.6819 4.31809 14.6667 8 14.6667Z" stroke="#F39556" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 4V8L10.6667 9.33333" stroke="#F39556" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span><?php echo $min_duration; ?>分〜</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- 店舗紹介文 -->
                        <?php if (!empty($shop['store_introduction'])): ?>
                        <div class="certified-store-card__introduction">
                            <?php
                            $introduction = $shop['store_introduction'];
                            $lines = explode("\n", $introduction);
                            $display_text = implode("\n", array_slice($lines, 0, 3));

                            if (mb_strlen($display_text) > 90) {
                                $display_text = mb_substr($display_text, 0, 90) . '...';
                            } elseif (count($lines) > 3) {
                                $display_text .= '...';
                            }

                            echo wp_kses($display_text, ['br' => []]);
                            ?>
                        </div>
                        <?php endif; ?>

                        <!-- ボタン -->
                        <div class="certified-store-card__buttons">
                            <a href="<?php echo home_url('/studio-detail/?shop_id=' . $shop['id']); ?>"
                               class="certified-store-card__button certified-store-card__button--primary"
                               onclick="event.stopPropagation();">
                                詳しく見る
                            </a>
                            <a href="<?php echo home_url('/studio-reservation/?shop_id=' . $shop['id']); ?>"
                               class="certified-store-card__button certified-store-card__button--secondary"
                               onclick="event.stopPropagation();">
                                ご予約相談
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="certified-stores-list__no-results">
                    <?php
                    $search_keyword = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
                    $search_prefecture = isset($_GET['prefecture']) ? sanitize_text_field($_GET['prefecture']) : '';

                    if (!empty($search_keyword) || !empty($search_prefecture)) {
                        echo '<p>検索条件に一致する認定店舗が見つかりませんでした。</p>';
                    } else {
                        echo '<p>現在、認定店舗の情報を読み込み中です。</p>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>

        </div> <!-- certified-stores-list__main の終了 -->
    </div> <!-- certified-stores-list__container の終了 -->
</section>