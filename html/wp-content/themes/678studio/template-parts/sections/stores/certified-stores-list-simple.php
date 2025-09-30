<?php
/**
 * Certified Stores List Section - 認定店舗一覧セクション
 * ロクナナハチ撮影認定店舗のみを表示
 */

// 認定店舗データを取得（検索パラメータを考慮）
function get_certified_shops_only() {
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
    $certified_shops = [];

    // 認定店舗のみを抽出 + 検索条件を適用
    foreach ($all_shops as $shop) {
        // 認定店舗チェック
        if (empty($shop['is_certified_store'])) {
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

        $certified_shops[] = $shop;
    }

    return ['shops' => $certified_shops, 'error' => null];
}

// 認定店舗データを取得
$certified_data = get_certified_shops_only();
$certified_shops = $certified_data['shops'];

// 最小価格取得ヘルパー関数
function get_minimum_plan_price_certified($shop) {
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
function get_minimum_plan_duration_certified($shop) {
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

<section class="certified-stores-list">
    <div class="certified-stores-list__container">
        <!-- 縦書きタイトル（Stickyエリア） -->
        <div class="certified-stores-list__vertical-title">
            <span class="certified-stores-list__circle">●</span>
            <h2 class="certified-stores-list__title">認定店舗 Certified Stores</h2>
        </div>

        <!-- メインコンテンツエリア -->
        <div class="certified-stores-list__main">
            <!-- ヘッダー部分 -->
            <div class="certified-stores-list__header">
                <!-- (1) タイトル上部のアイコン -->
                <div class="certified-stores-list__top-icon">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/Certified-title-top.svg" alt="認定店舗アイコン">
                </div>

                <!-- (2) タイトル部分 -->
                <div class="certified-stores-list__title-wrapper">
                    <h1 class="certified-stores-list__main-title">
                        ロクナナハチ撮影認定店舗
                        <!-- 青い認証バッジ -->
                        <div class="certified-stores-list__certification-badge">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/badge.svg" alt="認定店" class="certified-stores-list__badge-icon">
                        </div>
                    </h1>
                </div>

                <!-- (3) サブタイトル -->
                <p class="certified-stores-list__subtitle">
                    ロクナナハチ撮影用の、撮影・ヘアメイクの技術講習を<br>
                    受講した店舗を認定店舗としています
                </p>

                <!-- (4) 右端装飾要素 -->
                <div class="certified-stores-list__right-wave">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/certified-title-section-wave.svg" alt="装飾">
                </div>
            </div>


            <!-- 認定店舗カード一覧 -->
            <div class="certified-stores-list__cards splide" id="certified-stores-slider">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php if (!empty($certified_shops)): ?>
                            <?php foreach ($certified_shops as $shop): ?>
                                <li class="splide__slide">
                                    <div class="certified-store-card" onclick="location.href='<?php echo home_url('/studio-detail/?shop_id=' . $shop['id']); ?>'" style="cursor: pointer;">
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
                            <div class="certified-store-card__content">
                                <div class="certified-store-card__badge">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/badge.svg" alt="認定店" class="certified-store-card__badge-icon">
                                    <span class="certified-store-card__badge-text">
                                        <?php
                                        if (function_exists('get_shop_display_name')) {
                                            $names = get_shop_display_name($shop, 'separated');
                                            // 店舗名がある場合は店舗名、なければ支店名を表示
                                            if (!empty($names['store'])) {
                                                echo esc_html($names['store']);
                                            } elseif (!empty($names['branch'])) {
                                                echo esc_html($names['branch']);
                                            } else {
                                                echo '認定店舗';
                                            }
                                        } else {
                                            echo esc_html($shop['name'] ?? '認定店舗');
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="certified-store-card__name">
                                    <?php
                                    if (function_exists('get_shop_display_name')) {
                                        $names = get_shop_display_name($shop, 'separated');
                                        // 店舗名と支店名の両方がある場合のみ支店名を表示
                                        if (!empty($names['store']) && !empty($names['branch'])) {
                                            echo '<div class="certified-store-card__branch-name">' . esc_html($names['branch']) . '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="certified-store-card__location">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/store-card-map-icon.svg" alt="所在地">
                                    <span>
                                        <?php
                                        // 住所表示ロジック（元のstoreセクションと同じ）
                                        try {
                                            // 考えられる住所フィールドをすべてチェック
                                            $address_fields = ['address', 'prefecture', 'location', 'city', 'region'];

                                            $found_address = '';
                                            foreach ($address_fields as $field) {
                                                if (!empty($shop[$field])) {
                                                    $found_address = $shop[$field];
                                                    break;
                                                }
                                            }

                                            if (!empty($found_address)) {
                                                // シンプルな住所表示ロジック
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
                                <div class="certified-store-card__info">
                                    <?php $min_price = get_minimum_plan_price_certified($shop); ?>
                                    <?php if ($min_price): ?>
                                        <div class="certified-store-card__info-item">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/store-card-price-icon.svg" alt="料金">
                                            <span><?php echo number_format($min_price); ?>円〜</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php $min_duration = get_minimum_plan_duration_certified($shop); ?>
                                    <?php if ($min_duration): ?>
                                        <div class="certified-store-card__info-item">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/store-card-clock-icon.svg" alt="時間">
                                            <span><?php echo $min_duration; ?>分〜</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="certified-store-card__introduction">
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
                                        echo 'ロクナナハチ撮影認定店舗です。';
                                    }
                                    ?>
                                </div>
                                <div class="certified-store-card__buttons">
                                    <a href="<?php echo home_url('/studio-detail/?shop_id=' . $shop['id']); ?>"
                                       class="certified-store-card__button certified-store-card__button--primary"
                                       onclick="event.stopPropagation();">詳しく見る</a>
                                    <a href="<?php echo home_url('/studio-reservation/?shop_id=' . $shop['id']); ?>"
                                       class="certified-store-card__button certified-store-card__button--secondary"
                                       onclick="event.stopPropagation();">ご予約相談</a>
                                </div>
                            </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="splide__slide">
                                <div class="certified-stores-list__no-stores">
                                    <?php
                                    $search_keyword = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
                                    $search_prefecture = isset($_GET['prefecture']) ? sanitize_text_field($_GET['prefecture']) : '';

                                    if (!empty($search_keyword) || !empty($search_prefecture)) {
                                        echo '<p>検索条件に一致する認定店舗が見つかりませんでした。</p>';
                                    } else {
                                        echo '<p>現在、認定店舗はありません。</p>';
                                    }
                                    ?>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- 左右矢印ボタン -->
            <div class="certified-stores-list__arrows">
                <button class="certified-stores-list__arrow certified-stores-list__arrow--left" aria-label="前のページ">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/card-arrow-left.svg" alt="前へ">
                </button>
                <button class="certified-stores-list__arrow certified-stores-list__arrow--right" aria-label="次のページ">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/card-arrow-right.svg" alt="次へ">
                </button>
            </div>
        </div>
    </div>
</section>