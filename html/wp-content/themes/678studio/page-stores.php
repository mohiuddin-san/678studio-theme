<?php
/**
 * Template Name: 店舗一覧
 * Description: 写真館の店舗一覧ページ
 */

// SEO情報は統一システム（StudioSEOManager）で自動処理されます

// Add structured data for local business listing
add_action('wp_footer', function() {
    ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "678写真館 店舗一覧",
  "description": "678写真館の提携店舗一覧",
  "url": "<?php echo esc_url(home_url('/stores/')); ?>",
  "numberOfItems": 30
}
</script>
<?php
}, 99);

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

  <!-- Store Search Header Section (New) -->
  <?php get_template_part('template-parts/sections/stores/store-search-header'); ?>

  <!-- Certified Stores List Section (PC) -->
  <?php get_template_part('template-parts/sections/stores/certified-stores-list-simple'); ?>

  <!-- Certified Stores List Section (SP) -->
  <?php get_template_part('template-parts/sections/stores/certified-stores-list-sp'); ?>

  <!-- Registered Stores List Section (PC) -->
  <?php get_template_part('template-parts/sections/stores/registered-stores-list-simple'); ?>

  <!-- Registered Stores List Section (SP) -->
  <?php get_template_part('template-parts/sections/stores/registered-stores-list-sp'); ?>

  <!-- Store Search Results Section -->
  <?php get_template_part('template-parts/sections/stores/store-search-results'); ?>

  <!-- Contact & Booking Section -->
  <?php get_template_part('template-parts/components/contact-booking'); ?>

</main>

<?php get_footer(); ?>