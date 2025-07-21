<?php
/**
 * Detail Button Component
 * 詳細表示用のボタンコンポーネント
 * 
 * @param array $args {
 *     @type string $text      Button text (default: '詳しく見る')
 *     @type string $url       Button URL (default: '#')
 *     @type string $variant   Button variant (default: 'detail')
 *     @type string $class     Additional CSS classes
 *     @type string $icon      Icon type ('none', 'arrow', 'plus') (default: 'none')
 * }
 */

$args = wp_parse_args($args ?? [], [
    'text' => '詳しく見る',
    'url' => '#',
    'variant' => 'detail',
    'class' => '',
    'icon' => 'none'
]);

$classes = ['detail-button'];
if (!empty($args['class'])) {
    $classes[] = $args['class'];
}

// バリエーションクラスを追加
$classes[] = 'detail-button--' . $args['variant'];

// アイコンのパスを決定
$icon_path = '';
switch ($args['icon']) {
    case 'cam':
        $icon_path = 'cam-black.svg';
        break;
    case 'arrow':
        $icon_path = 'arrow.svg';
        break;
    case 'plus':
        $icon_path = 'plus.svg';
        break;
    case 'none':
    default:
        $icon_path = '';
        break;
}
?>

<a href="<?php echo esc_url($args['url']); ?>" class="<?php echo esc_attr(implode(' ', $classes)); ?>">
  <div class="detail-button__content">
    <span class="detail-button__text"><?php echo esc_html($args['text']); ?></span>
    <?php if (!empty($icon_path)): ?>
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/<?php echo esc_attr($icon_path); ?>" alt=""
      class="detail-button__icon">
    <?php endif; ?>
  </div>
</a>