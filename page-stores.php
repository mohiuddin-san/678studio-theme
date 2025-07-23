<?php
/*
Template Name: Stores List
*/

get_header();
?>

<main class="main-content store-archive">

  <!-- Breadcrumb -->
  <?php get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
      ['text' => 'TOP', 'url' => home_url()],
      ['text' => '店舗一覧', 'url' => '']
    ]
  ]); ?>

  <!-- Store Search Section -->
  <?php get_template_part('template-parts/sections/home/studio-search'); ?>

</main>

<?php
get_template_part('template-parts/components/footer');
get_footer();
?>