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
    <li class="navigation__item"><a href="<?php echo get_post_type_archive_link('seo_articles'); ?>"
        class="navigation__link">お役立ち情報</a></li>
  </ul>

  <!-- CTAエリア：PC用はボタン、SP用はテキストリンク -->
  <div class="navigation__cta">
    <!-- PC用ボタン（SP時は非表示） -->
    <div class="navigation__cta-buttons">
      <a href="<?php echo esc_url(home_url('/studio-reservation')); ?>"
         class="navigation__cta-button navigation__cta-button--reservation">
        <span>ご予約相談</span>
      </a>
      <a href="<?php echo esc_url(home_url('/studio-inquiry')); ?>"
         class="navigation__cta-button navigation__cta-button--inquiry">
        <span>お問い合わせ</span>
      </a>
      <div class="navigation__dropdown">
        <button class="navigation__cta-button navigation__cta-button--publication"
                id="publication-toggle"
                aria-expanded="false"
                aria-haspopup="true"
                aria-controls="publication-dropdown">
          <span>掲載希望の写真館へ</span>
        </button>

        <!-- PCドロップダウンメニュー -->
        <div class="navigation__dropdown-menu"
             id="publication-dropdown"
             role="menu"
             aria-labelledby="publication-toggle">
          <div class="navigation__dropdown-content">
            <a href="<?php echo esc_url(home_url('/studio-recruitment')); ?>"
               class="navigation__dropdown-link"
               role="menuitem">
              <span>資料ダウンロード</span>
            </a>
            <a href="<?php echo esc_url(home_url('/corporate-inquiry')); ?>"
               class="navigation__dropdown-link"
               role="menuitem">
              <span>お問い合わせ</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- SP用テキストリンク（PC時は非表示） -->
    <div class="navigation__mobile-links">
      <a href="<?php echo esc_url(home_url('/studio-reservation')); ?>" class="navigation__mobile-link">
        ご予約相談
      </a>
      <a href="<?php echo esc_url(home_url('/studio-inquiry')); ?>" class="navigation__mobile-link">
        お問い合わせ
      </a>
      <div class="navigation__mobile-expandable">
        <button class="navigation__mobile-link navigation__mobile-toggle" id="mobile-publication-toggle">
          掲載希望の写真館へ
          <span class="navigation__mobile-plus">+</span>
        </button>
        <div class="navigation__mobile-submenu" id="mobile-publication-submenu">
          <a href="<?php echo esc_url(home_url('/studio-recruitment')); ?>" class="navigation__mobile-sublink">
            資料ダウンロード
          </a>
          <a href="<?php echo esc_url(home_url('/corporate-inquiry')); ?>" class="navigation__mobile-sublink">
            お問い合わせ
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>