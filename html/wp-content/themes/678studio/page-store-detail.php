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

</main>

<?php
get_template_part('template-parts/components/footer');
get_footer();
?>