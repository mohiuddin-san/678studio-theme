<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>

  <header class="header" role="banner">
    <div class="header__container">
      <div class="branding">
        <div class="branding__logo">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="<?php bloginfo('name'); ?>"
            class="branding__image">
        </div>
      </div>
      <?php get_template_part('template-parts/header/navigation'); ?>
    </div>
  </header>