<?php
/**
 * Single Store Template
 * 店舗詳細ページテンプレート
 * 
 * @package 678studio
 */

get_header();

// Start the loop
while (have_posts()) : the_post();
?>

<main class="main-content single-store">
  
  <!-- Store Hero Section -->
  <section class="store-hero">
    <div class="store-hero__container">
      <?php if (has_post_thumbnail()) : ?>
        <div class="store-hero__image">
          <?php the_post_thumbnail('full', ['class' => 'store-hero__image-img']); ?>
        </div>
      <?php endif; ?>
      
      <div class="store-hero__content">
        <h1 class="store-hero__title"><?php the_title(); ?></h1>
        <?php if (get_field('store_catchphrase')) : ?>
          <p class="store-hero__catchphrase"><?php the_field('store_catchphrase'); ?></p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Breadcrumb -->
  <?php get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
      ['text' => 'TOP', 'url' => home_url()],
      ['text' => '店舗一覧', 'url' => '/stores/'],
      ['text' => get_the_title(), 'url' => '']
    ]
  ]); ?>

  <!-- Store Information Section -->
  <section class="store-info">
    <div class="store-info__container">
      
      <!-- Basic Information -->
      <div class="store-info__basic">
        <h2 class="store-info__heading">店舗情報</h2>
        
        <dl class="store-info__list">
          <?php if (get_field('store_address')) : ?>
          <div class="store-info__item">
            <dt class="store-info__label">住所</dt>
            <dd class="store-info__data"><?php the_field('store_address'); ?></dd>
          </div>
          <?php endif; ?>
          
          <?php if (get_field('store_phone')) : ?>
          <div class="store-info__item">
            <dt class="store-info__label">電話番号</dt>
            <dd class="store-info__data">
              <a href="tel:<?php echo esc_attr(str_replace('-', '', get_field('store_phone'))); ?>" class="store-info__phone">
                <?php the_field('store_phone'); ?>
              </a>
            </dd>
          </div>
          <?php endif; ?>
          
          <?php if (get_field('store_hours')) : ?>
          <div class="store-info__item">
            <dt class="store-info__label">営業時間</dt>
            <dd class="store-info__data"><?php the_field('store_hours'); ?></dd>
          </div>
          <?php endif; ?>
          
          <?php if (get_field('store_holiday')) : ?>
          <div class="store-info__item">
            <dt class="store-info__label">定休日</dt>
            <dd class="store-info__data"><?php the_field('store_holiday'); ?></dd>
          </div>
          <?php endif; ?>
        </dl>
      </div>

      <!-- Store Features -->
      <?php if (get_field('store_features')) : ?>
      <div class="store-info__features">
        <h2 class="store-info__heading">店舗の特徴</h2>
        <div class="store-info__features-content">
          <?php the_field('store_features'); ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- Store Gallery -->
  <?php if (have_rows('store_gallery')) : ?>
  <section class="store-gallery">
    <div class="store-gallery__container">
      <h2 class="store-gallery__heading">店内の様子</h2>
      <div class="store-gallery__grid">
        <?php while (have_rows('store_gallery')) : the_row(); 
          $image = get_sub_field('gallery_image');
        ?>
          <div class="store-gallery__item">
            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" class="store-gallery__image">
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Access Map -->
  <?php if (get_field('store_map')) : ?>
  <section class="store-access">
    <div class="store-access__container">
      <h2 class="store-access__heading">アクセス</h2>
      <div class="store-access__map">
        <?php the_field('store_map'); ?>
      </div>
      
      <?php if (get_field('store_access_info')) : ?>
      <div class="store-access__info">
        <?php the_field('store_access_info'); ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- CTA Section -->
  <section class="store-cta">
    <div class="store-cta__container">
      <h2 class="store-cta__heading">ご予約・お問い合わせ</h2>
      <p class="store-cta__text">
        撮影のご予約やお問い合わせは、お電話またはWebフォームから承っております。
      </p>
      <div class="store-cta__buttons">
        <?php if (get_field('store_phone')) : ?>
        <a href="tel:<?php echo esc_attr(str_replace('-', '', get_field('store_phone'))); ?>" class="store-cta__button store-cta__button--phone">
          <?php get_template_part('template-parts/components/camera-button', null, [
            'text' => '電話で予約',
            'url' => 'tel:' . esc_attr(str_replace('-', '', get_field('store_phone'))),
            'bg_color' => 'reservation',
            'icon' => 'people'
          ]); ?>
        </a>
        <?php endif; ?>
        
        <?php get_template_part('template-parts/components/camera-button', null, [
          'text' => 'お問い合わせ',
          'url' => '/contact/?store=' . get_the_ID(),
          'bg_color' => 'contact',
          'icon' => 'home'
        ]); ?>
      </div>
    </div>
  </section>

  <!-- Contact & Booking Section (共通) -->
  <?php get_template_part('template-parts/components/contact-booking'); ?>

</main>

<?php
endwhile;

get_template_part('template-parts/components/footer');
get_footer();
?>