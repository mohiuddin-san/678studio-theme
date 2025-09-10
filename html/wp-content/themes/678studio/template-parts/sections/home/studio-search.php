<?php
/**
 * Studio Search Section - お近くのフォトスタジオを探す
 * Search form only - redirects to /stores/ page for results
 */
?>

<section class="studio-search-section" id="studio-search-section">
  <div class="studio-search-section__container">

    <!-- ヘッダーエリア -->
    <div class="studio-search-section__header scroll-animate-item" data-delay="0">
      <div class="studio-search-section__label">
        <?php get_template_part('template-parts/components/thoughts-label', null, [
            'text' => 'Search for a photo studio'
        ]); ?>
      </div>
      <h2 class="studio-search-section__title">お近くのフォトスタジオを探す</h2>
      <p class="studio-search-section__subtitle">全国の写真館で678撮影が受けられます</p>
    </div>

    <!-- 検索フォーム -->
    <div class="studio-search-section__search scroll-animate-item" data-delay="0.2">
      <form class="studio-search-section__form" action="<?php echo home_url('/stores/'); ?>" method="GET">
        <div class="studio-search-section__input-group">
          <div class="studio-search-section__search-box">
            <svg class="studio-search-section__search-icon" viewBox="0 0 24 24">
              <path
                d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 0 0 1.48-5.34c-.47-2.78-2.79-5-5.59-5.34a6.505 6.505 0 0 0-7.27 7.27c.34 2.8 2.56 5.12 5.34 5.59a6.5 6.5 0 0 0 5.34-1.48l.27.28v.79l4.25 4.25c.41.41 1.08.41 1.49 0 .41-.41.41-1.08 0-1.49L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
            </svg>
            <input type="text" 
                   name="search" 
                   placeholder="店舗名・地域・最寄り駅をご入力ください" 
                   class="studio-search-section__search-input"
                   id="studio-search-input">
          </div>
          
          <!-- 都道府県選択 -->
          <div class="studio-search-section__prefecture-select">
            <select name="prefecture" class="studio-search-section__prefecture-dropdown">
              <option value="">都道府県を選択</option>
              <option value="北海道">北海道</option>
              <option value="青森県">青森県</option>
              <option value="岩手県">岩手県</option>
              <option value="宮城県">宮城県</option>
              <option value="秋田県">秋田県</option>
              <option value="山形県">山形県</option>
              <option value="福島県">福島県</option>
              <option value="茨城県">茨城県</option>
              <option value="栃木県">栃木県</option>
              <option value="群馬県">群馬県</option>
              <option value="埼玉県">埼玉県</option>
              <option value="千葉県">千葉県</option>
              <option value="東京都">東京都</option>
              <option value="神奈川県">神奈川県</option>
              <option value="新潟県">新潟県</option>
              <option value="富山県">富山県</option>
              <option value="石川県">石川県</option>
              <option value="福井県">福井県</option>
              <option value="山梨県">山梨県</option>
              <option value="長野県">長野県</option>
              <option value="岐阜県">岐阜県</option>
              <option value="静岡県">静岡県</option>
              <option value="愛知県">愛知県</option>
              <option value="三重県">三重県</option>
              <option value="滋賀県">滋賀県</option>
              <option value="京都府">京都府</option>
              <option value="大阪府">大阪府</option>
              <option value="兵庫県">兵庫県</option>
              <option value="奈良県">奈良県</option>
              <option value="和歌山県">和歌山県</option>
              <option value="鳥取県">鳥取県</option>
              <option value="島根県">島根県</option>
              <option value="岡山県">岡山県</option>
              <option value="広島県">広島県</option>
              <option value="山口県">山口県</option>
              <option value="徳島県">徳島県</option>
              <option value="香川県">香川県</option>
              <option value="愛媛県">愛媛県</option>
              <option value="高知県">高知県</option>
              <option value="福岡県">福岡県</option>
              <option value="佐賀県">佐賀県</option>
              <option value="長崎県">長崎県</option>
              <option value="熊本県">熊本県</option>
              <option value="大分県">大分県</option>
              <option value="宮崎県">宮崎県</option>
              <option value="鹿児島県">鹿児島県</option>
              <option value="沖縄県">沖縄県</option>
            </select>
          </div>
        </div>
        
        <!-- 検索ボタン -->
        <div class="studio-search-section__submit">
          <button type="submit" class="studio-search-section__search-btn">検索</button>
        </div>
      </form>
    </div>

  </div>
</section>

