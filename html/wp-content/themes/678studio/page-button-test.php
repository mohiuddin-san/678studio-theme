<?php
/**
 * Button Test Page Template - ボタンコンポーネント確認用
 */

get_header();
?>

<main class="main-content button-test-page">
  <div style="padding: 40px 0; min-height: 100vh;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">

      <!-- Page Title -->
      <div style="text-align: center; margin-bottom: 50px;">
        <h1 style="font-size: 32px; margin-bottom: 20px;">ボタンコンポーネント一覧</h1>
        <p style="color: #666; font-size: 16px;">camera-button.phpの全バリエーションの確認用ページです</p>
      </div>

      <!-- Button Grid -->
      <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; margin-bottom: 60px;">

        <!-- Default (white) -->
        <div style="text-align: center; padding: 30px; border-radius: 12px; border: 1px solid #e9ecef;">
          <h3 style="margin-bottom: 20px; color: #333;">Default (white)</h3>
          <div style="margin-bottom: 15px;">
            <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => 'デフォルトボタン',
              'url' => '#',
              'bg_color' => 'white',
              'icon' => 'cam'
            ]); ?>
          </div>
          <code style="font-size: 12px; color: #666; display: block; margin-top: 10px;">
            bg_color: 'white'<br>
            背景: #fff
          </code>
        </div>

        <!-- Blue -->
        <div style="text-align: center; padding: 30px; border-radius: 12px; border: 1px solid #e9ecef;">
          <h3 style="margin-bottom: 20px; color: #333;">Blue</h3>
          <div style="margin-bottom: 15px;">
            <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => 'ブルーボタン',
              'url' => '#',
              'bg_color' => 'blue',
              'icon' => 'cam'
            ]); ?>
          </div>
          <code style="font-size: 12px; color: #666; display: block; margin-top: 10px;">
            bg_color: 'blue'<br>
            背景: #c0d5dd
          </code>
        </div>

        <!-- Reservation -->
        <div style="text-align: center; padding: 30px; border-radius: 12px; border: 1px solid #e9ecef;">
          <h3 style="margin-bottom: 20px; color: #333;">Reservation</h3>
          <div style="margin-bottom: 15px;">
            <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '予約ボタン',
              'url' => '#',
              'bg_color' => 'reservation',
              'icon' => 'people'
            ]); ?>
          </div>
          <code style="font-size: 12px; color: #666; display: block; margin-top: 10px;">
            bg_color: 'reservation'<br>
            背景: #fcf6de
          </code>
        </div>

        <!-- Send -->
        <div style="text-align: center; padding: 30px; border-radius: 12px; border: 1px solid #e9ecef;">
          <h3 style="margin-bottom: 20px; color: #333;">Send</h3>
          <div style="margin-bottom: 15px;">
            <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '送信ボタン',
              'url' => '#',
              'bg_color' => 'send',
              'icon' => 'mailsend'
            ]); ?>
          </div>
          <code style="font-size: 12px; color: #666; display: block; margin-top: 10px;">
            bg_color: 'send'<br>
            背景: #c0d5dd
          </code>
        </div>

        <!-- Detail -->
        <div style="text-align: center; padding: 30px; border-radius: 12px; border: 1px solid #e9ecef;">
          <h3 style="margin-bottom: 20px; color: #333;">Detail</h3>
          <div style="margin-bottom: 15px;">
            <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '詳細ボタン',
              'url' => '#',
              'bg_color' => 'detail',
              'icon' => 'none'
            ]); ?>
          </div>
          <code style="font-size: 12px; color: #666; display: block; margin-top: 10px;">
            bg_color: 'detail'<br>
            背景: #F5E2CF<br>
            角丸: 6px
          </code>
        </div>

        <!-- Detail Card -->
        <div style="text-align: center; padding: 30px; border-radius: 12px; border: 1px solid #e9ecef;">
          <h3 style="margin-bottom: 20px; color: #333;">Detail Card</h3>
          <div style="margin-bottom: 15px;">
            <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => '詳細カードボタン',
              'url' => '#',
              'bg_color' => 'detail-card',
              'icon' => 'none'
            ]); ?>
          </div>
          <code style="font-size: 12px; color: #666; display: block; margin-top: 10px;">
            bg_color: 'detail-card'<br>
            背景: #F5E2CF<br>
            角丸: 10px<br>
            小さめサイズ
          </code>
        </div>

        <!-- Contact -->
        <div style="text-align: center; padding: 30px; border-radius: 12px; border: 1px solid #e9ecef;">
          <h3 style="margin-bottom: 20px; color: #333;">Contact</h3>
          <div style="margin-bottom: 15px;">
            <?php get_template_part('template-parts/components/camera-button', null, [
              'text' => 'お問い合わせボタン',
              'url' => '#',
              'bg_color' => 'contact',
              'icon' => 'home'
            ]); ?>
          </div>
          <code style="font-size: 12px; color: #666; display: block; margin-top: 10px;">
            bg_color: 'contact'<br>
            背景: #F5E2CF
          </code>
        </div>

      </div>

      <!-- Usage Examples -->
      <div style="background: #f8f9fa; padding: 30px; border-radius: 12px; border: 1px solid #e9ecef;">
        <h2 style="margin-bottom: 20px;">使用方法</h2>
        <pre
          style="background: #fff; padding: 20px; border-radius: 8px; overflow-x: auto; font-size: 14px; line-height: 1.5;"><code>&lt;?php get_template_part('template-parts/components/camera-button', null, [
  'text' => 'ボタンテキスト',
  'url' => '#',
  'bg_color' => 'detail', // white, blue, reservation, send, detail, detail-card, contact
  'icon' => 'cam' // cam, people, mailsend, home, none
]); ?&gt;</code></pre>
      </div>

      <!-- Navigation -->
      <div style="text-align: center; margin-top: 40px;">
        <a href="<?php echo home_url(); ?>"
          style="display: inline-block; padding: 12px 24px; background: #007cba; color: white; text-decoration: none; border-radius: 6px;">トップページに戻る</a>
      </div>

    </div>
  </div>
</main>

<?php get_footer(); ?>