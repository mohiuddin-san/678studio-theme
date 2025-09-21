<nav class="navigation-mobile" role="navigation" aria-label="Mobile Navigation">
  <!-- 全体を統一したメニューとして構成 -->
  <div class="navigation-mobile__menu-container">
    <ul class="navigation-mobile__menu" id="mobile-primary-menu">
      <li class="navigation-mobile__item">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="navigation-mobile__link">トップ</a>
      </li>
      <li class="navigation-mobile__item">
        <a href="<?php echo esc_url(home_url('/about')); ?>" class="navigation-mobile__link">ロクナナハチ撮影とは？</a>
      </li>
      <li class="navigation-mobile__item">
        <a href="<?php echo esc_url(home_url('/stores')); ?>" class="navigation-mobile__link">写真館検索</a>
      </li>
      <li class="navigation-mobile__item">
        <a href="<?php echo esc_url(home_url('/photo-gallery')); ?>" class="navigation-mobile__link">ギャラリー</a>
      </li>
      <li class="navigation-mobile__item">
        <a href="<?php echo esc_url(home_url('/articles')); ?>" class="navigation-mobile__link">お役立ち情報</a>
      </li>
    </ul>

    <!-- セパレーター（お役立ち情報とご予約相談の間） -->
    <div class="navigation-mobile__separator"></div>

    <!-- CTA項目を含む統一メニュー -->
    <div class="navigation-mobile__cta">
      <a href="<?php echo esc_url(home_url('/studio-reservation')); ?>" class="navigation-mobile__link">
        ご予約相談
      </a>

      <a href="<?php echo esc_url(home_url('/studio-inquiry')); ?>" class="navigation-mobile__link">
        お問い合わせ
      </a>

      <!-- SP専用展開式メニュー -->
      <div class="navigation-mobile__expandable">
        <button class="navigation-mobile__link navigation-mobile__toggle"
                id="mobile-publication-toggle"
                aria-expanded="false"
                aria-controls="mobile-publication-submenu">
          掲載希望の方へ
          <span class="navigation-mobile__plus">+</span>
        </button>

        <div class="navigation-mobile__submenu"
             id="mobile-publication-submenu"
             aria-hidden="true">
          <a href="<?php echo esc_url(home_url('/studio-recruitment')); ?>"
             class="navigation-mobile__sublink">
            資料ダウンロード
          </a>
          <a href="<?php echo esc_url(home_url('/corporate-inquiry')); ?>"
             class="navigation-mobile__sublink">
            お問い合わせ
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>