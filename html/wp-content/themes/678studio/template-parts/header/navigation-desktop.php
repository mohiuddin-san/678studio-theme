<nav class="navigation-desktop" role="navigation" aria-label="Primary Navigation">
  <ul class="navigation-desktop__menu" id="desktop-primary-menu">
    <li class="navigation-desktop__item">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="navigation-desktop__link">トップ</a>
    </li>
    <li class="navigation-desktop__item">
      <a href="<?php echo esc_url(home_url('/about')); ?>" class="navigation-desktop__link">ロクナナハチ撮影とは？</a>
    </li>
    <li class="navigation-desktop__item">
      <a href="<?php echo esc_url(home_url('/stores')); ?>" class="navigation-desktop__link">写真館検索</a>
    </li>
    <li class="navigation-desktop__item">
      <a href="<?php echo esc_url(home_url('/photo-gallery')); ?>" class="navigation-desktop__link">ギャラリー</a>
    </li>
    <li class="navigation-desktop__item">
      <a href="<?php echo esc_url(home_url('/articles')); ?>" class="navigation-desktop__link">お役立ち情報</a>
    </li>
  </ul>

  <!-- PC専用CTA：ボタンスタイル -->
  <div class="navigation-desktop__cta">
    <a href="<?php echo esc_url(home_url('/studio-reservation')); ?>"
       class="navigation-desktop__cta-button navigation-desktop__cta-button--reservation">
      <span>ご予約相談</span>
    </a>

    <a href="<?php echo esc_url(home_url('/studio-inquiry')); ?>"
       class="navigation-desktop__cta-button navigation-desktop__cta-button--inquiry">
      <span>お問い合わせ</span>
    </a>

    <button class="navigation-desktop__cta-button navigation-desktop__cta-button--publication"
            id="desktop-publication-modal-trigger">
      <span>掲載希望の方へ</span>
    </button>
  </div>
</nav>

<!-- 掲載希望モーダル（PC専用） -->
<div class="publication-modal" id="desktop-publication-modal">
  <div class="publication-modal__content">
    <button class="publication-modal__close" id="desktop-publication-modal-close">&times;</button>
    <h3 class="publication-modal__title">掲載希望の方へ</h3>
    <div class="publication-modal__links">
      <a href="<?php echo esc_url(home_url('/studio-recruitment')); ?>" class="publication-modal__link">
        <span>資料ダウンロード</span>
      </a>
      <a href="<?php echo esc_url(home_url('/corporate-inquiry')); ?>" class="publication-modal__link">
        <span>お問い合わせ</span>
      </a>
    </div>
  </div>
</div>