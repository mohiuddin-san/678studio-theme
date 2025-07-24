<?php
/**
 * Reusable Input Field Component
 */
$label = $args['label'] ?? '';
$type = $args['type'] ?? 'text';
$name = $args['name'] ?? '';
$placeholder = $args['placeholder'] ?? '';
$value = $args['value'] ?? '';
?>

<div class="input-field">
  <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label>
  <?php if ($type === 'textarea'): ?>
    <textarea name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" rows="4"></textarea>
  <?php else: ?>
    <input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" value="<?php echo esc_attr($value); ?>">
  <?php endif; ?>
</div>