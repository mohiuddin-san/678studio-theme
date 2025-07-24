<?php
/**
 * Contact Section Component
 */
?>

<section class="contact-section">

  <div class="contact-main-text">
    <p>TOP / 写真館情報</p>
  </div>

  <!-- Content Section -->
  <div class="contact-content">
     <?php get_template_part('template-parts/components/thoughts-label', null, [
          'text' => 'Contact & Reservations'
      ]); ?>
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/underline.svg" alt=""
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

    <h2 class="contact-title">選択店舗</h2>

    <div class="contact-search">
      <select class="contact-select">
        <option>ご予約・お問い合わせの店舗をお選びください</option>
      </select>
    </div>

    <div class="contact-details">
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
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/underline.svg" alt=""
         class="user-count-section__line" />
        <p>予約方法を選択してください。2つの方法からお選びいただけます</p>
        <div class="schedule-options">
            <div class="option-card">
                <h2>オンライン予約（推奨）</h2>
                <ul>
                    <li>24時間いつでも申込可能</li>
                    <li>ゆっくり考えながら入力できる</li>
                    <li>自動返信で申込内容を確認</li>
                    <li>24時間以内にスタッフから連絡</li>
                </ul>
                <button>オンライン予約 →</button>
            </div>
            <div class="option-card">
                <h2>お電話での予約</h2>
                <ul>
                    <li>すぐに相談・質問ができる</li>
                    <li>詳しい説明を聞ける</li>
                    <li>その場で日程調整可能</li>
                    <li>不安をすぐに解消</li>
                </ul>
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
            <label for="phone">電話番号 (必須)</label>
            <input type="text" id="phone" name="phone" placeholder="例: 03-1234-5678">
        </div>

        <div class="dropdown-field">
            <label for="method">ご希望の予約方法 (必須)</label>
            <select id="method" name="method">
                <option value="online">選択してください</option>
                <option value="online">オンライン</option>
                <option value="phone">お電話</option>
            </select>
        </div>

        <div class="input-field">
            <label for="contact">お電話番号 (必須)</label>
            <input type="text" id="contact" name="contact" placeholder="例: 03-1234-5678">
        </div>

        <div class="input-field">
            <label for="email">メールアドレス (任意)</label>
            <input type="email" id="email" name="email" placeholder="例: hanako@example.com">
        </div>

        <h1>ご希望内容</h1>

        <div class="input-field">
            <label for="reservation">ご希望プラン (任意)</label>
            <select id="reservation" name="reservation">
                <option value="">選択してください</option>
                <option value="plan1">プラン1</option>
                <option value="plan2">プラン2</option>
            </select>
        </div>

        <div class="input-field">
            <label for="period">ご希望期間 (任意)</label>
            <select id="period" name="period">
                <option value="">選択してください</option>
                <option value="1month">1ヶ月</option>
                <option value="3months">3ヶ月</option>
            </select>
        </div>

        <div class="input-field">
            <label for="time">ご希望時間 (任意)</label>
            <select id="time" name="time">
                <option value="">選択してください</option>
                <option value="morning">午前</option>
                <option value="afternoon">午後</option>
            </select>
        </div>
        <div class="textarea-field">
            <textarea id="notes" name="notes" placeholder="詳細欄"></textarea>
        </div>
        <div class="input-field">
            <label for="area">ご希望エリア (任意)</label>
            <select id="area" name="area">
                <option value="">選択してください</option>
                <option value="tokyo">東京</option>
                <option value="osaka">大阪</option>
            </select>
        </div>

        <div class="textarea-field">
            <label for="notes">詳細相談内容</label>
            <label for="notes">詳しいご相談内容・ご質問（任意）</label>
            <textarea id="notes" name="notes" placeholder="ご不安なこと、知りたいこと、特別な配慮が必要なことなど、 何でもお気軽にお書きください。  
記入例： ・化粧品アレルギーがあります ・車椅子での利用を考えています ・家族へのサプライズ撮影を計画中です 
・以前他の写真館で満足できませんでした。"></textarea>
        </div>

        <div class="confirmation-field">
            <label>
                <input type="checkbox" name="agreement"> 個人情報の取り扱いについて同意する
            </label>
        </div>

        <button type="submit">送信</button>
    </div>
  </div>
</section>