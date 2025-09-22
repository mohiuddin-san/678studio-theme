<nav class="navigation-mobile" role="navigation" aria-label="Mobile Navigation">
  <!-- 全体を統一したメニューとして構成 -->
  <div class="navigation-mobile__menu-container">
    <ul class="navigation-mobile__menu" id="mobile-primary-menu">
      <li class="navigation-mobile__item">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="navigation-mobile__link">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-home-icon.svg" alt="" class="navigation-mobile__icon">
          <span class="navigation-mobile__text">トップ</span>
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-arrow-icon.svg" alt="" class="navigation-mobile__arrow">
        </a>
      </li>
      <li class="navigation-mobile__item">
        <a href="<?php echo esc_url(home_url('/about')); ?>" class="navigation-mobile__link">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-about-icon.svg" alt="" class="navigation-mobile__icon">
          <span class="navigation-mobile__text">ロクナナハチ撮影とは？</span>
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-arrow-icon.svg" alt="" class="navigation-mobile__arrow">
        </a>
      </li>
      <li class="navigation-mobile__item">
        <a href="<?php echo esc_url(home_url('/stores')); ?>" class="navigation-mobile__link">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-search-icon.svg" alt="" class="navigation-mobile__icon">
          <span class="navigation-mobile__text">写真館検索</span>
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-arrow-icon.svg" alt="" class="navigation-mobile__arrow">
        </a>
      </li>
      <li class="navigation-mobile__item">
        <a href="<?php echo esc_url(home_url('/photo-gallery')); ?>" class="navigation-mobile__link">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-gallery-icon.svg" alt="" class="navigation-mobile__icon">
          <span class="navigation-mobile__text">ギャラリー</span>
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-arrow-icon.svg" alt="" class="navigation-mobile__arrow">
        </a>
      </li>
      <li class="navigation-mobile__item">
        <a href="<?php echo get_post_type_archive_link('seo_articles'); ?>" class="navigation-mobile__link">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-blog-icon.svg" alt="" class="navigation-mobile__icon">
          <span class="navigation-mobile__text">お役立ち情報</span>
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-arrow-icon.svg" alt="" class="navigation-mobile__arrow">
        </a>
      </li>
    </ul>

    <!-- CTA項目を含む統一メニュー -->
    <div class="navigation-mobile__cta">
      <a href="<?php echo esc_url(home_url('/studio-reservation')); ?>" class="navigation-mobile__link">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-reservation-icon.svg" alt="" class="navigation-mobile__icon">
        <span class="navigation-mobile__text">ご予約相談</span>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-arrow-icon.svg" alt="" class="navigation-mobile__arrow">
      </a>

      <a href="<?php echo esc_url(home_url('/studio-inquiry')); ?>" class="navigation-mobile__link">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-inquiry-icon.svg" alt="" class="navigation-mobile__icon">
        <span class="navigation-mobile__text">お問い合わせ</span>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-arrow-icon.svg" alt="" class="navigation-mobile__arrow">
      </a>

      <!-- 掲載希望の方へセクション -->
      <div class="navigation-mobile__publication">
        <h3 class="navigation-mobile__publication-title">掲載希望の方へ</h3>
        <div class="navigation-mobile__publication-buttons">
          <a href="<?php echo esc_url(home_url('/studio-recruitment')); ?>" class="navigation-mobile__publication-button navigation-mobile__publication-button--download">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-download-icon.svg" alt="" class="navigation-mobile__publication-icon">
            <span>資料ダウンロード</span>
          </a>
          <a href="<?php echo esc_url(home_url('/corporate-inquiry')); ?>" class="navigation-mobile__publication-button navigation-mobile__publication-button--inquiry">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/header-corp-inquiry-icon.svg" alt="" class="navigation-mobile__publication-icon">
            <span>お問い合わせ</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>