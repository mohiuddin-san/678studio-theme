<?php
/*
Template Name: Store Detail Test
*/

get_header();
?>

<main class="main-content single-store">

  <!-- Breadcrumb -->
  <?php get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
      ['text' => 'TOP', 'url' => home_url()],
      ['text' => '店舗一覧', 'url' => '/stores/'],
      ['text' => '678フォトスタジオ 青山店', 'url' => '']
    ]
  ]); ?>

  <!-- Store Hero Section -->
  <section class="store-hero">
    <div class="store-hero__container">

      <!-- Store Info -->
      <div class="store-hero__info">
        <div class="store-hero__category">えがお写真館</div>
        <h1 class="store-hero__title">ロクナナハチ撮影店舗</h1>
      </div>

      <!-- Store Image -->
      <div class="store-hero__image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="店舗内観"
          class="store-hero__image-img">
      </div>

    </div>
  </section>

  <!-- Store Basic Info Section -->
  <section class="store-basic-info">
    <div class="store-basic-info__container">

      <h2 class="store-basic-info__heading">
        基本情報
        <img class="store-basic-info__underline"
          src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt="">
      </h2>

      <dl class="store-basic-info__list">
        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">全店舗</dt>
          <dd class="store-basic-info__data">えがお写真館</dd>
        </div>

        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">住所</dt>
          <dd class="store-basic-info__data">〒150-0001 東京都豊島区巣鴨1-2-3</dd>
        </div>

        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">電話番号</dt>
          <dd class="store-basic-info__data">
            <a href="tel:03-5944-5737" class="store-basic-info__phone">03-5944-5737</a>
          </dd>
        </div>

        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">最寄り駅</dt>
          <dd class="store-basic-info__data">JR渋谷駅より徒歩5分</dd>
        </div>

        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">営業時間</dt>
          <dd class="store-basic-info__data">10:00〜18:00</dd>
        </div>

        <div class="store-basic-info__item">
          <dt class="store-basic-info__label">定休日</dt>
          <dd class="store-basic-info__data">あり（3台）</dd>
        </div>
      </dl>

    </div>
  </section>

  <!-- Gallery Section -->
  <section class="store-gallery">
    <div class="store-basic-info__container">
      <h2 class="store-basic-info__heading">
        撮影ギャラリー
        <img class="store-basic-info__underline"
          src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt="">
      </h2>
    </div>

    <!-- Infinite Slider -->
    <div class="store-gallery__slider">
      <div class="store-gallery__track">
        <?php 
        // 仮の画像を10枚表示（同じ画像を複製して無限ループを実現）
        for ($i = 0; $i < 10; $i++) : 
        ?>
          <div class="store-gallery__item">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/grayscale.jpg" alt="ギャラリー画像 <?php echo $i + 1; ?>" data-full-image="<?php echo get_template_directory_uri(); ?>/assets/images/grayscale.jpg">
            <div class="store-gallery__overlay">
              <svg class="store-gallery__icon" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="16" r="10" stroke="white" stroke-width="2"/>
                <path d="M23 23L30 30" stroke="white" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <!-- Access Section -->
  <section class="store-access">
    <div class="store-basic-info__container">
      <h2 class="store-basic-info__heading">
        アクセス
        <img class="store-basic-info__underline"
          src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt="">
      </h2>

      <!-- Google Map -->
      <div class="store-access__map">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3240.8280566009746!2d139.7005599156845!3d35.65858628019441!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x60188ca40296b4dd%3A0x69bf49c622b1c221!2z5p2x5Lqu6aeF!5e0!3m2!1sja!2sjp!4v1673616000000!5m2!1sja!2sjp"
          width="100%" 
          height="400" 
          style="border:0;" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>

      <!-- 最寄り駅情報 -->
      <div class="store-access__station">
        <p class="store-access__station-text">JR渋谷駅より徒歩5分</p>
      </div>
    </div>
  </section>

  <!-- Contact & Booking Section -->
  <?php get_template_part('template-parts/components/contact-booking'); ?>
</main>

<!-- Lightbox Modal -->
<div class="lightbox" id="galleryLightbox">
  <div class="lightbox__overlay"></div>
  <div class="lightbox__content">
    <button class="lightbox__close" aria-label="閉じる">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
    <img class="lightbox__image" src="" alt="">
  </div>
</div>


<?php
get_template_part('template-parts/components/footer');
get_footer();
?>