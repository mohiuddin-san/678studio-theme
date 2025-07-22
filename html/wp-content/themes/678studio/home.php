<?php get_header(); ?>

<main class="main-content">
  <?php get_template_part('template-parts/sections/home/hero'); ?>
  <?php get_template_part('template-parts/sections/home/user-count'); ?>
  <?php get_template_part('template-parts/sections/home/media-slider'); ?>
  <?php get_template_part('template-parts/sections/home/thoughts-layout'); ?>
  <?php get_template_part('template-parts/sections/home/about'); ?>
  <?php get_template_part('template-parts/sections/home/recommend'); ?>
  <?php get_template_part('template-parts/sections/home/studio-search'); ?>
  <?php get_template_part('template-parts/sections/home/gallery'); ?>
  <?php get_template_part('template-parts/sections/home/plan'); ?>
</main>

<?php get_template_part('template-parts/components/footer'); ?>

<?php get_footer(); ?>