<?php
/**
 * Contact & Booking Section Component
 * お問い合わせ・ご予約セクション
 * 
 * 使用方法:
 * <?php get_template_part('template-parts/components/contact-booking'); ?>
*/
?>

<section class="contact-booking">
  <div class="contact-booking__container">

    <!-- Header Area -->
    <div class="contact-booking__header">
      <div class="contact-booking__label">
        <span class="contact-booking__label-text">Inquiries & Bookings</span>
      </div>
      <h2 class="contact-booking__title">お問い合わせ・ご予約相談</h2>
      <p class="contact-booking__description">
        ロクナナハチ撮影のご予約・ご相談はこちらから
      </p>
    </div>

    <!-- Buttons Area -->
    <div class="contact-booking__buttons">

      <!-- ご予約相談ボタン (左側) -->
      <div class="contact-booking__button">
        <?php get_template_part('template-parts/components/camera-button', null, [
          'text' => 'ご予約相談',
          'url' => home_url('/studio-reservation/'),
          'bg_color' => 'reservation',
          'icon' => 'people'
        ]); ?>
      </div>

      <!-- お問い合わせボタン (右側) -->
      <div class="contact-booking__button">
        <?php get_template_part('template-parts/components/camera-button', null, [
          'text' => 'お問い合わせ',
          'url' => home_url('/studio-inquiry/'),
          'bg_color' => 'contact',
          'icon' => 'home'
        ]); ?>
      </div>

    </div>

  </div>
</section>