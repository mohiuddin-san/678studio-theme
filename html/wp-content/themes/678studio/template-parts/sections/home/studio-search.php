<?php
/**
 * Studio Search Section - お近くのフォトスタジオを探す
 * Fetches shop data from API with pagination and search
 */

function fetch_studio_shops($search_query = '', $page = 1, $per_page = 6) {
    $api_url = 'https://678photo.com/api/get_all_studio_shop.php';

    $response = wp_remote_get($api_url, [
        'timeout' => 15, 
        'sslverify' => false 
    ]);

    if (is_wp_error($response)) {
        return ['shops' => [], 'total' => 0, 'error' => $response->get_error_message()];
    }
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['shops' => [], 'total' => 0, 'error' => 'Invalid JSON response'];
    }
    if (!isset($data['shops']) || !is_array($data['shops'])) {
        return ['shops' => [], 'total' => 0, 'error' => 'No shops found in API response'];
    }
    $filtered_shops = $data['shops'];
    if (!empty($search_query)) {
        $filtered_shops = array_filter($data['shops'], function($shop) use ($search_query) {
            return stripos($shop['name'] ?? '', $search_query) !== false || 
                   stripos($shop['nearest_station'] ?? '', $search_query) !== false;
        });
    }
    $total_shops = count($filtered_shops);
    $total_pages = max(1, ceil($total_shops / $per_page)); // Ensure at least 1 page
    $page = min($page, $total_pages); // Prevent invalid page numbers
    $offset = ($page - 1) * $per_page;
    $shops = array_slice($filtered_shops, $offset, $per_page);

    return [
        'shops' => $shops,
        'total' => $total_shops,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'error' => null
    ];
}

$page = isset($_GET['studio_page']) ? max(1, intval($_GET['studio_page'])) : 1;
$search_query = isset($_GET['studio_search']) ? sanitize_text_field($_GET['studio_search']) : '';

$shop_data = fetch_studio_shops($search_query, $page);
$shops = $shop_data['shops'];
$total_pages = $shop_data['total_pages'];
$current_page = $shop_data['current_page'];

// // Debugging output for admins (on-screen, not logged)
// if (current_user_can('administrator')) {
//     echo '<div style="background: #fff; padding: 10px; border: 1px solid #ccc; margin-bottom: 20px;">';
//     echo '<h3>Debug Info</h3>';
//     echo '<p>API URL: https://678photo.com/api/get_all_studio_shop.php</p>';
//     echo '<p>Search Query: ' . esc_html($search_query) . '</p>';
//     echo '<p>Current Page: ' . $current_page . '</p>';
//     echo '<p>Total Shops: ' . $shop_data['total'] . '</p>';
//     echo '<p>Total Pages: ' . $total_pages . '</p>';
//     echo '<p>Shops on Current Page: ' . count($shops) . '</p>';
//     if ($shop_data['error']) {
//         echo '<p style="color: red;">Error: ' . esc_html($shop_data['error']) . '</p>';
//     }
//     echo '<p>Shops Data: <pre>' . esc_html(print_r($shops, true)) . '</pre></p>';
//     echo '</div>';
// }
?>

<section class="studio-search-section">
  <div class="studio-search-section__container">

    <!-- ヘッダーエリア -->
    <div class="studio-search-section__header">
      <div class="studio-search-section__label">
        <?php get_template_part('template-parts/components/thoughts-label', null, [
            'text' => 'Search for a photo studio'
        ]); ?>
      </div>
      <h2 class="studio-search-section__title">お近くのフォトスタジオを探す</h2>
      <p class="studio-search-section__subtitle">全国の写真館で678撮影が受けられます</p>
    </div>

    <!-- 検索バー -->
    <div class="studio-search-section__search">
      <div class="studio-search-section__search-box">
        <svg class="studio-search-section__search-icon" viewBox="0 0 24 24">
          <path
            d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 0 0 1.48-5.34c-.47-2.78-2.79-5-5.59-5.34a6.505 6.505 0 0 0-7.27 7.27c.34 2.8 2.56 5.12 5.34 5.59a6.5 6.5 0 0 0 5.34-1.48l.27.28v.79l4.25 4.25c.41.41 1.08.41 1.49 0 .41-.41.41-1.08 0-1.49L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
        </svg>
        <input type="text" placeholder="「東京」「埼玉県」「横浜市」「大阪」" class="studio-search-section__search-input"
          id="studio-search-input" value="<?php echo esc_attr($search_query); ?>">
      </div>
    </div>

    <!-- スタジオカード一覧 -->
    <div class="studio-search-section__cards">
      <?php if (empty($shops)): ?>
      <p>検索結果が見つかりませんでした。</p>
      <?php else: ?>
      <?php foreach ($shops as $shop): ?>
      <div class="studio-card">
        <div class="studio-card__image">
          <img
            src="<?php echo !empty($shop['image_urls']) ? esc_url($shop['image_urls'][0]) : get_template_directory_uri() . '/assets/images/cardpic-sample.jpg'; ?>"
            alt="スタジオ写真">
          <div class="studio-card__location"><?php echo esc_html($shop['nearest_station'] ?? 'N/A'); ?></div>
        </div>
        <div class="studio-card__content">
          <h3 class="studio-card__name"><?php echo esc_html($shop['name'] ?? 'Unknown'); ?></h3>
          <div class="studio-card__details">
            <p class="studio-card__address"><?php echo esc_html($shop['address'] ?? 'N/A'); ?></p>
            <div class="studio-card__hours">
              <div class="studio-card__hour-item">営業時間：<?php echo esc_html($shop['business_hours'] ?? 'N/A'); ?></div>
              <div class="studio-card__hour-item">定休日：<?php echo esc_html($shop['holidays'] ?? 'N/A'); ?></div>
            </div>
          </div>
          <?php get_template_part('template-parts/components/camera-button', null, [
                  'text' => '詳しく見る',
                  'bg_color' => 'detail-card',
                  'icon' => 'none',
                  'class' => 'studio-card__contact-btn',
                  'url' => home_url('/shop-detail/?shop_id=' . $shop['id'])
              ]); ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- ページネーション -->
    <?php if ($total_pages > 1): ?>
    <div class="studio-search-section__pagination">
      <a href="?studio_page=<?php echo max(1, $current_page - 1); ?><?php echo $search_query ? '&studio_search=' . urlencode($search_query) : ''; ?>"
        class="pagination-btn pagination-btn--prev <?php echo $current_page == 1 ? 'disabled' : ''; ?>">◀</a>
      <div class="pagination-numbers">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?studio_page=<?php echo $i; ?><?php echo $search_query ? '&studio_search=' . urlencode($search_query) : ''; ?>"
          class="<?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
      </div>
      <a href="?studio_page=<?php echo min($total_pages, $current_page + 1); ?><?php echo $search_query ? '&studio_search=' . urlencode($search_query) : ''; ?>"
        class="pagination-btn pagination-btn--next <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">▶</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- JavaScript for dynamic search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('studio-search-input');
  let debounceTimer;

  searchInput.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const query = searchInput.value.trim();
      const url = new URL(window.location);
      if (query) {
        url.searchParams.set('studio_search', query);
        url.searchParams.set('studio_page', 1);
      } else {
        url.searchParams.delete('studio_search');
        url.searchParams.set('studio_page', 1);
      }
      console.log('Redirecting to: ' + url.toString());
      window.location.href = url.toString();
    }, 500);
  });
});
</script>

<style>
.pagination-btn.disabled {
  opacity: 0.5;
  pointer-events: none;
}
</style>