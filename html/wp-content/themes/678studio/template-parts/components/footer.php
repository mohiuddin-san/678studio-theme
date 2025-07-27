<?php
/**
 * Footer Component - サイトフッター
 */
?>

<footer class="footer">
  <!-- Top Navigation Section -->
  <div class="footer__nav-section">
    <div class="footer__container">
      <!-- Logo -->
      <div class="footer__logo">
        <a href="<?php echo esc_url(home_url('/')); ?>">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="ロクナナハチ(678)">
        </a>
      </div>

      <!-- Navigation Links -->
      <nav class="footer__nav">
        <div class="footer__nav-row footer__nav-row--primary">
          <a href="/" class="footer__nav-link">トップページ</a>
          <a href="/about" class="footer__nav-link">678撮影について</a>
          <a href="/gallery" class="footer__nav-link">ギャラリー</a>
          <a href="/stores" class="footer__nav-link">写真館検索</a>
          <a href="/studio-reservation" class="footer__nav-link">ご予約</a>
          <a href="/studio-inquery" class="footer__nav-link">お問合せ</a>
        </div>
        <div class="footer__nav-row footer__nav-row--secondary">
          <a href="/privacy" class="footer__nav-link">プライバシーポリシー</a>
          <a href="https://egao-salon.jp/" class="footer__nav-link" target="_blank" rel="noopener noreferrer">本サービス運営</a>
        </div>
      </nav>
    </div>
  </div>

  <!-- Copyright Section -->
  <div class="footer__copyright-section">
    <div class="footer__container">
      <p class="footer__copyright">© 2025 ロクナナハチ(678) ALL RIGHTS RESERVED.</p>
    </div>
  </div>
</footer>