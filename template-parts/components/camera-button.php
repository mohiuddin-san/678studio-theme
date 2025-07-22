<?php
/**
 * Camera Button Component
 * 
 * @param array $args {
 *     @type string $text Button text (default: '写真館を探す')
 *     @type string $url  Button URL (default: '#')
 *     @type string $class Additional CSS classes
 * }
 */

$args = wp_parse_args($args ?? [], [
    'text' => '写真館を探す',
    'url' => '#',
    'class' => '',
    'bg_color' => 'white', // 'white', 'blue', 'reservation', 'send', 'detail', 'contact'
    'icon' => 'cam' // 'cam', 'people', 'mailsend', 'home', 'none'
]);

$classes = ['camera-button'];
if (!empty($args['class'])) {
    $classes[] = $args['class'];
}

// 背景色クラスを追加
switch ($args['bg_color']) {
    case 'blue':
        $classes[] = 'camera-button--blue-bg';
        break;
    case 'reservation':
        $classes[] = 'camera-button--reservation';
        break;
    case 'send':
        $classes[] = 'camera-button--send';
        break;
    case 'detail':
        $classes[] = 'camera-button--detail';
        break;
    case 'detail-card':
        $classes[] = 'camera-button--detail-card';
        break;
    case 'contact':
        $classes[] = 'camera-button--contact';
        break;
}

// アイコンのパスを決定
$icon_path = '';
switch ($args['icon']) {
    case 'people':
        $icon_path = 'people.svg';
        break;
    case 'mailsend':
        $icon_path = 'mailsend.svg';
        break;
    case 'home':
        $icon_path = 'home.svg';
        break;
    case 'cam':
        $icon_path = 'cam.svg';
        break;
    case 'none':
        $icon_path = '';
        break;
    default:
        $icon_path = 'cam.svg';
}
?>

<a href="<?php echo esc_url($args['url']); ?>" class="<?php echo esc_attr(implode(' ', $classes)); ?>">
  <div class="camera-button__content">
    <span class="camera-button__text"><?php echo esc_html($args['text']); ?></span>
    <?php if (!empty($icon_path)): ?>
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/<?php echo esc_attr($icon_path); ?>" alt="" class="camera-button__icon">
    <?php endif; ?>
  </div>
</a>