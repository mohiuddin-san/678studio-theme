<?php
/**
 * Thoughts Label Component
 * 
 * @param array $args {
 *     Optional. Array of arguments.
 *     @type string $text Label text content. Default 'Our Thoughts'.
 * }
 */

$text = $args['text'] ?? 'Our Thoughts';
?>

<div class="thoughts-label">
  <p class="thoughts-label__text"><?php echo esc_html($text); ?></p>
</div>