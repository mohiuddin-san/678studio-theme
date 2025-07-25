<?php
/**
 * About Link Section Component
 * 
 * 使用方法:
 * <?php get_template_part('template-parts/components/about-link', null, [
 *     'buttons' => [
 *         ['text' => '利用シーン', 'url' => '#'],
 *         ['text' => '撮影プラン', 'url' => '#'],
 *         ['text' => '撮影の流れ', 'url' => '#']
 *     ],
 *     'description' => 'ここに説明文を入力します。'
 * ]); ?>
*
* @param array $args {
* @type array $buttons ボタンの配列
* @type string $title セクションタイトル
* @type string $description 説明文
* }
*/

$buttons = $args['buttons'] ?? [
['text' => '利用シーン', 'url' => '#'],
['text' => '撮影プラン', 'url' => '#'],
['text' => '撮影の流れ', 'url' => '#']
];

$title = $args['title'] ?? '';
$description = $args['description'] ??
'ロクナナハチ撮影は、60代・70代・80代の方々の美しさと品格を、<br class="pc-only">プロの技術で最大限に引き出す撮影サービスです。<br
  class="pc-only">記念撮影、家族写真、遺影撮影まで、人生の大切な瞬間をあなたらしい自然な美しさで残します。';
?>

<section class="about-link">
  <div class="about-link__container">

    <?php if (!empty($title)): ?>
    <div class="about-link__title-area">
      <h2 class="about-link__title"><?php echo esc_html($title); ?></h2>
    </div>

    <?php endif; ?>

    <!-- Button Navigation -->
    <nav class="about-link__nav">
      <?php foreach ($buttons as $button): ?>
      <?php get_template_part('template-parts/components/camera-button', null, [
                    'text' => $button['text'],
                    'url' => $button['url'],
                    'bg_color' => 'detail',
                    'icon' => 'none'
                ]); ?>
      <?php endforeach; ?>
    </nav>

    <!-- Description -->
    <div class="about-link__description">
      <p class="about-link__text">
        <?php echo wp_kses($description, ['br' => ['class' => []]]); ?>
      </p>
    </div>

  </div>
</section>