<?php
/**
 * Store Search Results Section - 店舗一覧・検索結果表示
 * Used on /stores/ page to show all stores or search results
 */

// ヘルパー関数: 最小価格を取得
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

// ヘルパー関数: 最小時間を取得
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

function fetch_studio_shops($search_query = '', $prefecture = '', $page = 1, $per_page = 6) {
    // functions.phpのキャッシュ機能を使用
    $data = get_cached_studio_data();
    
    
    if (isset($data['error'])) {
        return ['certified_shops' => [], 'regular_shops' => [], 'total' => 0, 'error' => $data['error']];
    }
    
    $filtered_shops = $data['shops'];
    
    // テキスト検索
    if (!empty($search_query)) {
        $filtered_shops = array_filter($filtered_shops, function($shop) use ($search_query) {
            $name_match = stripos($shop['name'] ?? '', $search_query) !== false;
            $station_match = stripos($shop['nearest_station'] ?? '', $search_query) !== false;
            $address_match = stripos($shop['address'] ?? '', $search_query) !== false;
            return $name_match || $station_match || $address_match;
        });
    }
    
    // 都道府県検索
    if (!empty($prefecture)) {
        $filtered_shops = array_filter($filtered_shops, function($shop) use ($prefecture) {
            $address = $shop['address'] ?? '';
            
            // 直接的なマッチ（住所に都道府県名が含まれている場合）
            $address_match = stripos($address, $prefecture) !== false;
            
            // 東京都の特別区のマッピング
            if (!$address_match && $prefecture === '東京都') {
                $tokyo_wards = [
                    '千代田区', '中央区', '港区', '新宿区', '文京区', '台東区', '墨田区', '江東区',
                    '品川区', '目黒区', '大田区', '世田谷区', '渋谷区', '中野区', '杉並区', '豊島区',
                    '北区', '荒川区', '板橋区', '練馬区', '足立区', '葛飾区', '江戸川区'
                ];
                
                foreach ($tokyo_wards as $ward) {
                    if (stripos($address, $ward) !== false) {
                        $address_match = true;
                        break;
                    }
                }
            }
            
            return $address_match;
        });
    }
    
    // 認定店と登録店舗に分割
    $certified_shops = [];
    $regular_shops = [];
    
    foreach ($filtered_shops as $shop) {
        if (!empty($shop['is_certified_store'])) {
            $certified_shops[] = $shop;
        } else {
            $regular_shops[] = $shop;
        }
    }
    
    $total_shops = count($filtered_shops);
    $total_pages = max(1, ceil($total_shops / $per_page));
    $page = min($page, $total_pages);

    return [
        'certified_shops' => $certified_shops,
        'regular_shops' => $regular_shops,
        'total' => $total_shops,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'error' => null
    ];
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$prefecture = isset($_GET['prefecture']) ? sanitize_text_field($_GET['prefecture']) : '';


$shop_data = fetch_studio_shops($search_query, $prefecture, $page);
$certified_shops = $shop_data['certified_shops'];
$regular_shops = $shop_data['regular_shops'];
$total_pages = $shop_data['total_pages'];
$current_page = $shop_data['current_page'];


?>

<section class="store-search-results" id="store-search-results">
  <div class="store-search-results__container">

    <!-- ヘッダーエリア -->
    <div class="store-search-results__header scroll-animate-item" data-delay="0">
      <div class="store-search-results__label">
        <?php get_template_part('template-parts/components/thoughts-label', null, [
            'text' => 'Search for a photo studio'
        ]); ?>
      </div>
      <h1 class="store-search-results__title">
        <?php if (!empty($search_query)): ?>
        「<?php echo esc_html($search_query); ?>」の検索結果
        <?php else: ?>
        お近くの写真館を探す
        <?php endif; ?>
      </h1>
      <p class="store-search-results__subtitle">
        <?php if (!empty($search_query)): ?>
        <?php echo $shop_data['total']; ?>件の写真館が見つかりました
        <?php else: ?>
        全国の写真館でロクナナハチ撮影が受けられます
        <?php endif; ?>
      </p>
    </div>

    <!-- 検索フォーム -->
    <div class="store-search-results__search scroll-animate-item" data-delay="0.2">
      <form class="store-search-results__form" method="GET">
        <div class="store-search-results__input-group">
          <div class="store-search-results__search-box">
            <svg class="store-search-results__search-icon" viewBox="0 0 24 24">
              <path
                d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 0 0 1.48-5.34c-.47-2.78-2.79-5-5.59-5.34a6.505 6.505 0 0 0-7.27 7.27c.34 2.8 2.56 5.12 5.34 5.59a6.5 6.5 0 0 0 5.34-1.48l.27.28v.79l4.25 4.25c.41.41 1.08.41 1.49 0 .41-.41.41-1.08 0-1.49L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
            </svg>
            <input type="text" name="search" placeholder="店舗名・地域・最寄り駅をご入力ください"
              class="store-search-results__search-input" value="<?php echo esc_attr($search_query); ?>">
          </div>

          <!-- 都道府県選択 -->
          <div class="store-search-results__prefecture-select">
            <select name="prefecture" class="store-search-results__prefecture-dropdown">
              <option value="">都道府県を選択</option>
              <option value="北海道" <?php selected($prefecture, '北海道'); ?>>北海道</option>
              <option value="青森県" <?php selected($prefecture, '青森県'); ?>>青森県</option>
              <option value="岩手県" <?php selected($prefecture, '岩手県'); ?>>岩手県</option>
              <option value="宮城県" <?php selected($prefecture, '宮城県'); ?>>宮城県</option>
              <option value="秋田県" <?php selected($prefecture, '秋田県'); ?>>秋田県</option>
              <option value="山形県" <?php selected($prefecture, '山形県'); ?>>山形県</option>
              <option value="福島県" <?php selected($prefecture, '福島県'); ?>>福島県</option>
              <option value="茨城県" <?php selected($prefecture, '茨城県'); ?>>茨城県</option>
              <option value="栃木県" <?php selected($prefecture, '栃木県'); ?>>栃木県</option>
              <option value="群馬県" <?php selected($prefecture, '群馬県'); ?>>群馬県</option>
              <option value="埼玉県" <?php selected($prefecture, '埼玉県'); ?>>埼玉県</option>
              <option value="千葉県" <?php selected($prefecture, '千葉県'); ?>>千葉県</option>
              <option value="東京都" <?php selected($prefecture, '東京都'); ?>>東京都</option>
              <option value="神奈川県" <?php selected($prefecture, '神奈川県'); ?>>神奈川県</option>
              <option value="新潟県" <?php selected($prefecture, '新潟県'); ?>>新潟県</option>
              <option value="富山県" <?php selected($prefecture, '富山県'); ?>>富山県</option>
              <option value="石川県" <?php selected($prefecture, '石川県'); ?>>石川県</option>
              <option value="福井県" <?php selected($prefecture, '福井県'); ?>>福井県</option>
              <option value="山梨県" <?php selected($prefecture, '山梨県'); ?>>山梨県</option>
              <option value="長野県" <?php selected($prefecture, '長野県'); ?>>長野県</option>
              <option value="岐阜県" <?php selected($prefecture, '岐阜県'); ?>>岐阜県</option>
              <option value="静岡県" <?php selected($prefecture, '静岡県'); ?>>静岡県</option>
              <option value="愛知県" <?php selected($prefecture, '愛知県'); ?>>愛知県</option>
              <option value="三重県" <?php selected($prefecture, '三重県'); ?>>三重県</option>
              <option value="滋賀県" <?php selected($prefecture, '滋賀県'); ?>>滋賀県</option>
              <option value="京都府" <?php selected($prefecture, '京都府'); ?>>京都府</option>
              <option value="大阪府" <?php selected($prefecture, '大阪府'); ?>>大阪府</option>
              <option value="兵庫県" <?php selected($prefecture, '兵庫県'); ?>>兵庫県</option>
              <option value="奈良県" <?php selected($prefecture, '奈良県'); ?>>奈良県</option>
              <option value="和歌山県" <?php selected($prefecture, '和歌山県'); ?>>和歌山県</option>
              <option value="鳥取県" <?php selected($prefecture, '鳥取県'); ?>>鳥取県</option>
              <option value="島根県" <?php selected($prefecture, '島根県'); ?>>島根県</option>
              <option value="岡山県" <?php selected($prefecture, '岡山県'); ?>>岡山県</option>
              <option value="広島県" <?php selected($prefecture, '広島県'); ?>>広島県</option>
              <option value="山口県" <?php selected($prefecture, '山口県'); ?>>山口県</option>
              <option value="徳島県" <?php selected($prefecture, '徳島県'); ?>>徳島県</option>
              <option value="香川県" <?php selected($prefecture, '香川県'); ?>>香川県</option>
              <option value="愛媛県" <?php selected($prefecture, '愛媛県'); ?>>愛媛県</option>
              <option value="高知県" <?php selected($prefecture, '高知県'); ?>>高知県</option>
              <option value="福岡県" <?php selected($prefecture, '福岡県'); ?>>福岡県</option>
              <option value="佐賀県" <?php selected($prefecture, '佐賀県'); ?>>佐賀県</option>
              <option value="長崎県" <?php selected($prefecture, '長崎県'); ?>>長崎県</option>
              <option value="熊本県" <?php selected($prefecture, '熊本県'); ?>>熊本県</option>
              <option value="大分県" <?php selected($prefecture, '大分県'); ?>>大分県</option>
              <option value="宮崎県" <?php selected($prefecture, '宮崎県'); ?>>宮崎県</option>
              <option value="鹿児島県" <?php selected($prefecture, '鹿児島県'); ?>>鹿児島県</option>
              <option value="沖縄県" <?php selected($prefecture, '沖縄県'); ?>>沖縄県</option>
            </select>
          </div>
        </div>

        <!-- 検索ボタン -->
        <div class="store-search-results__submit">
          <button type="submit" class="store-search-results__search-btn">検索</button>
        </div>
      </form>
    </div>


    <!-- 認定店セクション -->
    <?php if (!empty($certified_shops)): ?>
    <div class="store-section store-section--certified scroll-animate-item" data-delay="0.6">
      <div class="store-section__header">
        <div class="store-section__badge store-section__badge--certified">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/badge.svg" alt="認定店"
            class="store-section__badge-icon">
          認定店舗
        </div>
        <p class="store-section__description">ロクナナハチ撮影用の、撮影・ヘアメイクの技術講習を受講した店舗を認定店舗としています</p>
      </div>
      <div class="store-search-results__cards-wrapper">
        <div class="store-search-results__cards">
        <?php foreach ($certified_shops as $shop): ?>
        <div class="studio-card studio-card--certified" onclick="location.href='<?php echo home_url('/studio-detail/?shop_id=' . $shop['id']); ?>'" style="cursor: pointer;">
          <div class="studio-card__image">
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

            <!-- 認定店バッジ（画像の左上にオーバーレイ） -->
            <?php if (!empty($shop['is_certified_store'])): ?>
            <div class="studio-card__badge-overlay">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/images/badge.svg" alt="認定店">
            </div>
            <?php endif; ?>

            <div class="studio-card__location">
              <?php
              // 都道府県を表示（住所から抽出または直接取得）
              $prefecture_display = '';
              $address = $shop['address'] ?? '';
              $prefecture_field = $shop['prefecture'] ?? '';

              // 住所から都道府県を抽出
              if (!empty($address) && preg_match('/^(.+?[都道府県])/u', $address, $matches)) {
                $prefecture_display = $matches[1];
              }
              // 住所に都道府県が含まれていない場合は、prefectureフィールドを使用
              else if (!empty($prefecture_field)) {
                $prefecture_display = $prefecture_field;
              }
              // フォールバック
              else if (!empty($address)) {
                // 住所の最初の部分を使用
                $parts = explode(' ', $address);
                $prefecture_display = $parts[0] ?? '';
              }

              echo esc_html($prefecture_display ?: 'N/A');
              ?>
            </div>
          </div>
          <div class="studio-card__content">
            <h3 class="studio-card__name">
              <?php echo nl2br(esc_html($shop['name'] ?? 'Unknown')); ?>
            </h3>

            <!-- 新しいアイコン付きの情報行 -->
            <div class="studio-card__info-row">
              <div class="studio-card__info-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/map_icon.svg" alt="場所"
                  class="studio-card__info-icon">
                <span class="studio-card__info-text">
                  <?php
                  // エラー表示を有効にして問題を特定
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

              <?php $min_price = get_minimum_plan_price($shop); ?>
              <?php if ($min_price): ?>
              <div class="studio-card__info-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/yen_icon.svg" alt="料金"
                  class="studio-card__info-icon">
                <span class="studio-card__info-text"><?php echo number_format($min_price); ?>円〜</span>
              </div>
              <?php endif; ?>

              <?php $min_duration = get_minimum_plan_duration($shop); ?>
              <?php if ($min_duration): ?>
              <div class="studio-card__info-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/clock-icon.svg" alt="時間"
                  class="studio-card__info-icon">
                <span class="studio-card__info-text"><?php echo $min_duration; ?>分〜</span>
              </div>
              <?php endif; ?>
            </div>

            <!-- 店舗紹介文 -->
            <?php if (!empty($shop['store_introduction'])): ?>
            <div class="studio-card__introduction">
              <?php 
              $introduction = $shop['store_introduction'];
              // 改行を含む場合は最初の3行程度まで表示
              $lines = explode("\n", $introduction);
              $display_text = implode("\n", array_slice($lines, 0, 3));
              
              // 文字数制限（90文字）
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
            <div class="studio-card__buttons">
              <div class="studio-card__button-item">
                <a href="<?php echo home_url('/studio-reservation/?shop_id=' . $shop['id']); ?>"
                  class="certified-card-button certified-card-button--booking" onclick="event.stopPropagation();">
                  ご予約相談
                </a>
              </div>
              <div class="studio-card__button-item">
                <a href="<?php echo home_url('/studio-inquiry/?shop_id=' . $shop['id']); ?>"
                  class="certified-card-button certified-card-button--contact" onclick="event.stopPropagation();">
                  お問い合わせ
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- 登録店舗セクション -->
    <?php if (!empty($regular_shops)): ?>
    <div class="store-section store-section--regular scroll-animate-item" data-delay="0.8">
      <div class="store-section__header">
        <div class="store-section__badge store-section__badge--regular">
          登録店舗
        </div>
      </div>
      <div class="store-search-results__cards-wrapper">
        <div class="store-search-results__cards">
        <?php foreach ($regular_shops as $shop): ?>
        <div class="studio-card studio-card--regular" onclick="location.href='<?php echo home_url('/studio-detail/?shop_id=' . $shop['id']); ?>'" style="cursor: pointer;">
          <div class="studio-card__content">
            <!-- 最寄り駅ラベル -->
            <div class="studio-card__station-label">
              <?php echo esc_html($shop['nearest_station'] ?? '最寄り駅'); ?>
            </div>

            <!-- 店舗名 -->
            <h3 class="studio-card__name"><?php echo nl2br(esc_html($shop['name'] ?? 'Unknown')); ?></h3>

            <!-- 基本情報 -->
            <div class="studio-card__info">
              <!-- 最寄り駅 -->
              <?php if (!empty($shop['nearest_station'])): ?>
              <div class="studio-card__info-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/map_icon.svg" alt="最寄り駅"
                  class="studio-card__info-icon">
                <span class="studio-card__info-text"><?php echo esc_html($shop['nearest_station']); ?></span>
              </div>
              <?php endif; ?>

              <!-- 住所 -->
              <div class="studio-card__info-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/map_icon.svg" alt="住所"
                  class="studio-card__info-icon">
                <span class="studio-card__info-text">
                  <?php
                  // 住所の整形（認定店舗と同じロジック）
                  $found_address = $shop['address'] ?? '';
                  
                  if (!empty($found_address)) {
                    // 住所が都道府県から始まっているかチェック
                    $prefectures = ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'];
                    
                    $has_prefecture = false;
                    foreach ($prefectures as $pref) {
                      if (strpos($found_address, $pref) === 0) {
                        $has_prefecture = true;
                        break;
                      }
                    }
                    
                    if (!$has_prefecture) {
                      // 都道府県が含まれていない場合、東京都を前に追加
                      echo '東京都' . esc_html($found_address);
                    } else {
                      // 住所に都道府県が含まれている場合
                      if (preg_match('/^(.+?[都道府県])(.+?[市区町村])/', $found_address, $matches)) {
                        echo esc_html($matches[1] . $matches[2]);
                      } else {
                        // フォールバック：住所をそのまま表示（最初の部分のみ）
                        $address_parts = explode(' ', $found_address);
                        echo esc_html($address_parts[0] ?? $found_address);
                      }
                    }
                  } else {
                    echo '住所情報が見つかりません';
                  }
                  ?>
                </span>
              </div>

              <?php $min_price = get_minimum_plan_price($shop); ?>
              <?php if ($min_price): ?>
              <div class="studio-card__info-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/yen_icon.svg" alt="料金"
                  class="studio-card__info-icon">
                <span class="studio-card__info-text"><?php echo number_format($min_price); ?>円〜</span>
              </div>
              <?php endif; ?>

              <?php $min_duration = get_minimum_plan_duration($shop); ?>
              <?php if ($min_duration): ?>
              <div class="studio-card__info-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/clock-icon.svg" alt="時間"
                  class="studio-card__info-icon">
                <span class="studio-card__info-text"><?php echo $min_duration; ?>分〜</span>
              </div>
              <?php endif; ?>
            </div>

            <!-- 店舗紹介文 -->
            <?php if (!empty($shop['store_introduction'])): ?>
            <div class="studio-card__introduction">
              <?php 
              $introduction = $shop['store_introduction'];
              // 改行を含む場合は最初の3行程度まで表示
              $lines = explode("\n", $introduction);
              $display_text = implode("\n", array_slice($lines, 0, 3));
              
              // 文字数制限（90文字）
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
            <div class="studio-card__buttons">
              <div class="studio-card__button-item">
                <a href="<?php echo home_url('/studio-reservation/?shop_id=' . $shop['id']); ?>"
                  class="certified-card-button certified-card-button--booking" onclick="event.stopPropagation();">
                  ご予約相談
                </a>
              </div>
              <div class="studio-card__button-item">
                <a href="<?php echo home_url('/studio-inquiry/?shop_id=' . $shop['id']); ?>"
                  class="certified-card-button certified-card-button--contact" onclick="event.stopPropagation();">
                  お問い合わせ
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- 検索結果が0件の場合 -->
    <?php if (empty($certified_shops) && empty($regular_shops)): ?>
    <div class="store-search-results__no-results">
      <p class="no-results">
        <?php if (!empty($search_query)): ?>
        「<?php echo esc_html($search_query); ?>」に一致する店舗が見つかりませんでした。
        <?php else: ?>
        現在、表示できる店舗がありません。
        <?php endif; ?>
      </p>
    </div>
    <?php endif; ?>

    <!-- ページネーション -->
    <?php if ($total_pages > 1): ?>
    <div class="store-search-results__pagination">
      <a href="?page=<?php echo max(1, $current_page - 1); ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>"
        class="pagination-btn pagination-btn--prev <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
      </a>
      <div class="pagination-numbers">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>"
          class="<?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
      </div>
      <a href="?page=<?php echo min($total_pages, $current_page + 1); ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>"
        class="pagination-btn pagination-btn--next <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>