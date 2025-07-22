<?php
/**
 * Reusable Thoughts Section Component
 * 
 * @param array $args {
 *     Optional. Array of arguments.
 *     @type string $wrapper_class   Custom wrapper class for styling
 *     @type string $wrapper_width   Custom width (e.g., 'm.fs(448, 0.7)')
 *     @type string $label_text      Label text
 *     @type string $title_text      Title text (can include <br>)
 *     @type string $content_text    Content text
 * }
 */

// デフォルト値を設定
$wrapper_class = $args['wrapper_class'] ?? 'thoughts-section__default-wrapper';
$wrapper_width = $args['wrapper_width'] ?? 'm.fs(448, 0.7)';
$label_text = $args['label_text'] ?? 'Our Thoughts';
$title_text = $args['title_text'] ?? 'ロクナナハチ撮影<br>への想い';
$content_text = $args['content_text'] ?? '';

// カスタムCSSを動的に生成（ユニークなクラス名を使用）
$unique_id = uniqid('thoughts_');
$custom_css = "
<style>
.{$unique_id} {
    width: {$wrapper_width};
    padding: " . (function_exists('m_fs') ? 'm.fs(32, 0.5)' : '32px') . ";
    display: flex;
    flex-direction: column;
    gap: " . (function_exists('m_fs') ? 'm.fs(24, 0.5)' : '24px') . ";
}
</style>";

echo $custom_css;
?>

<div class="<?php echo esc_attr($wrapper_class); ?> <?php echo esc_attr($unique_id); ?>">
  <div class="thoughts-section__label">
    <?php get_template_part('template-parts/components/thoughts-label', null, [
              'text' => $label_text
          ]); ?>
  </div>

  <div class="thoughts-section__title">
    <?php get_template_part('template-parts/components/thoughts-title', null, [
              'title' => $title_text
          ]); ?>
  </div>

  <div class="thoughts-section__content">
    <?php get_template_part('template-parts/components/thoughts-text', null, [
              'text' => $content_text
          ]); ?>
  </div>
</div>