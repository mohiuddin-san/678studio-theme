<?php
/**
 * Studio Search Section - お近くのフォトスタジオを探す
 * Fetches shop data from API with pagination and search
 */

function fetch_studio_shops($search_query = '', $page = 1, $per_page = 6) {
    // functions.phpのキャッシュ機能を使用
    $data = get_cached_studio_data();
    
    if (isset($data['error'])) {
        return ['shops' => [], 'total' => 0, 'error' => $data['error']];
    }
    
    $filtered_shops = $data['shops'];
    if (!empty($search_query)) {
        $filtered_shops = array_filter($data['shops'], function($shop) use ($search_query) {
            return stripos($shop['name'] ?? '', $search_query) !== false || 
                   stripos($shop['nearest_station'] ?? '', $search_query) !== false ||
                   stripos($shop['address'] ?? '', $search_query) !== false;
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

<section class="studio-search-section" id="studio-search-section">
  <div class="studio-search-section__container">

    <!-- ヘッダーエリア -->
    <div class="studio-search-section__header scroll-animate-item" data-delay="0">
      <div class="studio-search-section__label">
        <?php get_template_part('template-parts/components/thoughts-label', null, [
            'text' => 'Search for a photo studio'
        ]); ?>
      </div>
      <h2 class="studio-search-section__title">お近くのフォトスタジオを探す</h2>
      <p class="studio-search-section__subtitle">全国の写真館で678撮影が受けられます</p>
    </div>

    <!-- 検索バー -->
    <div class="studio-search-section__search scroll-animate-item" data-delay="0.2">
      <div class="studio-search-section__search-box">
        <svg class="studio-search-section__search-icon" viewBox="0 0 24 24">
          <path
            d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 0 0 1.48-5.34c-.47-2.78-2.79-5-5.59-5.34a6.505 6.505 0 0 0-7.27 7.27c.34 2.8 2.56 5.12 5.34 5.59a6.5 6.5 0 0 0 5.34-1.48l.27.28v.79l4.25 4.25c.41.41 1.08.41 1.49 0 .41-.41.41-1.08 0-1.49L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
        </svg>
        <input type="text" placeholder="店舗名・地域・最寄り駅をご入力ください" class="studio-search-section__search-input"
          id="studio-search-input" value="<?php echo esc_attr($search_query); ?>">
      </div>
    </div>

    <!-- 検索結果件数表示 -->
    <div class="studio-search-section__result-count scroll-animate-item" data-delay="0.4" id="search-result-count">
      <div class="result-count-container">
        <?php if (!empty($search_query)): ?>
        <div class="result-count-text">
          <span class="search-term">「<?php echo esc_html($search_query); ?>」</span>
          <span class="result-label">の検索結果</span>
        </div>
        <div class="result-count-number">
          <span class="count"><?php echo $shop_data['total']; ?></span>
          <span class="count-unit">件</span>
        </div>
        <?php else: ?>
        <div class="result-count-text">
          <span class="result-label">全国のフォトスタジオ</span>
        </div>
        <div class="result-count-number">
          <span class="count"><?php echo $shop_data['total']; ?></span>
          <span class="count-unit">件</span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- スタジオカード一覧 -->
    <div class="studio-search-section__cards scroll-animate-item" data-delay="0.6">
      <?php if (empty($shops)): ?>
      <p>検索結果が見つかりませんでした。</p>
      <?php else: ?>
      <?php foreach ($shops as $shop): ?>
      <div class="studio-card">
        <div class="studio-card__image">
          <?php
          // メイン画像の表示優先順位: main_image -> image_urls[0] -> デフォルト画像
          $image_src = '';
          if (!empty($shop['main_image'])) {
              // Base64データかURLかを判定
              if (strpos($shop['main_image'], 'data:image') === 0) {
                  $image_src = $shop['main_image']; // Base64データはそのまま使用
              } else {
                  $image_src = esc_url($shop['main_image']); // URLの場合はエスケープ
              }
          } elseif (!empty($shop['image_urls']) && !empty($shop['image_urls'][0])) {
              // ギャラリー画像をフォールバック
              if (strpos($shop['image_urls'][0], 'data:image') === 0) {
                  $image_src = $shop['image_urls'][0];
              } else {
                  $image_src = esc_url($shop['image_urls'][0]);
              }
          } else {
              // デフォルト画像
              $image_src = get_template_directory_uri() . '/assets/images/cardpic-sample.jpg';
          }
          ?>
          <img src="<?php echo $image_src; ?>" alt="スタジオ写真">
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
                  'url' => home_url('/studio-detail/?shop_id=' . $shop['id'])
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
        class="pagination-btn pagination-btn--prev <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
      </a>
      <div class="pagination-numbers">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?studio_page=<?php echo $i; ?><?php echo $search_query ? '&studio_search=' . urlencode($search_query) : ''; ?>"
          class="<?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
      </div>
      <a href="?studio_page=<?php echo min($total_pages, $current_page + 1); ?><?php echo $search_query ? '&studio_search=' . urlencode($search_query) : ''; ?>"
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

<!-- JavaScript for real-time AJAX search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('studio-search-input');
  const cardsContainer = document.querySelector('.studio-search-section__cards');
  const paginationContainer = document.querySelector('.studio-search-section__pagination');
  const resultCountContainer = document.getElementById('search-result-count');
  let debounceTimer;
  let currentPage = 1;

  // AJAX search function
  function performAjaxSearch(query = '', page = 1) {
    // Show loading state
    cardsContainer.style.opacity = '0.6';

    const formData = new FormData();
    formData.append('action', 'studio_search');
    formData.append('search_query', query);
    formData.append('page', page);
    formData.append('nonce', '<?php echo wp_create_nonce('studio_search_nonce'); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update cards
          cardsContainer.innerHTML = data.data.cards_html;

          // Update pagination
          if (paginationContainer) {
            if (data.data.pagination_html.trim()) {
              paginationContainer.innerHTML = data.data.pagination_html;
              paginationContainer.style.display = 'flex';
            } else {
              paginationContainer.style.display = 'none';
            }
          }

          // Update result count
          if (resultCountContainer && data.data.total_shops !== undefined) {
            if (query.trim()) {
              resultCountContainer.innerHTML = `
              <div class="result-count-container">
                <div class="result-count-text">
                  <span class="search-term">「${query}」</span>
                  <span class="result-label">の検索結果</span>
                </div>
                <div class="result-count-number">
                  <span class="count">${data.data.total_shops}</span>
                  <span class="count-unit">件</span>
                </div>
              </div>
            `;
            } else {
              resultCountContainer.innerHTML = `
              <div class="result-count-container">
                <div class="result-count-text">
                  <span class="result-label">全国のフォトスタジオ</span>
                </div>
                <div class="result-count-number">
                  <span class="count">${data.data.total_shops}</span>
                  <span class="count-unit">件</span>
                </div>
              </div>
            `;
            }
          }

          // Update current page
          currentPage = data.data.current_page;

          // Re-attach pagination event listeners
          attachPaginationListeners();

          // Restore opacity
          cardsContainer.style.opacity = '1';

        } else {
          console.error('Search failed:', data.data.message);
          cardsContainer.style.opacity = '1';
        }
      })
      .catch(error => {
        console.error('AJAX error:', error);
        cardsContainer.style.opacity = '1';
      });
  }

  // Attach pagination event listeners
  function attachPaginationListeners() {
    const paginationLinks = document.querySelectorAll('.studio-search-section__pagination a');

    paginationLinks.forEach(link => {
      // Remove any existing listeners to prevent duplicates
      link.removeEventListener('click', handlePaginationClick);
      link.addEventListener('click', handlePaginationClick);
    });
  }

  // Separate pagination click handler
  function handlePaginationClick(e) {
    e.preventDefault();

    // Check if button is disabled
    if (this.classList.contains('disabled') || this.dataset.disabled === 'true') {
      return;
    }

    let page;
    if (this.dataset.page) {
      page = parseInt(this.dataset.page);
    } else {
      // Fallback: extract page from href for static links
      const href = this.getAttribute('href');
      const match = href.match(/studio_page=(\d+)/);
      page = match ? parseInt(match[1]) : 1;
    }

    if (isNaN(page) || page < 1) {
      console.warn('Invalid page number:', page);
      return;
    }

    const query = searchInput.value.trim();
    performAjaxSearch(query, page);
  }

  // Search input events
  searchInput.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const query = this.value.trim();
      currentPage = 1;
      performAjaxSearch(query, 1);
    }, 300); // Reduced debounce time for more responsive feel
  });

  // Enter key for immediate search
  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      clearTimeout(debounceTimer);
      const query = this.value.trim();
      currentPage = 1;
      performAjaxSearch(query, 1);
    }
  });

  // Initial pagination setup
  attachPaginationListeners();

  // Add smooth transition CSS
  cardsContainer.style.transition = 'opacity 0.3s ease';
});
</script>

<style>
.pagination-btn.disabled,
.pagination-btn[data-disabled="true"] {
  opacity: 0.5;
  pointer-events: none;
  cursor: not-allowed;
}

/* Force remove blue outline from search input */
.studio-search-section__search-input,
.studio-search-section__search-input:focus,
.studio-search-section__search-input:active,
.studio-search-section__search-input:focus-visible {
  outline: none !important;
  border: none !important;
  box-shadow: none !important;
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
  appearance: none !important;
}

.studio-search-section__search-box,
.studio-search-section__search-box:focus-within {
  outline: none !important;
}

/* Loading animation */
.studio-search-section__cards {
  transition: opacity 0.3s ease;
}

/* Result count styling */
.studio-search-section__result-count {
  margin: 32px 0 48px;
  display: flex;
  justify-content: center;
}

.result-count-container {
  display: inline-flex;
  align-items: center;
  gap: 16px;
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid #e5e7eb;
  border-radius: 24px;
  padding: 12px 24px;
  backdrop-filter: blur(8px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: all 0.3s ease;
}

.result-count-container:hover {
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  transform: translateY(-1px);
}

.result-count-text {
  display: flex;
  align-items: center;
  gap: 4px;
  font-family: 'Noto Sans JP', sans-serif;
}

.search-term {
  font-weight: 600;
  color: #3f3f3f;
  font-size: 15px;
  letter-spacing: 0.5px;
}

.result-label {
  color: #666666;
  font-size: 14px;
  letter-spacing: 0.3px;
}

.result-count-number {
  display: flex;
  align-items: baseline;
  gap: 2px;
  padding: 4px 12px;
  background: linear-gradient(135deg, #a99f3c 0%, #b8ae4a 100%);
  border-radius: 16px;
  min-width: 60px;
  justify-content: center;
}

.count {
  font-weight: 700;
  color: #ffffff;
  font-size: 18px;
  line-height: 1;
  font-family: 'Helvetica Neue', Arial, sans-serif;
  letter-spacing: 0.5px;
}

.count-unit {
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
  font-size: 12px;
  line-height: 1;
  font-family: 'Noto Sans JP', sans-serif;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .studio-search-section__result-count {
    margin: 24px 0 32px;
  }

  .result-count-container {
    gap: 12px;
    padding: 10px 20px;
    border-radius: 20px;
  }

  .search-term {
    font-size: 14px;
  }

  .result-label {
    font-size: 13px;
  }

  .result-count-number {
    padding: 3px 10px;
    border-radius: 14px;
    min-width: 50px;
  }

  .count {
    font-size: 16px;
  }

  .count-unit {
    font-size: 11px;
  }
}
</style>