<?php
/**
 * Registered Stores List Section - 登録店舗一覧セクション
 * 登録店舗のみを表示（画像なし）
 */

// 登録店舗データを取得（検索パラメータを考慮）
function get_registered_shops_only() {
    // 検索パラメータを取得
    $search_keyword = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $search_prefecture = isset($_GET['prefecture']) ? sanitize_text_field($_GET['prefecture']) : '';

    // Phase 3-1: 新しいヘルパー関数を使用
    if (function_exists('get_all_studio_shops_data')) {
        $data = get_all_studio_shops_data();
    } else {
        // フォールバック：既存システム
        $data = get_cached_studio_data();
    }

    if (isset($data['error'])) {
        return ['shops' => [], 'error' => $data['error']];
    }

    $all_shops = $data['shops'];
    $registered_shops = [];

    // 登録店舗（認定店舗以外）のみを抽出 + 検索条件を適用
    foreach ($all_shops as $shop) {
        // 認定店舗を除外
        if (!empty($shop['is_certified_store'])) {
            continue;
        }

        // キーワード検索のフィルタリング
        if (!empty($search_keyword)) {
            // 店舗名を正しく取得（get_shop_display_name関数を使用）
            $shop_name = '';
            if (function_exists('get_shop_display_name')) {
                $shop_name = get_shop_display_name($shop, 'full');
            } else {
                $shop_name = $shop['name'] ?? '';
            }

            // 検索対象フィールド
            $search_targets = [
                $shop_name,
                $shop['name'] ?? '',
                $shop['store_name'] ?? '',
                $shop['branch_name'] ?? '',
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
            // addressフィールドから都道府県を抽出
            $shop_address = $shop['address'] ?? '';
            $extracted_prefecture = '';

            // 都道府県パターンマッチング
            if (preg_match('/(北海道|.+?[都道府県])/u', $shop_address, $matches)) {
                $extracted_prefecture = $matches[1];
            }

            if ($extracted_prefecture !== $search_prefecture) {
                continue;
            }
        }

        $registered_shops[] = $shop;
    }

    return ['shops' => $registered_shops, 'error' => null];
}

// 登録店舗データを取得
$registered_data = get_registered_shops_only();
$registered_shops = $registered_data['shops'];

// 最小価格取得ヘルパー関数
function get_minimum_plan_price_registered($shop) {
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

// 最小時間取得ヘルパー関数
function get_minimum_plan_duration_registered($shop) {
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
?>

<section class="registered-stores-list">
    <div class="registered-stores-list__container">
        <!-- 縦書きタイトル（Stickyエリア） -->
        <div class="registered-stores-list__vertical-title">
            <span class="registered-stores-list__circle">●</span>
            <h2 class="registered-stores-list__title">登録店舗 Registered Store</h2>
        </div>

        <!-- メインコンテンツエリア -->
        <div class="registered-stores-list__main">
            <!-- ヘッダー部分 -->
            <div class="registered-stores-list__header">
                <!-- タイトル部分 -->
                <h1 class="registered-stores-list__main-title">
                    登録店舗
                </h1>

                <!-- 右端装飾要素 -->
                <div class="registered-stores-list__right-wave">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/certified-title-section-wave.svg" alt="装飾">
                </div>
            </div>


            <!-- 登録店舗カード一覧 -->
            <div class="registered-stores-list__cards splide" id="registered-stores-slider">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php if (!empty($registered_shops)): ?>
                            <?php foreach ($registered_shops as $shop): ?>
                                <li class="splide__slide">
                                    <div class="registered-store-card" onclick="location.href='<?php echo home_url('/studio-detail/?shop_id=' . $shop['id']); ?>'" style="cursor: pointer;">
                            <div class="registered-store-card__content">
                                <div class="registered-store-card__name-wrapper">
                                    <div class="registered-store-card__name">
                                        <?php
                                        if (function_exists('get_shop_display_name')) {
                                            $names = get_shop_display_name($shop, 'separated');
                                            // 店舗名がある場合は店舗名、なければ支店名を表示
                                            if (!empty($names['store'])) {
                                                echo esc_html($names['store']);
                                            } elseif (!empty($names['branch'])) {
                                                echo esc_html($names['branch']);
                                            } else {
                                                echo '登録店舗';
                                            }
                                        } else {
                                            echo esc_html($shop['name'] ?? '登録店舗');
                                        }
                                        ?>
                                    </div>
                                    <?php
                                    // 支店名（店舗名と支店名の両方がある場合のみ表示）
                                    if (function_exists('get_shop_display_name')) {
                                        $names = get_shop_display_name($shop, 'separated');
                                        if (!empty($names['store']) && !empty($names['branch'])) {
                                            echo '<div class="registered-store-card__branch-name">' . esc_html($names['branch']) . '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="registered-store-card__location">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/store-card-map-icon.svg" alt="所在地">
                                    <span>
                                        <?php
                                        // 住所表示ロジック
                                        try {
                                            $address_fields = ['address', 'prefecture', 'location', 'city', 'region'];

                                            $found_address = '';
                                            foreach ($address_fields as $field) {
                                                if (!empty($shop[$field])) {
                                                    $found_address = $shop[$field];
                                                    break;
                                                }
                                            }

                                            if (!empty($found_address)) {
                                                $display_address = '';

                                                // 都道府県+市区町村パターン（マルチバイト対応）
                                                if (preg_match('/(.+?[都道府県])(.+?[市区町村])/u', $found_address, $matches)) {
                                                    $display_address = $matches[1] . $matches[2];
                                                }
                                                // 市区町村のみ
                                                else if (preg_match('/(.+?[市区町村])/u', $found_address, $matches)) {
                                                    $display_address = $matches[1];
                                                }
                                                // 区のみ（東京都推定）
                                                else if (preg_match('/(.+?区)/u', $found_address, $matches)) {
                                                    $display_address = '東京都' . $matches[1];
                                                }
                                                // フォールバック：最初の部分
                                                else {
                                                    $parts = preg_split('/[0-9\-]/u', $found_address);
                                                    $display_address = $parts[0] ?? $found_address;
                                                    if (mb_strlen($display_address) > 15) {
                                                        $display_address = mb_substr($display_address, 0, 15) . '...';
                                                    }
                                                }

                                                echo esc_html(trim($display_address));
                                            } else {
                                                echo '住所不明';
                                            }
                                        } catch (Exception $e) {
                                            echo 'エラー: ' . $e->getMessage();
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="registered-store-card__info">
                                    <?php $min_price = get_minimum_plan_price_registered($shop); ?>
                                    <?php if ($min_price): ?>
                                        <div class="registered-store-card__info-item">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/store-card-price-icon.svg" alt="料金">
                                            <span><?php echo number_format($min_price); ?>円〜</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php $min_duration = get_minimum_plan_duration_registered($shop); ?>
                                    <?php if ($min_duration): ?>
                                        <div class="registered-store-card__info-item">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/store-card-clock-icon.svg" alt="時間">
                                            <span><?php echo $min_duration; ?>分〜</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="registered-store-card__introduction">
                                    <?php
                                    $introduction = $shop['store_introduction'] ?? '';
                                    if (!empty($introduction)) {
                                        // brタグと改行を削除してスペースに置き換え
                                        $introduction = str_replace(['<br>', '<br/>', '<br />', "\n", "\r"], ' ', $introduction);
                                        // 連続するスペースを1つにまとめる
                                        $introduction = preg_replace('/\s+/', ' ', $introduction);
                                        $introduction = trim($introduction);

                                        // 文字数制限（60文字）
                                        if (mb_strlen($introduction) > 60) {
                                            echo esc_html(mb_substr($introduction, 0, 60) . '...');
                                        } else {
                                            echo esc_html($introduction);
                                        }
                                    } else {
                                        echo 'ロクナナハチ撮影登録店舗です。';
                                    }
                                    ?>
                                </div>
                                <div class="registered-store-card__buttons">
                                    <a href="<?php echo home_url('/studio-detail/?shop_id=' . $shop['id']); ?>"
                                       class="registered-store-card__button registered-store-card__button--primary"
                                       onclick="event.stopPropagation();">詳しく見る</a>
                                    <a href="<?php echo home_url('/studio-reservation/?shop_id=' . $shop['id']); ?>"
                                       class="registered-store-card__button registered-store-card__button--secondary"
                                       onclick="event.stopPropagation();">ご予約相談</a>
                                </div>
                            </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="splide__slide">
                                <div class="registered-stores-list__no-stores">
                                    <?php
                                    $search_keyword = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
                                    $search_prefecture = isset($_GET['prefecture']) ? sanitize_text_field($_GET['prefecture']) : '';

                                    if (!empty($search_keyword) || !empty($search_prefecture)) {
                                        echo '<p>検索条件に一致する登録店舗が見つかりませんでした。</p>';
                                    } else {
                                        echo '<p>現在、登録店舗はありません。</p>';
                                    }
                                    ?>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- 左右矢印ボタン -->
            <div class="registered-stores-list__arrows">
                <button class="registered-stores-list__arrow registered-stores-list__arrow--left" aria-label="前のページ">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/card-arrow-left.svg" alt="前へ">
                </button>
                <button class="registered-stores-list__arrow registered-stores-list__arrow--right" aria-label="次のページ">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/card-arrow-right.svg" alt="次へ">
                </button>
            </div>
        </div>
    </div>
</section>