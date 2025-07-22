<?php
/**
 * Breadcrumb Component
 * 
 * 使用方法:
 * <?php get_template_part('template-parts/components/breadcrumb', null, [
 *     'items' => [
 *         ['text' => 'TOP', 'url' => home_url()],
 *         ['text' => 'ページタイトル', 'url' => ''] // URLが空の場合は現在のページ
 *     ]
 * ]); ?>
 * 
 * @param array $args {
 *     @type array $items パンくずリストのアイテム配列
 *                        各アイテム: ['text' => 'テキスト', 'url' => 'URL']
 *                        最後のアイテムのURLは空にする（現在のページ）
 * }
 */

$items = $args['items'] ?? [
    ['text' => 'TOP', 'url' => home_url()],
    ['text' => get_the_title(), 'url' => '']
];
?>

<nav class="breadcrumb" aria-label="パンくずナビゲーション">
    <div class="breadcrumb__container">
        <ul class="breadcrumb__list">
            <?php foreach ($items as $index => $item): ?>
                <li class="breadcrumb__item">
                    <?php if (!empty($item['url']) && $index < count($items) - 1): ?>
                        <a href="<?php echo esc_url($item['url']); ?>" class="breadcrumb__link">
                            <?php echo esc_html($item['text']); ?>
                        </a>
                    <?php else: ?>
                        <span class="breadcrumb__current">
                            <?php echo esc_html($item['text']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($index < count($items) - 1): ?>
                        <span class="breadcrumb__separator" aria-hidden="true">/</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>