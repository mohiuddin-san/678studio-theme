<nav class="navigation" role="navigation" aria-label="Primary Navigation">
  <ul class="navigation__menu" id="primary-menu">
    <li class="navigation__item"><a href="<?php echo esc_url(home_url('/')); ?>" class="navigation__link">トップ</a></li>
    <li class="navigation__item"><a href="<?php echo esc_url(home_url('/about')); ?>"
        class="navigation__link">ロクナナハチ撮影とは？</a></li>
    <li class="navigation__item"><a href="<?php echo esc_url(home_url('/stores')); ?>"
        class="navigation__link">写真館検索</a>
    </li>
    <li class="navigation__item"><a href="<?php echo esc_url(home_url('/photo-gallery')); ?>"
        class="navigation__link">ギャラリー</a></li>
    <li class="navigation__item"><a href="<?php echo esc_url(home_url('/articles')); ?>"
        class="navigation__link">お役立ち情報</a></li>
  </ul>

  <!-- CTAボタン（Pigment風） -->
  <div class="navigation__cta">
    <a href="<?php echo esc_url(home_url('/studio-reservation')); ?>"
       class="navigation__cta-button navigation__cta-button--reservation">
      <span>ご予約相談</span>
    </a>
    <a href="<?php echo esc_url(home_url('/studio-inquiry')); ?>"
       class="navigation__cta-button navigation__cta-button--inquiry">
      <span>お問い合わせ</span>
    </a>
    <button class="navigation__cta-button navigation__cta-button--publication" id="publication-modal-trigger">
      <span>掲載希望の方へ</span>
    </button>
  </div>

  <!-- 掲載希望モーダル -->
  <div class="publication-modal" id="publication-modal">
    <div class="publication-modal__content">
      <button class="publication-modal__close" id="publication-modal-close">&times;</button>
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
</nav>