<?php
/**
 * Template Name: 店舗詳細
 * Description: 写真館の店舗詳細ページテンプレート
 */

get_header();

// Function to fetch shop data by ID using unified caching system
function fetch_studio_shop_by_id($shop_id) {
    // 個別ショップキャッシュをチェック
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

// Function to extract and validate map embed content
function get_map_embed_content($shop) {
    $map_url = !empty($shop['map_url']) ? trim($shop['map_url']) : '';
    
    if (empty($map_url)) {
        return null;
    }

    // Check if map_url contains a complete iframe tag
    if (strpos($map_url, '<iframe') !== false && strpos($map_url, '</iframe>') !== false) {
        // Fix common spacing issues in iframe tags
        $fixed_map_url = $map_url;
        $fixed_map_url = str_replace('<iframesrc=', '<iframe src=', $fixed_map_url);
        $fixed_map_url = preg_replace('/(<iframe[^>]*?)([a-z]+="[^"]*")([a-z]+=)/i', '$1$2 $3', $fixed_map_url);
        $fixed_map_url = preg_replace('/([a-z]+="[^"]*")([a-z]+=)/i', '$1 $2', $fixed_map_url);
        
        // Extract src URL for validation
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
    
    // Check if it's a direct embed URL
    if (filter_var($map_url, FILTER_VALIDATE_URL) && 
        (strpos($map_url, 'google.com/maps/embed') !== false || 
         strpos($map_url, 'maps.google.com') !== false)) {
        return [
            'type' => 'url',
            'content' => $map_url
        ];
    }

    return null;
}

// Get shop ID from URL
$shop_id = isset($_GET['shop_id']) ? intval($_GET['shop_id']) : 0;

// Clear cache if requested (for admins only)
if (isset($_GET['clear_cache']) && current_user_can('administrator')) {
    // 統一キャッシュシステムをクリア
    if (function_exists('clear_studio_data_cache')) {
        clear_studio_data_cache();
    }
    // 個別キャッシュもクリア
    delete_transient('studio_shop_' . $shop_id);
    wp_redirect(remove_query_arg('clear_cache'));
    exit;
}

// Validate shop ID
if ($shop_id <= 0) {
    wp_die('不正な店舗IDです。', 'エラー', ['response' => 404]);
}

$shop_data = fetch_studio_shop_by_id($shop_id);
$shop = $shop_data['shop'];

// Handle shop not found
if (!$shop) {
    wp_die('指定された店舗が見つかりませんでした。', 'エラー', ['response' => 404]);
}

$map_embed_data = get_map_embed_content($shop);

// SEO情報は統一システム（StudioSEOManager）で自動処理されます

// Debugging output for admins
// if (current_user_can('administrator')) {
//     echo '<div style="background: #fff; padding: 10px; border: 1px solid #ccc; margin: 20px;">';
//     echo '<h3>Debug Info</h3>';
//     echo '<p>Shop ID: ' . esc_html($shop_id) . '</p>';
//     echo '<p>Map Embed Data: ' . esc_html($map_embed_data ? $map_embed_data['type'] . ' - ' . substr($map_embed_data['content'], 0, 200) . '...' : 'None') . '</p>';
//     echo '<p>Map URL from API: ' . esc_html($shop['map_url'] ?? 'None') . '</p>';
//     echo '</div>';
// }
// ?>

<main class="main-content single-store" data-shop-id="<?php echo esc_attr($shop_id); ?>">

  <!-- Breadcrumb -->
  <?php get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
      ['text' => 'TOP', 'url' => home_url()],
      ['text' => '店舗一覧', 'url' => home_url('/stores/')],
      ['text' => esc_html($shop['name'] ?? '店舗詳細'), 'url' => '']
    ]
  ]); ?>

  <!-- Store Hero Section -->
  <section class="store-hero">
    <div class="store-hero__container">
      <div class="store-hero__info">
        <div class="store-hero__category"><?php echo esc_html($shop['name'] ?? 'ロクナナハチ撮影店舗'); ?></div>
        <h1 class="store-hero__title">ロクナナハチ撮影店舗</h1>
      </div>
      <div class="store-hero__image">
        <?php
        // メイン画像の表示優先順位: main_image -> image_urls[0] -> デフォルト画像
        $hero_image_src = '';
        if (!empty($shop['main_image'])) {
            // Base64データかURLかを判定
            if (strpos($shop['main_image'], 'data:image') === 0) {
                $hero_image_src = $shop['main_image']; // Base64データはそのまま使用
            } else {
                $hero_image_src = esc_url($shop['main_image']); // URLの場合はエスケープ
            }
        } elseif (!empty($shop['image_urls']) && !empty($shop['image_urls'][0])) {
            // ギャラリー画像をフォールバック
            if (strpos($shop['image_urls'][0], 'data:image') === 0) {
                $hero_image_src = $shop['image_urls'][0];
            } else {
                $hero_image_src = esc_url($shop['image_urls'][0]);
            }
        } else {
            // デフォルト画像
            $hero_image_src = get_template_directory_uri() . '/assets/images/cardpic-sample.jpg';
        }
        ?>
        <img src="<?php echo $hero_image_src; ?>" alt="店舗内観" class="store-hero__image-img">
      </div>
    </div>
  </section>

  <!-- Store Basic Info Section -->
  <section class="store-basic-info">
    <div class="store-basic-info__container">
      <h2 class="store-basic-info__heading">
        基本情報
        <img class="store-basic-info__underline"
          src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt="">
      </h2>
      <dl class="store-basic-info__list">
        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">店舗名</dt>
          <dd class="store-basic-info__data"><?php echo esc_html($shop['name'] ?? 'N/A'); ?></dd>
        </div>
        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">住所</dt>
          <dd class="store-basic-info__data"><?php echo esc_html($shop['address'] ?? 'N/A'); ?></dd>
        </div>
        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">電話番号</dt>
          <dd class="store-basic-info__data">
            <a href="tel:<?php echo esc_attr($shop['phone'] ?? ''); ?>"
              class="store-basic-info__phone"><?php echo esc_html($shop['phone'] ?? 'N/A'); ?></a>
          </dd>
        </div>
        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">最寄り駅</dt>
          <dd class="store-basic-info__data"><?php echo esc_html($shop['nearest_station'] ?? 'N/A'); ?></dd>
        </div>
        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">営業時間</dt>
          <dd class="store-basic-info__data"><?php echo esc_html($shop['business_hours'] ?? 'N/A'); ?></dd>
        </div>
        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">定休日</dt>
          <dd class="store-basic-info__data"><?php echo esc_html($shop['holidays'] ?? 'N/A'); ?></dd>
        </div>
      </dl>
    </div>
  </section>

  <!-- Gallery Section -->
   <section class="store-gallery">
    <div class="store-basic-info__container">
      <h2 class="store-basic-info__heading">
        撮影ギャラリー
        <img class="store-basic-info__underline"
          src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt="">
      </h2>
    </div>
    <div class="store-gallery__slider">
      <div class="store-gallery__track">
        <?php 
        $gallery_images = [];
        
        // メインギャラリー画像を収集（簡素化されたギャラリーシステム）
        if (!empty($shop['main_gallery_images']) && is_array($shop['main_gallery_images'])) {
            foreach ($shop['main_gallery_images'] as $image) {
                // 新しいデータ構造では $image['url'] に画像URLが含まれる
                if (is_array($image) && isset($image['url'])) {
                    $gallery_images[] = $image['url'];
                } elseif (is_string($image)) {
                    // 後方互換性のため
                    $gallery_images[] = $image;
                }
            }
        }
        
        // 重複を除去
        $gallery_images = array_unique($gallery_images);
        
        // 画像の表示
        if (empty($gallery_images)): ?>
          <div class="store-gallery__no-images">
            <p>まだギャラリーに画像が登録されていません。</p>
          </div>
        <?php else:
          foreach ($gallery_images as $index => $image_url): 
            // ギャラリー画像のBase64データかURLかを判定
            $gallery_image_src = '';
            $gallery_image_full = '';
            if (strpos($image_url, 'data:image') === 0) {
                // Base64データはそのまま使用
                $gallery_image_src = $image_url;
                $gallery_image_full = $image_url;
            } else {
                // URLの場合はエスケープ
                $gallery_image_src = esc_url($image_url);
                $gallery_image_full = esc_url($image_url);
            }
        ?>
          <div class="store-gallery__item">
            <img src="<?php echo $gallery_image_src; ?>" alt="ギャラリー画像 <?php echo $index + 1; ?>" data-full-image="<?php echo $gallery_image_full; ?>">
            <div class="store-gallery__overlay">
              <svg class="store-gallery__icon" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="16" r="10" stroke="white" stroke-width="2"/>
                <path d="M23 23L30 30" stroke="white" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
          </div>
        <?php 
          endforeach; 
        endif; ?>
      </div>
    </div>
  </section>

  <!-- Access Section -->
  <section class="store-access">
    <div class="store-basic-info__container">
      <h2 class="store-basic-info__heading">
        アクセス
        <img class="store-basic-info__underline"
          src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt="">
      </h2>

      <!-- Google Map -->
      <div class="store-access__map">
        <?php if ($map_embed_data): ?>
          <?php if ($map_embed_data['type'] === 'iframe'): ?>
            <!-- Complete iframe tag from database -->
            <div class="map-iframe-container">
              <?php 
              // Output iframe with proper sanitization
              echo wp_kses($map_embed_data['content'], [
                'iframe' => [
                  'src' => true,
                  'width' => true,
                  'height' => true,
                  'style' => true,
                  'allowfullscreen' => true,
                  'loading' => true,
                  'referrerpolicy' => true,
                  'frameborder' => true,
                  'border' => true,
                  'class' => true,
                  'id' => true,
                  'title' => true,
                  'scrolling' => true,
                  'marginwidth' => true,
                  'marginheight' => true
                ]
              ]);
              ?>
            </div>
          <?php else: ?>
            <!-- Direct URL -->
            <iframe src="<?php echo esc_url($map_embed_data['content']); ?>" 
                    width="100%" height="400" 
                    style="border:0; display: block;"
                    allowfullscreen="" loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          <?php endif; ?>
        <?php else: ?>
        <p>地図を表示できません。以下のリンクからアクセスしてください：<br>
          <a href="<?php echo esc_url($shop['map_url'] ?? 'https://maps.app.goo.gl/659nXgwsXYb3dbYH7'); ?>"
            target="_blank">Google Mapsで表示</a>
        </p>
        <?php endif; ?>
      </div>

      <!-- Nearest Station Information -->
      <div class="store-access__station">
        <p class="store-access__station-text"><?php echo esc_html($shop['nearest_station'] ?? 'N/A'); ?></p>
      </div>
    </div>
  </section>

  <!-- Contact & Booking Section -->
  <?php get_template_part('template-parts/components/contact-booking'); ?>
</main>

<!-- Lightbox Modal -->
<div class="lightbox" id="galleryLightbox">
  <div class="lightbox__overlay"></div>
  <div class="lightbox__content">
    <button class="lightbox__close" aria-label="閉じる">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>
    <img class="lightbox__image" src="" alt="">
  </div>
</div>

<style>
/* Ensure the map iframe is visible and sized correctly */
.store-access__map iframe {
  display: block !important;
  width: 100% !important;
  height: 400px !important;
  max-width: 100%;
  border: 0;
}

/* Container for embedded iframe from database */
.map-iframe-container {
  width: 100%;
  height: 400px;
  overflow: hidden;
}

.map-iframe-container iframe {
  width: 100% !important;
  height: 400px !important;
  border: 0 !important;
  display: block !important;
}

/* Style for no images message */
.store-gallery__no-images {
  text-align: center;
  padding: 40px 20px;
  color: #666;
  font-size: 16px;
  background: #f9f9f9;
  border-radius: 8px;
  margin: 20px 0;
}

.store-gallery__no-images p {
  margin: 0;
}
</style>



<?php
get_template_part('template-parts/components/footer');
get_footer();
?>