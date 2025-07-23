<?php
/**
 * Store Archive Page - 店舗一覧
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
  <section class="studio-search-section">
    <div class="studio-search-section__container">

      <!-- ヘッダーエリア -->
      <div class="studio-search-section__header">
        <div class="studio-search-section__label">
          <?php get_template_part('template-parts/components/thoughts-label', null, [
              'text' => 'Search for a photo studio'
          ]); ?>
        </div>
        <h1 class="studio-search-section__title">お近くのフォトスタジオを探す</h1>
        <p class="studio-search-section__subtitle">全国の写真館で678撮影が受けられます</p>
      </div>

      <!-- 検索バー -->
      <div class="studio-search-section__search">
        <div class="studio-search-section__search-box">
          <svg class="studio-search-section__search-icon" viewBox="0 0 24 24">
            <path
              d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 0 0 1.48-5.34c-.47-2.78-2.79-5-5.59-5.34a6.505 6.505 0 0 0-7.27 7.27c.34 2.8 2.56 5.12 5.34 5.59a6.5 6.5 0 0 0 5.34-1.48l.27.28v.79l4.25 4.25c.41.41 1.08.41 1.49 0 .41-.41.41-1.08 0-1.49L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
          </svg>
          <input type="text" placeholder="「埼玉県」「横浜市」「大阪」「うめざきまい」" class="studio-search-section__search-input">
        </div>
      </div>

      <!-- スタジオカード一覧 -->
      <div class="studio-search-section__cards">
        
        <?php if (have_posts()) : ?>
          <?php while (have_posts()) : the_post(); ?>
            <!-- 店舗カード（動的） -->
            <div class="studio-card">
              <div class="studio-card__image">
                <?php if (has_post_thumbnail()) : ?>
                  <?php the_post_thumbnail('medium', ['alt' => get_the_title()]); ?>
                <?php else : ?>
                  <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="<?php the_title(); ?>">
                <?php endif; ?>
                <div class="studio-card__location">
                  <?php echo get_field('store_location') ?: '東京'; ?>
                </div>
              </div>
              <div class="studio-card__content">
                <h3 class="studio-card__name"><?php the_title(); ?></h3>
                <div class="studio-card__details">
                  <p class="studio-card__address">
                    <?php echo get_field('store_address') ?: '住所情報を登録してください'; ?>
                  </p>
                  <div class="studio-card__hours">
                    <div class="studio-card__hour-item">
                      営業時間：<?php echo get_field('store_hours') ?: '10:00～19:00'; ?>
                    </div>
                    <div class="studio-card__hour-item">
                      定休日：<?php echo get_field('store_holiday') ?: '火曜日'; ?>
                    </div>
                  </div>
                </div>
                <?php get_template_part('template-parts/components/camera-button', null, [
                    'text' => '詳しく見る',
                    'bg_color' => 'detail-card',
                    'icon' => 'none',
                    'class' => 'studio-card__contact-btn',
                    'url' => get_permalink()
                ]); ?>
              </div>
            </div>
          <?php endwhile; ?>
        
        <?php else : ?>
          <!-- 店舗がない場合のサンプル表示 -->
          <div class="studio-card">
            <div class="studio-card__image">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="スタジオ写真">
              <div class="studio-card__location">東京</div>
            </div>
            <div class="studio-card__content">
              <h3 class="studio-card__name">678フォトスタジオ 青山店</h3>
              <div class="studio-card__details">
                <p class="studio-card__address">東京都港区南青山3-1-1<br>青山ビル 5F</p>
                <div class="studio-card__hours">
                  <div class="studio-card__hour-item">営業時間：10:00～19:00</div>
                  <div class="studio-card__hour-item">定休日：火曜日</div>
                </div>
              </div>
              <?php get_template_part('template-parts/components/camera-button', null, [
                  'text' => '詳しく見る',
                  'bg_color' => 'detail-card',
                  'icon' => 'none',
                  'class' => 'studio-card__contact-btn',
                  'url' => '/store-detail-test/'
              ]); ?>
            </div>
          </div>

          <div class="studio-card">
            <div class="studio-card__image">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="スタジオ写真">
              <div class="studio-card__location">東京</div>
            </div>
            <div class="studio-card__content">
              <h3 class="studio-card__name">678フォトスタジオ 渋谷店</h3>
              <div class="studio-card__details">
                <p class="studio-card__address">東京都渋谷区道玄坂2-10-12<br>新大宗ビル3F</p>
                <div class="studio-card__hours">
                  <div class="studio-card__hour-item">営業時間：9:00～18:00</div>
                  <div class="studio-card__hour-item">定休日：水曜日</div>
                </div>
              </div>
              <?php get_template_part('template-parts/components/camera-button', null, [
                  'text' => '詳しく見る',
                  'bg_color' => 'detail-card',
                  'icon' => 'none',
                  'class' => 'studio-card__contact-btn',
                  'url' => '/store-detail-test/'
              ]); ?>
            </div>
          </div>

          <div class="studio-card">
            <div class="studio-card__image">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="スタジオ写真">
              <div class="studio-card__location">神奈川</div>
            </div>
            <div class="studio-card__content">
              <h3 class="studio-card__name">678フォトスタジオ 横浜店</h3>
              <div class="studio-card__details">
                <p class="studio-card__address">神奈川県横浜市西区高島2-19-12<br>スカイビル20F</p>
                <div class="studio-card__hours">
                  <div class="studio-card__hour-item">営業時間：10:00～19:00</div>
                  <div class="studio-card__hour-item">定休日：月曜日</div>
                </div>
              </div>
              <?php get_template_part('template-parts/components/camera-button', null, [
                  'text' => '詳しく見る',
                  'bg_color' => 'detail-card',
                  'icon' => 'none',
                  'class' => 'studio-card__contact-btn',
                  'url' => '/store-detail-test/'
              ]); ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- ページネーション -->
      <?php if (have_posts() && $wp_query->max_num_pages > 1) : ?>
      <div class="studio-search-section__pagination">
        <?php
        the_posts_pagination(array(
          'mid_size'  => 2,
          'prev_text' => '◀',
          'next_text' => '▶',
          'screen_reader_text' => 'ページナビゲーション',
        ));
        ?>
      </div>
      <?php endif; ?>

    </div>
  </section>

</main>

<?php
get_template_part('template-parts/components/footer');
get_footer();
?>