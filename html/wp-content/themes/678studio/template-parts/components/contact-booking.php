<?php
/**
 * Contact & Booking Section Component
 * お問い合わせ・ご予約セクション
 *
 * 使用方法:
 * <?php get_template_part('template-parts/components/contact-booking'); ?>
 * または店舗IDを指定:
 * <?php get_template_part('template-parts/components/contact-booking', null, ['shop_id' => $shop_id]); ?>
*/

// 渡された引数を取得
$shop_id = isset($args['shop_id']) ? intval($args['shop_id']) : 0;

// URLパラメータを構築
$reservation_url = home_url('/studio-reservation/');
$inquiry_url = home_url('/studio-inquiry/');

if ($shop_id > 0) {
    $reservation_url = add_query_arg('shop_id', $shop_id, $reservation_url);
    $inquiry_url = add_query_arg('shop_id', $shop_id, $inquiry_url);
}
?>

<section class="contact-booking">
  <div class="contact-booking__container">

    <!-- Decorative Header -->
    <div class="contact-booking__decoration">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/booking-area-header-ilst.svg" alt="" class="contact-booking__decoration-img">
    </div>

    <!-- Main Title -->
    <h2 class="contact-booking__main-title">Bookings & Inquiries</h2>

    <!-- Sub Title -->
    <h3 class="contact-booking__sub-title">お問い合わせ・ご予約相談</h3>

    <!-- Description -->
    <div class="contact-booking__description">
      <p>ロクナナハチ<br>撮影のご予約・ご相談はこちらから</p>
    </div>

    <!-- Buttons Area -->
    <div class="contact-booking__buttons">
      <!-- ご予約ボタン (左側) -->
      <a href="<?php echo esc_url($reservation_url); ?>" class="contact-booking__button contact-booking__button--primary">
        <span>ご予約</span>
      </a>

      <!-- お問い合わせボタン (右側) -->
      <a href="<?php echo esc_url($inquiry_url); ?>" class="contact-booking__button contact-booking__button--secondary">
        <span>お問い合わせ</span>
      </a>
    </div>

  </div>
</section>