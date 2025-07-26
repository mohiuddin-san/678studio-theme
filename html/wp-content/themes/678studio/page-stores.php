<?php
/**
 * Template Name: 店舗一覧
 * Description: 写真館の店舗一覧ページ
 */

// SEO optimization for stores list page
add_filter('pre_get_document_title', function() {
    return '店舗一覧 - 678写真館';
}, 10);

add_action('wp_head', function() {
    echo '<meta name="description" content="678写真館の店舗一覧ページです。全国の提携写真館で678撮影サービスをご利用いただけます。">' . "\n";
    echo '<meta property="og:title" content="店舗一覧 - 678写真館">' . "\n";
    echo '<meta property="og:description" content="678写真館の店舗一覧。お近くの写真館を検索できます。">' . "\n";
}, 1);

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

  <!-- Store Search Section -->
  <?php get_template_part('template-parts/sections/home/studio-search'); ?>

</main>

<?php
get_template_part('template-parts/components/footer');
get_footer();
?>