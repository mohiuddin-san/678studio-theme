<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no, address=no, email=no">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
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