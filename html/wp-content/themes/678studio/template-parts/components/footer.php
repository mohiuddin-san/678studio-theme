<?php
/**
 * Footer Component - サイトフッター
 */
?>

<footer class="footer">
  <!-- Main Footer Content -->
  <div class="footer__main">
    <div class="footer__container">
      <!-- Logo Section -->
      <div class="footer__logo-section">
        <div class="footer__logo">
          <a href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="ロクナナハチ(678)">
          </a>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="footer__nav">
        <div class="footer__nav-grid">
          <!-- PC Navigation -->
          <div class="footer__nav-desktop pc-only">
            <!-- Column 1: Main Services -->
            <div class="footer__nav-column">
              <a href="/" class="footer__nav-link">トップページ</a>
              <a href="/about" class="footer__nav-link">678撮影について</a>
              <a href="/photo-gallery" class="footer__nav-link">ギャラリー</a>
            </div>

            <!-- Column 2: User Services -->
            <div class="footer__nav-column">
              <a href="/stores" class="footer__nav-link">写真館検索</a>
              <a href="/studio-reservation" class="footer__nav-link">ご予約相談</a>
              <a href="/studio-inquiry" class="footer__nav-link">お問合せ</a>
              <a href="<?php echo get_post_type_archive_link('seo_articles'); ?>" class="footer__nav-link">お役立ち情報</a>
            </div>

            <!-- Column 3: Studio Services -->
            <div class="footer__nav-column">
              <span class="footer__nav-label">
                <svg class="footer__nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                掲載希望の方へ
              </span>
              <a href="/studio-recruitment" class="footer__nav-link">資料ダウンロード</a>
              <a href="/corporate-inquiry" class="footer__nav-link">お問い合わせ</a>
            </div>

            <!-- Column 4: Company Info -->
            <div class="footer__nav-column footer__nav-column--company">
              <span class="footer__nav-label">
                <svg class="footer__nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                企業情報
              </span>
              <a href="/privacy" class="footer__nav-link">プライバシーポリシー</a>
              <a href="https://san-creation.com/" class="footer__nav-link" target="_blank" rel="noopener noreferrer">運営会社</a>
            </div>
          </div>

          <!-- Mobile Navigation -->
          <div class="footer__nav-mobile sp-only">
            <!-- Left Column -->
            <div class="footer__nav-column">
              <a href="/" class="footer__nav-link">トップページ</a>
              <a href="/about" class="footer__nav-link">678撮影について</a>
              <a href="/photo-gallery" class="footer__nav-link">ギャラリー</a>
              <a href="/stores" class="footer__nav-link">写真館検索</a>
              <a href="/studio-reservation" class="footer__nav-link">ご予約相談</a>
              <a href="/studio-inquiry" class="footer__nav-link">お問合せ</a>
              <a href="<?php echo get_post_type_archive_link('seo_articles'); ?>" class="footer__nav-link">お役立ち情報</a>
            </div>

            <!-- Right Column -->
            <div class="footer__nav-column">
              <span class="footer__nav-label">
                <svg class="footer__nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                掲載希望の方へ
              </span>
              <a href="/studio-recruitment" class="footer__nav-link">資料ダウンロード</a>
              <a href="/corporate-inquiry" class="footer__nav-link">お問い合わせ</a>
              <span class="footer__nav-label">
                <svg class="footer__nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                企業情報
              </span>
              <a href="/privacy" class="footer__nav-link">プライバシーポリシー</a>
              <a href="https://san-creation.com/" class="footer__nav-link" target="_blank" rel="noopener noreferrer">運営会社</a>
            </div>
          </div>
        </div>
      </nav>
    </div>
  </div>

  <!-- Copyright Section (White Background) -->
  <div class="footer__copyright-section">
    <div class="footer__container">
      <p class="footer__copyright">© 2025 ロクナナハチ ALL RIGHTS RESERVED.</p>
    </div>
  </div>
</footer>