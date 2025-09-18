<?php
/**
 * FAQ Section - About Page Specific
 * よくある質問セクション
 */

$faq_items = [
  [
    'question' => 'どの様な服装で撮影すれば良いですか？',
    'answer' => 'お客様のお好みに合わせてお選びいただけます。フォーマルな装いから普段着まで、どのような服装でも美しく撮影いたします。どの様な写真が撮りたいかでアドバイスをさせていただきますので、お困りの際はご予約店舗に事前にご相談ください。'
  ],
  [
    'question' => '予約の変更やキャンセルは可能ですか？',
    'answer' => 'ご予約の変更やキャンセル規定は、各店舗によって異なります。ご予約いただいた店舗へ直接お問い合わせください。店舗の連絡先は店舗一覧ページからご確認いただけます。'
  ],
  [
    'question' => '店舗一覧にある、「登録店舗」「認定店舗」とはなんですか？',
    'answer' => '登録店舗は、このサイトに登録されている写真館のことになります。認定店舗は、登録店舗の中でえがお写真館にて技術講習を受講した写真館になります。'
  ],
  [
    'question' => '店舗によって撮影プランが違うのですか？',
    'answer' => '「撮影」「ヘアメイク」「データのお渡し」は各店舗で共通となっております。価格やその他のオプションは店舗によって異なりますので、各店舗にお問い合わせください。'
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