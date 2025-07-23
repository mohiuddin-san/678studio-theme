<?php
/**
 * Studio Search Section - お近くのフォトスタジオを探す
 */
?>

<section class="studio-search-section">
  <div class="studio-search-section__container">

    <!-- ヘッダーエリア -->
    <div class="studio-search-section__header">
      <div class="studio-search-section__label">
        <?php get_template_part('template-parts/components/thoughts-label', null, [
            'text' => 'Search for a photo studio'
        ]); ?>
      </div>
      <h2 class="studio-search-section__title">お近くのフォトスタジオを探す</h2>
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
      <!-- カード1 -->
      <div class="studio-card">
        <div class="studio-card__image">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="スタジオ写真">
          <div class="studio-card__location">東京</div>
        </div>
        <div class="studio-card__content">
          <h3 class="studio-card__name">えがお写真館</h3>
          <div class="studio-card__details">
            <p class="studio-card__address">東京都豊島区巣鴨1丁目22-26<br>サマンサハイツ</p>
            <div class="studio-card__hours">
              <div class="studio-card__hour-item">営業時間：10:00～19:00</div>
              <div class="studio-card__hour-item">定休日：10:00～19:00</div>
            </div>
          </div>
          <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '詳しく見る',
              'bg_color' => 'detail-card',
              'icon' => 'none',
              'class' => 'studio-card__contact-btn'
          ]); ?>
        </div>
      </div>

      <!-- カード2 -->
      <div class="studio-card">
        <div class="studio-card__image">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="スタジオ写真">
          <div class="studio-card__location">東京</div>
        </div>
        <div class="studio-card__content">
          <h3 class="studio-card__name">えがお写真館</h3>
          <div class="studio-card__details">
            <p class="studio-card__address">東京都豊島区巣鴨1丁目22-26<br>サマンサハイツ</p>
            <div class="studio-card__hours">
              <div class="studio-card__hour-item">営業時間：10:00～19:00</div>
              <div class="studio-card__hour-item">定休日：10:00～19:00</div>
            </div>
          </div>
          <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '詳しく見る',
              'bg_color' => 'detail-card',
              'icon' => 'none',
              'class' => 'studio-card__contact-btn'
          ]); ?>
        </div>
      </div>

      <!-- カード3 -->
      <div class="studio-card">
        <div class="studio-card__image">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="スタジオ写真">
          <div class="studio-card__location">東京</div>
        </div>
        <div class="studio-card__content">
          <h3 class="studio-card__name">えがお写真館</h3>
          <div class="studio-card__details">
            <p class="studio-card__address">東京都豊島区巣鴨1丁目22-26<br>サマンサハイツ</p>
            <div class="studio-card__hours">
              <div class="studio-card__hour-item">営業時間：10:00～19:00</div>
              <div class="studio-card__hour-item">定休日：10:00～19:00</div>
            </div>
          </div>
          <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '詳しく見る',
              'bg_color' => 'detail-card',
              'icon' => 'none',
              'class' => 'studio-card__contact-btn'
          ]); ?>
        </div>
      </div>

      <!-- カード4 -->
      <div class="studio-card">
        <div class="studio-card__image">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="スタジオ写真">
          <div class="studio-card__location">東京</div>
        </div>
        <div class="studio-card__content">
          <h3 class="studio-card__name">えがお写真館</h3>
          <div class="studio-card__details">
            <p class="studio-card__address">東京都豊島区巣鴨1丁目22-26<br>サマンサハイツ</p>
            <div class="studio-card__hours">
              <div class="studio-card__hour-item">営業時間：10:00～19:00</div>
              <div class="studio-card__hour-item">定休日：10:00～19:00</div>
            </div>
          </div>
          <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '詳しく見る',
              'bg_color' => 'detail-card',
              'icon' => 'none',
              'class' => 'studio-card__contact-btn'
          ]); ?>
        </div>
      </div>

      <!-- カード5 -->
      <div class="studio-card">
        <div class="studio-card__image">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="スタジオ写真">
          <div class="studio-card__location">東京</div>
        </div>
        <div class="studio-card__content">
          <h3 class="studio-card__name">えがお写真館</h3>
          <div class="studio-card__details">
            <p class="studio-card__address">東京都豊島区巣鴨1丁目22-26<br>サマンサハイツ</p>
            <div class="studio-card__hours">
              <div class="studio-card__hour-item">営業時間：10:00～19:00</div>
              <div class="studio-card__hour-item">定休日：10:00～19:00</div>
            </div>
          </div>
          <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '詳しく見る',
              'bg_color' => 'detail-card',
              'icon' => 'none',
              'class' => 'studio-card__contact-btn'
          ]); ?>
        </div>
      </div>

      <!-- カード6 -->
      <div class="studio-card">
        <div class="studio-card__image">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="スタジオ写真">
          <div class="studio-card__location">東京</div>
        </div>
        <div class="studio-card__content">
          <h3 class="studio-card__name">えがお写真館</h3>
          <div class="studio-card__details">
            <p class="studio-card__address">東京都豊島区巣鴨1丁目22-26<br>サマンサハイツ</p>
            <div class="studio-card__hours">
              <div class="studio-card__hour-item">営業時間：10:00～19:00</div>
              <div class="studio-card__hour-item">定休日：10:00～19:00</div>
            </div>
          </div>
          <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '詳しく見る',
              'bg_color' => 'detail-card',
              'icon' => 'none',
              'class' => 'studio-card__contact-btn'
          ]); ?>
        </div>
      </div>
    </div>

    <!-- ページネーション -->
    <div class="studio-search-section__pagination">
      <button class="pagination-btn pagination-btn--prev">◀</button>
      <div class="pagination-numbers">
        <span class="active">1</span>
        <span>2</span>
        <span>3</span>
        <span>4</span>
      </div>
      <button class="pagination-btn pagination-btn--next">▶</button>
    </div>

  </div>
</section>