<?php
/**
 * FAQ Section - About Page Specific
 * よくある質問セクション
 */

$faq_items = [
  [
    'question' => 'Q1. 撮影時間はどのくらいかかりますか？',
    'answer' => '来店からお帰りまで、約2時間です。ヘアメイクや着替えの時間も含まれておりますので、ゆっくりとお過ごしいただけます。'
  ],
  [
    'question' => 'Q2. どのような服装で撮影すればよいですか？',
    'answer' => 'お客様のお好みに合わせてお選びいただけます。フォーマルな装いから普段着まで、どのような服装でも美しく撮影いたします。事前にご相談いただければ、スタイリングのアドバイスもさせていただきます。'
  ],
  [
    'question' => 'Q3. データはどのような形で受け取れますか？',
    'answer' => 'デジタルデータはCD-ROMまたはUSBメモリでお渡しいたします。高画質のJPEG形式での納品となり、SNSでのシェアや印刷にもご利用いただけます。'
  ],
  [
    'question' => 'Q4. 予約の変更やキャンセルは可能ですか？',
    'answer' => 'キャンセル規定は各店舗によって異なります。ご予約いただいた店舗へ直接お問い合わせください。店舗の連絡先は店舗一覧ページからご確認いただけます。'
  ]
];
?>

<section class="faq-section" id="faq-section">
  <div class="faq-section__container">
    
    <!-- ヘッダーエリア -->
    <div class="faq-section__header scroll-animate-item" data-delay="0">
      <div class="faq-section__icon">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/light.svg" alt="太陽アイコン" class="faq-section__sun-icon">
      </div>
      <h2 class="faq-section__title">よくある質問</h2>
    </div>

    <!-- FAQ リスト -->
    <div class="faq-section__list scroll-animate-item" data-delay="0.2">
      <?php foreach ($faq_items as $index => $item): ?>
      <div class="faq-item" data-faq-item="<?php echo $index; ?>">
        <div class="faq-item__question" data-faq-toggle>
          <span class="faq-item__q-text"><?php echo esc_html($item['question']); ?></span>
          <img class="faq-item__icon" src="<?php echo get_template_directory_uri(); ?>/assets/images/plus.svg" alt="開く" data-faq-icon>
        </div>
        <div class="faq-item__answer" data-faq-answer>
          <div class="faq-item__a-content">
            <p class="faq-item__a-text"><?php echo esc_html($item['answer']); ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    
  </div>
</section>