<?php
/**
 * Photography Flow Section - About Page Specific
 * 撮影の流れセクション
 */
?>

<section class="photography-flow-section" id="photography-flow-section">
  <div class="photography-flow-section__container">

    <!-- ヘッダーエリア -->
    <div class="photography-flow-section__header scroll-animate-item" data-delay="0">
      <div class="photography-flow-section__label">
        <span class="photography-flow-section__label-text">Steps</span>
      </div>
      <h2 class="photography-flow-section__title">撮影の流れ</h2>
    </div>

    <!-- フローカード一覧 -->
    <div class="photography-flow-section__cards scroll-animate-item" data-delay="0.2">

      <!-- フローカード1 -->
      <div class="flow-card">
        <div class="flow-card__image">
          <img class="flow-card__image-img" src="<?php echo get_template_directory_uri(); ?>/assets/images/flow1.jpg"
            alt="お問い合わせ・ご予約">
        </div>
        <div class="flow-card__content">
          <div class="flow-card__header">
            <div class="flow-card__number">
              <img class="flow-card__number-icon"
                src="<?php echo get_template_directory_uri(); ?>/assets/images/number.svg" alt="#">
              <span class="flow-card__number-text">1</span>
            </div>
            <div class="flow-card__badge">ご予約日時のご相談</div>
          </div>
          <img class="flow-card__underline"
            src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-long.svg" alt="">
          <p class="flow-card__description">
            <a href="/stores" style="color: #a5c3cf; text-decoration: underline;">店舗検索</a>よりお近くの店舗をお選びいただき、ご予約日時のご相談を<a
              href="/studio-reservation"
              style="color: #a5c3cf; text-decoration: underline;">メールフォーム</a>またはお電話にて、ご連絡ください。
          </p>
        </div>
      </div>

      <!-- フローカード2 -->
      <div class="flow-card">
        <div class="flow-card__image">
          <img class="flow-card__image-img" src="<?php echo get_template_directory_uri(); ?>/assets/images/flow2.jpg"
            alt="事前カウンセリング">
        </div>
        <div class="flow-card__content">
          <div class="flow-card__header">
            <div class="flow-card__number">
              <img class="flow-card__number-icon"
                src="<?php echo get_template_directory_uri(); ?>/assets/images/number.svg" alt="#">
              <span class="flow-card__number-text">2</span>
            </div>
            <div class="flow-card__badge">ご指定の店舗と日時のご相談</div>
          </div>
          <img class="flow-card__underline"
            src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-long.svg" alt="">
          <p class="flow-card__description">
            メールフォームの場合は、お問い合わせ後に、ご指定の店舗より改めて返信させていただきます。
            撮影日時や撮影内容など、詳細をご相談ください。またオプションは各店舗によって異なりますので、合わせてご相談・ご確認いただけるとスムーズです。
          </p>
        </div>
      </div>

      <!-- フローカード3 -->
      <div class="flow-card">
        <div class="flow-card__image">
          <img class="flow-card__image-img" src="<?php echo get_template_directory_uri(); ?>/assets/images/flow3.jpg"
            alt="撮影">
        </div>
        <div class="flow-card__content">
          <div class="flow-card__header">
            <div class="flow-card__number">
              <img class="flow-card__number-icon"
                src="<?php echo get_template_directory_uri(); ?>/assets/images/number.svg" alt="#">
              <span class="flow-card__number-text">3</span>
            </div>
            <div class="flow-card__badge">撮影当日</div>
          </div>
          <img class="flow-card__underline"
            src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-long.svg" alt="">
          <p class="flow-card__description">
            ご予約いただいた日時に、直接ご予約店舗までお越しください。
            あなたの自然な雰囲気を重視したヘアメイクと、日常の延長にある美しさを引き出した撮影で、ベストショットを写真に収めます。<br><br>
            <span class="flow-card__note">※オプションメニューについては店舗によって異なりますので、各店舗にお問い合わせください。</span>
          </p>
        </div>
      </div>

      <!-- フローカード4 -->
      <div class="flow-card">
        <div class="flow-card__image">
          <img class="flow-card__image-img" src="<?php echo get_template_directory_uri(); ?>/assets/images/flow4.jpg"
            alt="写真確定・データ納品">
        </div>
        <div class="flow-card__content">
          <div class="flow-card__header">
            <div class="flow-card__number">
              <img class="flow-card__number-icon"
                src="<?php echo get_template_directory_uri(); ?>/assets/images/number.svg" alt="#">
              <span class="flow-card__number-text">4</span>
            </div>
            <div class="flow-card__badge">撮影当日または後日</div>
          </div>
          <img class="flow-card__underline"
            src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-long.svg" alt="">
          <p class="flow-card__description">
            撮影後、お客様と一緒に写真を選定し、写真のデータをお渡しいたします。<br><br>
            <span class="flow-card__note">※データの納品方法は店舗によって異なることがございますので、詳しくは各店舗にお問合せください。</span>
          </p>
        </div>
      </div>

    </div>

  </div>
</section>