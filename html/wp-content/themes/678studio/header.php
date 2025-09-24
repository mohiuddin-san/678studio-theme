<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <!-- 320px未満の画面では320pxの表示を縮小して表示 -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script>
    (function() {
      // 実際の画面幅を取得
      var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

      // 320px未満の場合、viewportを320pxに固定（自動縮小される）
      if (screenWidth < 320) {
        var viewport = document.querySelector('meta[name="viewport"]');
        viewport.setAttribute('content', 'width=320');
      }
    })();
  </script>
  <meta name="format-detection" content="telephone=no, address=no, email=no">

  <!-- Google Consent Mode -->
  <script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}

  // デフォルトで同意を拒否状態に設定
  gtag('consent', 'default', {
    'analytics_storage': 'denied',
    'ad_storage': 'denied',
    'ad_user_data': 'denied',
    'ad_personalization': 'denied'
  });
  </script>

  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-TSCBDTQ8');</script>
  <!-- End Google Tag Manager -->

  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TSCBDTQ8"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <!-- Cookie Consent Banner -->
  <div id="cookie-consent-banner" class="cookie-consent-banner" style="display: none;">
    <div class="cookie-consent-content">
      <div class="cookie-consent-text">
        <h3>Cookieの利用について</h3>
        <p>当サイトでは、サービス向上のためにGoogle AnalyticsでCookieを使用しています。詳細は<a href="/privacy-policy" target="_blank">プライバシーポリシー</a>をご確認ください。</p>
      </div>
      <div class="cookie-consent-buttons">
        <button id="cookie-accept-all" class="cookie-btn cookie-btn-accept">すべて許可</button>
        <button id="cookie-accept-necessary" class="cookie-btn cookie-btn-necessary">必要なもののみ</button>
        <button id="cookie-settings" class="cookie-btn cookie-btn-settings">設定</button>
      </div>
    </div>
  </div>

  <!-- Cookie Settings Modal -->
  <div id="cookie-settings-modal" class="cookie-modal" style="display: none;">
    <div class="cookie-modal-content">
      <div class="cookie-modal-header">
        <h3>Cookie設定</h3>
        <button id="cookie-modal-close" class="cookie-modal-close">&times;</button>
      </div>
      <div class="cookie-modal-body">
        <div class="cookie-category">
          <div class="cookie-category-header">
            <label>
              <input type="checkbox" id="necessary-cookies" checked disabled>
              <strong>必須Cookie</strong>
            </label>
          </div>
          <p>サイトの基本機能に必要なCookieです。無効にすることはできません。</p>
        </div>

        <div class="cookie-category">
          <div class="cookie-category-header">
            <label>
              <input type="checkbox" id="analytics-cookies">
              <strong>分析Cookie</strong>
            </label>
          </div>
          <p>Google Analyticsによるサイト利用状況の分析に使用されます。</p>
        </div>
      </div>
      <div class="cookie-modal-footer">
        <button id="cookie-save-settings" class="cookie-btn cookie-btn-accept">設定を保存</button>
        <button id="cookie-modal-cancel" class="cookie-btn cookie-btn-necessary">キャンセル</button>
      </div>
    </div>
  </div>

  <?php wp_body_open(); ?>

  <header class="header" role="banner">
    <div class="header__container">
      <div class="branding">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="branding__logo">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="<?php bloginfo('name'); ?>"
            class="branding__image">
        </a>
      </div>

      <!-- デスクトップナビゲーション -->
      <div class="header__navigation-desktop">
        <?php get_template_part('template-parts/header/navigation-desktop'); ?>
      </div>

      <!-- モバイルハンバーガーボタン -->
      <button class="header__hamburger" aria-label="メニューを開く" aria-expanded="false">
        <span class="header__hamburger-line"></span>
        <span class="header__hamburger-line"></span>
      </button>

      <!-- モバイルナビゲーション -->
      <div class="header__navigation-mobile">
        <?php get_template_part('template-parts/header/navigation-mobile'); ?>
      </div>
    </div>
  </header>