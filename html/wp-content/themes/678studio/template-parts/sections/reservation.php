<?php
/**
 * Contact Section Component
 */
?>

<section class="contact-section">
  <!-- Top Header Container -->
  <div class="contact-header-container">
    <div class="contact-header">
      <?php get_template_part('template-parts/components/thoughts-label', null, [
          'text' => 'Contact & Reservations'
      ]); ?>
    </div>
  </div>

  <!-- Main Page Text -->
  <div class="contact-main-text">
    <p>TOP / 写真館情報</p>
  </div>

  <!-- Content Section -->
  <div class="contact-content">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt=""
      class="user-count-section__line" />
    <div class="contact-buttons">
      <?php get_template_part('template-parts/components/camera-button', null, [
          'text' => 'お問い合わせ',
          'url' => home_url('/search'),
          'class' => 'hero-section__button',
          'bg_color' => 'reservation',
          'icon' => 'home'
      ]); ?>
      <?php get_template_part('template-parts/components/camera-button', null, [
          'text' => 'ご予約',
          'url' => home_url('/search'),
          'class' => 'hero-section__button',
          'bg_color' => 'contact',
          'icon' => 'people'
      ]); ?>
    </div>

    <h2 class="contact-title">選択店舗確認</h2>

    <div class="contact-search">
      <select class="contact-select">
        <option>ご予約・お問い合わせの店舗をお選びください</option>
      </select>
    </div>

    <div class="contact-details" style="display: none;">
      <div class="contact-image">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cardpic-sample.jpg" alt="Studio Image">
      </div>
      <div class="contact-info">
        <h3>〇〇店での予約・相談</h3>
        <table>
          <tr><td>店舗名</td><td>選択された店舗名</td></tr>
          <tr><td>住所</td><td>店舗住所</td></tr>
          <tr><td>電話番号</td><td>店舗電話番号</td></tr>
          <tr><td>営業時間</td><td>店舗営業時間</td></tr>
          <tr><td>定休日</td><td>店舗定休日</td></tr>
        </table>
      </div>
    </div>

    <div class="schedule-container">
        <h1>ご予約</h1>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/underline-store.svg" alt=""
         class="user-count-section__line" />
         <div class="text-block">
        <p class= "titel">予約方法選択</p>
        <p class= "subtitle">予約方法を選択してください。2つの方法からお選びいただけます</p>
        </div>
        <div class="schedule-options">
            <div class="option-card">
                <h2>お電話での予約</h2>
                <button>お電話での予約 →</button>
            </div>
        </div>
    </div>
    <div class="form-container">
        <h1>オンライン予約フォーム</h1>

        <div class="input-field">
            <label for="name">お名前 (必須)</label>
            <input type="text" id="name" name="name" placeholder="例: 山田 花子">
        </div>

        <div class="input-field">
            <label for="kana">フリガナ (必須)</label>
            <input type="text" id="kana" name="kana" placeholder="例: ヤマダ ハナコ">
        </div>

        <div class="input-field">
            <label for="contact">お電話番号 (必須)</label>
            <input type="text" id="contact" name="contact" placeholder="例: 03-1234-5678">
        </div>

        <div class="input-field">
            <label for="email">メールアドレス (任意)</label>
            <input type="email" id="email" name="email" placeholder="例: hanako@example.com">
        </div>
 <div class="input-field">
  <label for="reservation_date">撮影希望日（必須）</label>
  <input type="date" id="reservation_date" name="reservation_date" required>
</div>

<div class="input-field">
  <label for="reservation_time_from">開始時間（From）</label>
  <input type="time" id="reservation_time_from" name="reservation_time_from" min="09:00" max="18:00" required>
</div>

<div class="input-field">
  <label for="reservation_time_to">終了時間（To）</label>
  <input type="time" id="reservation_time_to" name="reservation_time_to" min="09:00" max="20:00" required>
</div>

        <div class="textarea-field">
            <label for="notes">詳細相談内容</label>
            <label for="notes">詳しいご相談内容・ご質問（任意）</label>
            <textarea id="notes" name="notes" placeholder="ご不安なこと、知りたいこと、特別な配慮が必要なことなど、 何でもお気軽にお書きください。  
記入例： ・化粧品アレルギーがあります ・車椅子での利用を考えています ・家族へのサプライズ撮影を計画中です 
・以前他の写真館で満足できませんでした。"></textarea>
        </div>



        <div class="confirmation-field-level">
            <label for="notes">確認事項</label>
            <label for="notes">必須確認項目</label>
        </div>
        <div class="confirmation-field-check">
            <label>
                <input type="checkbox" name="agreement"> 個人情報の取り扱いについて同意する
            </label>
        </div>

        <div class="contact-buttons">
      <?php get_template_part('template-parts/components/camera-button', null, [
          'text' => 'お問い合わせ',
          'url' => home_url('/search'),
          'class' => 'hero-section__button',
          'bg_color' => 'reservation',
          'icon' => 'home'
      ]); ?>
        
    </div>
  </div>
</section>