<?php
/**
 * Template Name: 店舗詳細
 * Description: 写真館の店舗詳細ページテンプレート
 */

get_header();

// Function to fetch shop data by ID using ACF system
function fetch_studio_shop_by_id($shop_id) {
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

  <!-- Studio Info Header -->
  <?php get_template_part('template-parts/sections/studio/studio-info-header', null, [
    'shop' => $shop
  ]); ?>

  <!-- Studio Hero Slider Section -->
  <?php
  // Get gallery images for the slider
  $gallery_images = array();
  if (!empty($shop['main_gallery_images']) && is_array($shop['main_gallery_images'])) {
    $gallery_images = $shop['main_gallery_images'];
  } elseif (!empty($shop['gallery_images']) && is_array($shop['gallery_images'])) {
    $gallery_images = $shop['gallery_images'];
  } elseif (!empty($shop['image_urls']) && is_array($shop['image_urls'])) {
    // Fallback to image_urls
    $gallery_images = $shop['image_urls'];
  } elseif (!empty($shop['images']) && is_array($shop['images'])) {
    // Final fallback to general images
    $gallery_images = $shop['images'];
  }

  // Only show slider if we have images
  if (!empty($gallery_images)) {
    get_template_part('template-parts/sections/studio/hero-slider', null, [
      'gallery_images' => $gallery_images,
      'shop_name' => $shop['name'] ?? ''
    ]);
  }
  ?>

  <!-- Store Basic Information Section -->
  <?php get_template_part('template-parts/sections/studio/store-basic-info', null, [
    'shop' => $shop
  ]); ?>

  <!-- Store Plan Section (New Design) -->
  <?php get_template_part('template-parts/sections/studio/store-plan-section', null, [
    'shop' => $shop
  ]); ?>

  <!-- Store Access Section -->
  <?php get_template_part('template-parts/sections/studio/store-access-section', null, [
    'shop' => $shop
  ]); ?>






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
/* Access details styles */
.store-access__details {
  margin-bottom: 20px;
}

.store-access__details-text {
  margin: 0;
  line-height: 1.6;
}

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

/* Certified badge styles */
.certified-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #3A89FF, #5BA0FF);
  color: white;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 600;
  margin-left: 8px;
}



.certified-badge svg {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

.certified-text {
  white-space: nowrap;
}

/* Store hero specific styles for badge in dark background */
.store-hero__category {
  color: #333 !important;
}

.store-hero__category .certified-badge {
  background: linear-gradient(135deg, #3A89FF, #5BA0FF);
  color: white;
}


/* Photography plans table styles */
.plans-table-wrapper {
  margin-top: 30px;
  overflow-x: auto;
}

.plans-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  min-width: 600px;
}

.plans-table__header {
  background: #f5f5f5;
  color: #333;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  white-space: nowrap;
}

.plans-table__row:nth-child(even) {
  background: #f9f9f9;
}

.plans-table__cell {
  padding: 12px;
  vertical-align: top;
  white-space: nowrap;
}

.plans-table__name {
  font-weight: 600;
  color: #333;
  background: #f5f5f5;
}

.plans-table__price {
  color: #333;
  font-weight: normal;
}

.plans-table__duration {
  color: #333;
}

.plans-table__description {
  line-height: 1.5;
  color: #333;
  white-space: normal;
  max-width: 300px;
}

.plans-table__empty {
  color: #999;
  font-style: italic;
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

/* Mobile responsiveness */
@media (max-width: 768px) {
  .certified-badge {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 12px;
    gap: 4px;
  }

  .certified-badge svg {
    width: 12px;
    height: 12px;
  }

  .plans-table {
    font-size: 13px;
  }

  .plans-table__header {
    padding: 8px;
    font-size: 13px;
  }

  .plans-table__cell {
    padding: 8px;
  }
}
</style>



<?php get_footer(); ?>