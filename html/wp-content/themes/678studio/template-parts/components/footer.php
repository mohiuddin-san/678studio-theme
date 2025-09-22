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

          <!-- Column 3: Studio Services & Company -->
          <div class="footer__nav-column">
            <span class="footer__nav-label">掲載希望の写真館へ</span>
            <a href="/studio-recruitment" class="footer__nav-link footer__nav-link--indent">資料ダウンロード</a>
            <a href="/corporate-inquiry" class="footer__nav-link footer__nav-link--indent">お問い合わせ</a>
            <a href="/privacy" class="footer__nav-link">プライバシーポリシー</a>
            <a href="https://san-creation.com/" class="footer__nav-link" target="_blank" rel="noopener noreferrer">運営会社</a>
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