<?php
/**
 * Title Section - Reusable Component with CSS Classes & fs() Function Support
 * 
 * 使用方法:
 * 
 * 1. プリセットバリアントを使用:
 * <?php get_template_part('template-parts/components/title-section', null, [
 *     'variant' => 'about',  // default, about, small, large
 *     'label_text' => 'What is 678?',
 *     'title_text' => 'ロクナナハチ撮影とは？'
 * ]); ?>
 * 
 * 2. fs関数でカスタム値を指定（推奨）:
 * <?php get_template_part('template-parts/components/title-section', null, [
 *     'variant' => 'default',
 *     'width' => 500,        // m.fs(500, 0.8) になる
 *     'width_scale' => 0.8,
 *     'padding' => 40,       // m.fs(40, 0.6) になる
 *     'padding_scale' => 0.6,
 *     'gap' => 30,          // m.fs(30, 0.7) になる
 *     'gap_scale' => 0.7,
 *     'label_text' => 'Custom Section',
 *     'title_text' => 'カスタムタイトル'
 * ]); ?>
 * 
 * 3. 部分的にカスタマイズ:
 * <?php get_template_part('template-parts/components/title-section', null, [
 *     'variant' => 'small',  // smallの基本設定
 *     'width' => 400,        // 幅だけカスタム（m.fs(400, 0.6)になる）
 *     'label_text' => 'Special',
 *     'title_text' => '特別なセクション'
 * ]); ?>
 * 
 * @param array $args {
 *     @type string $variant        CSS variant class (default: 'default')
 *     @type string $label_text     Label text
 *     @type string $title_text     Title text
 *     @type string $content_text   Content text
 *     @type bool   $show_label     Show label section (default: true)
 *     @type int    $width          Custom width in px (fs関数の第1引数)
 *     @type float  $width_scale    Custom width scale (fs関数の第2引数)
 *     @type int    $padding        Custom padding in px (fs関数の第1引数)
 *     @type float  $padding_scale  Custom padding scale (fs関数の第2引数)
 *     @type int    $gap            Custom gap in px (fs関数の第1引数)
 *     @type float  $gap_scale      Custom gap scale (fs関数の第2引数)
 * }
 */

$variant = $args['variant'] ?? 'default';
$label_text = $args['label_text'] ?? 'Our Thoughts';
$title_text = $args['title_text'] ?? 'タイトル';
$content_text = $args['content_text'] ?? '';
$custom_class = $args['custom_class'] ?? '';
$show_label = $args['show_label'] ?? true;

// インラインスタイル用のCSS生成
$inline_styles = [];

// fs関数の値を直接計算してCSSに出力
if (isset($args['width']) && isset($args['width_scale'])) {
    $width = intval($args['width']);
    $width_scale = floatval($args['width_scale']);
    $min_width = $width * $width_scale;
    $width_diff = $width - $min_width;
    $inline_styles[] = "width: calc({$min_width}px + ({$width_diff}) * (100vw - 768px) / (672))";
}

if (isset($args['padding']) && isset($args['padding_scale'])) {
    $padding = intval($args['padding']);
    $padding_scale = floatval($args['padding_scale']);
    $min_padding = $padding * $padding_scale;
    $padding_diff = $padding - $min_padding;
    $inline_styles[] = "padding: calc({$min_padding}px + ({$padding_diff}) * (100vw - 768px) / (672))";
}

if (isset($args['gap']) && isset($args['gap_scale'])) {
    $gap = intval($args['gap']);
    $gap_scale = floatval($args['gap_scale']);
    $min_gap = $gap * $gap_scale;
    $gap_diff = $gap - $min_gap;
    $inline_styles[] = "gap: calc({$min_gap}px + ({$gap_diff}) * (100vw - 768px) / (672))";
}

$style_attr = !empty($inline_styles) ? ' style="' . implode('; ', $inline_styles) . '"' : '';
$class_attr = 'title-section title-section--' . esc_attr($variant);
if (!empty($custom_class)) {
    $class_attr .= ' ' . esc_attr($custom_class);
}
?>

<div class="<?php echo $class_attr; ?>"<?php echo $style_attr; ?>>
  <?php if ($show_label): ?>
  <div class="title-section__label">
    <?php get_template_part('template-parts/components/thoughts-label', null, [
              'text' => $label_text
          ]); ?>
  </div>
  <?php endif; ?>

  <div class="title-section__title">
    <h2 class="title-section__title-text">
      <?php 
      // タイトルテキストを<br>で分割して各行に下線を追加
      $lines = explode('<br>', $title_text);
      foreach ($lines as $index => $line) {
        if ($index > 0) echo "\n      ";
        echo '<span class="title-section__title-line">' . trim($line) . '<img class="title-section__title-underline" src="' . get_template_directory_uri() . '/assets/images/underline.svg" alt=""></span>';
      }
      ?>
    </h2>
  </div>

  <?php if (!empty($content_text)): ?>
  <div class="title-section__content">
    <?php get_template_part('template-parts/components/thoughts-text', null, [
              'text' => $content_text
          ]); ?>
  </div>
  <?php endif; ?>
</div>