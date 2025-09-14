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
        <div class="footer__nav-row footer__nav-row--primary">
          <a href="/" class="footer__nav-link">トップページ</a>
          <a href="/about" class="footer__nav-link">678撮影について</a>
          <a href="/photo-gallery" class="footer__nav-link">ギャラリー</a>
          <a href="/stores" class="footer__nav-link">写真館検索</a>
          <a href="/studio-reservation" class="footer__nav-link">ご予約</a>
          <a href="/studio-inquiry" class="footer__nav-link">お問合せ</a>
          <a href="/privacy" class="footer__nav-link">プライバシーポリシー</a>
          <a href="https://san-creation.com/" class="footer__nav-link" target="_blank" rel="noopener noreferrer">運営会社</a>
        </div>
      </nav>

      <!-- Divider Line -->
      <div class="footer__divider"></div>

      <!-- Bottom Navigation -->
      <nav class="footer__bottom-nav">
        <span class="footer__bottom-text footer__bottom-text--bold">掲載希望の写真館へ</span>
        <a href="/download" class="footer__bottom-link">資料ダウンロード</a>
        <a href="/contact" class="footer__bottom-link">お申し込み・お問い合わせ</a>
      </nav>
    </div>
  </div>

  <!-- Copyright Section (White Background) -->
  <div class="footer__copyright-section">
    <div class="footer__container">
      <p class="footer__copyright">© 2025 ハチナナロク ALL RIGHTS RESERVED.</p>
    </div>
  </div>
</footer>