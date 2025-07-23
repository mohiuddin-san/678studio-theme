<?php
/**
 * Thoughts Text Component
 * 
 * @param array $args {
 *     Optional. Array of arguments.
 *     @type string $text Text content.
 * }
 */

$text = $args['text'] ?? '';
?>

<div class="thoughts-text">
    <?php echo wp_kses_post($text); ?>
</div>