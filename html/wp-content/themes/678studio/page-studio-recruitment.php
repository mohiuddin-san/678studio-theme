<?php
/**
 * Template Name: 掲載希望の写真館へ
 * Description: 写真館掲載希望者向けの資料ダウンロードと申込みページ
 */

// SEO情報は統一システム（StudioSEOManager）で自動処理されます

get_header();
?>

<main class="main-content studio-recruitment">


  <!-- Studio Recruitment Section -->
  <?php get_template_part('template-parts/sections/studio-recruitment'); ?>

</main>

<?php
get_template_part('template-parts/components/footer');
get_footer();
?>