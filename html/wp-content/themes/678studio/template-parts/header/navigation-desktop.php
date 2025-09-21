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
      <svg class="navigation-desktop__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008ZM15 12.75h.008v.008H15v-.008Zm0 2.25h.008v.008H15V15Zm0 2.25h.008v.008H15v-.008Zm2.25-4.5h.008v.008H17.25v-.008Zm0 2.25h.008v.008H17.25V15Zm0 2.25h.008v.008H17.25v-.008Z" />
      </svg>
    </a>

    <a href="<?php echo esc_url(home_url('/studio-inquiry')); ?>"
       class="navigation-desktop__cta-button navigation-desktop__cta-button--inquiry">
      <span>お問い合わせ</span>
      <svg class="navigation-desktop__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
      </svg>
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