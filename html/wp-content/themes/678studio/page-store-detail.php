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
        <img class="store-basic-info__underline" src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt="">
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
    <div class="store-gallery__container">
      <h2 class="store-gallery__heading">
        ギャラリー
        <img class="store-gallery__underline" src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt="">
      </h2>
      
      <div class="store-gallery__slider-container">
        <div class="store-gallery__slider" id="gallery-slider">
          <div class="store-gallery__slide">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/gallery-1.png" alt="ギャラリー画像1" class="store-gallery__image">
          </div>
          <div class="store-gallery__slide">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/gallery_003.png" alt="ギャラリー画像2" class="store-gallery__image">
          </div>
          <div class="store-gallery__slide">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/plan-1.jpg" alt="ギャラリー画像3" class="store-gallery__image">
          </div>
          <div class="store-gallery__slide">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-1.jpg" alt="ギャラリー画像4" class="store-gallery__image">
          </div>
          <div class="store-gallery__slide">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-2.jpg" alt="ギャラリー画像5" class="store-gallery__image">
          </div>
        </div>
        
        <div class="store-gallery__controls">
          <button class="store-gallery__prev" id="gallery-prev">‹</button>
          <button class="store-gallery__next" id="gallery-next">›</button>
        </div>
        
        <div class="store-gallery__indicators" id="gallery-indicators">
          <button class="store-gallery__indicator active" data-slide="0"></button>
          <button class="store-gallery__indicator" data-slide="1"></button>
          <button class="store-gallery__indicator" data-slide="2"></button>
          <button class="store-gallery__indicator" data-slide="3"></button>
          <button class="store-gallery__indicator" data-slide="4"></button>
        </div>
      </div>
    </div>
  </section>

  <!-- Access Section -->
  <section class="store-access">
    <div class="store-access__container">
      <h2 class="store-access__heading">
        アクセス
        <img class="store-access__underline" src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt="">
      </h2>
      
      <div class="store-access__content">
        <div class="store-access__info">
          <div class="store-access__address">
            <h3 class="store-access__address-title">住所</h3>
            <p class="store-access__address-text">〒150-0001 東京都豊島区巣鴨1-2-3</p>
          </div>
          
          <div class="store-access__transport">
            <h3 class="store-access__transport-title">交通アクセス</h3>
            <p class="store-access__transport-text">JR渋谷駅より徒歩5分</p>
          </div>
        </div>
        
        <div class="store-access__map">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3241.5263892573147!2d139.70153731582045!3d35.6617773802019!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x60188ca96aa1dd7b%3A0x67c9b0a1cefbe82a!2z5p2x5Lqs6YO95riL6LC35Yy66J2j55Sw!5e0!3m2!1sja!2sjp!4v1642567890123!5m2!1sja!2sjp" 
            width="100%" 
            height="300" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade"
            class="store-access__map-iframe">
          </iframe>
        </div>
      </div>
    </div>
  </section>

</main>

<?php
get_template_part('template-parts/components/footer');
get_footer();
?>