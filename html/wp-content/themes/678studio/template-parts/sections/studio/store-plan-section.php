<?php
/**
 * Store Plan Section
 * Display photography plans with vertical title and plan content
 *
 * Uses new studio data helpers for plan information
 */

// Get shop_id from various sources
$shop_id = 0;
if (isset($args['shop']['id'])) {
    $shop_id = $args['shop']['id'];
} elseif (isset($_GET['shop_id'])) {
    $shop_id = intval($_GET['shop_id']);
}

// Use new helper functions to get data
$shop = array();
if ($shop_id > 0 && function_exists('get_studio_shop_data_simple')) {
    $shop_data = get_studio_shop_data_simple($shop_id);
    if (!isset($shop_data['error']) && !empty($shop_data['shop'])) {
        $shop = $shop_data['shop'];
    }
}

// Fallback to existing data if new system fails
if (empty($shop) && isset($args['shop'])) {
    $shop = $args['shop'];
}

// Don't render if no shop data or no plans
if (empty($shop) || empty($shop['photo_plans'])) {
    return;
}

// Get all plans (max 3)
$photo_plans = array_slice($shop['photo_plans'], 0, 3);
?>

<section class="store-plan-section">
    <div class="store-plan-section__container">
        <!-- Vertical Title -->
        <div class="store-plan-section__vertical-title">
            <span class="store-plan-section__circle">●</span>
            <h2 class="store-plan-section__title">撮影プラン</h2>
            <span class="store-plan-section__title-en">Plan</span>
        </div>

        <!-- Main Content Area -->
        <div class="store-plan-section__main<?php echo count($photo_plans) > 1 ? ' store-plan-section__main--multiple-plans' : ''; ?>">
            <!-- Top Block: Plan Title -->
            <div class="store-plan-section__header">
                <!-- Plan Title with Icon -->
                <div class="store-plan-section__store-name">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detaiil-plan-icon.svg" alt="" class="store-plan-section__name-icon">
                    <h3 class="store-plan-section__name">撮影プラン</h3>
                </div>
            </div>

            <!-- Multiple Plans -->
            <?php foreach ($photo_plans as $index => $plan): ?>
            <div class="store-plan-section__plan-item">
                <!-- Bottom Block: Plan Image + Plan Content -->
                <div class="store-plan-section__body">
                    <!-- Left: Plan Image -->
                    <div class="store-plan-section__visual">
                        <div class="store-plan-section__image-container">
                            <?php
                            // プラン画像を取得（ACFから）
                            $plan_image_url = $plan['plan_image'] ?? '';

                            // フォールバック画像
                            if (empty($plan_image_url)) {
                                $plan_image_url = get_template_directory_uri() . '/assets/images/plan-sample-woman.jpg';
                            }

                            $plan_name = $plan['plan_name'] ?? 'プラン' . ($index + 1);
                            ?>
                            <img src="<?php echo esc_url($plan_image_url); ?>" alt="<?php echo esc_attr($plan_name); ?>" class="store-plan-section__plan-img">
                        </div>

                        <!-- Decorative Elements (Only for first plan) -->
                        <?php if ($index === 0): ?>
                        <div class="store-plan-section__decorations">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-plan-top-img-illust.svg" alt="" class="store-plan-section__plan-illust">
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Plan Content -->
                    <div class="store-plan-section__content">
                        <!-- Plan Name -->
                        <h4 class="store-plan-section__plan-name"><?php echo esc_html($plan['plan_name'] ?? 'プラン' . ($index + 1)); ?></h4>

                        <!-- Plan Description -->
                        <div class="store-plan-section__description">
                            <?php
                            $description = $plan['plan_description'] ?? '';
                            if (!empty($description)) {
                                echo wpautop(wp_kses_post($description));
                            }
                            ?>
                        </div>

                        <!-- Plan Details Table -->
                        <table class="store-plan-section__details">
                            <tr>
                                <th class="store-plan-section__detail-label">目安時間</th>
                                <th class="store-plan-section__detail-label">料金</th>
                            </tr>
                            <tr>
                                <td class="store-plan-section__detail-value"><?php echo esc_html($plan['formatted_duration']); ?></td>
                                <td class="store-plan-section__detail-value store-plan-section__price"><?php echo esc_html($plan['formatted_price']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Mobile Layout -->
<section class="store-plan-section-mobile">
    <div class="store-plan-section-mobile__container">
        <!-- Vertical Title -->
        <div class="store-plan-section-mobile__vertical-title">
            <span class="store-plan-section-mobile__circle">●</span>
            <h2 class="store-plan-section-mobile__title">撮影プラン</h2>
            <span class="store-plan-section-mobile__title-en">Plan</span>
        </div>

        <!-- Plan Title with Icon -->
        <div class="store-plan-section-mobile__store-name">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detaiil-plan-icon.svg" alt="" class="store-plan-section-mobile__name-icon">
            <h3 class="store-plan-section-mobile__name">撮影プラン</h3>
        </div>

        <!-- Plans Loop -->
        <?php foreach ($photo_plans as $index => $plan): ?>
        <div class="store-plan-section-mobile__plan-item<?php echo ($index === count($photo_plans) - 1 && count($photo_plans) > 1) ? ' store-plan-section-mobile__plan-item--last' : ''; ?>">

            <!-- Plan Image (Center) with Decorations -->
            <div class="store-plan-section-mobile__image-container">
                <div class="store-plan-section-mobile__image">
                    <?php
                    // プラン画像を取得（ACFから）
                    $plan_image_url = $plan['plan_image'] ?? '';

                    // フォールバック画像
                    if (empty($plan_image_url)) {
                        $plan_image_url = get_template_directory_uri() . '/assets/images/plan-sample-woman.jpg';
                    }

                    $plan_name = $plan['plan_name'] ?? 'プラン' . ($index + 1);
                    ?>
                    <img src="<?php echo esc_url($plan_image_url); ?>" alt="<?php echo esc_attr($plan_name); ?>" class="store-plan-section-mobile__plan-img">
                </div>

                <!-- Decorative Elements outside image (only for first plan) -->
                <?php if ($index === 0): ?>
                <div class="store-plan-section-mobile__decorations">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-wave-ilst-1-sp.svg" alt="" class="store-plan-section-mobile__wave-1">
                </div>
                <?php endif; ?>
            </div>

            <!-- Plan Name (Bottom) -->
            <div class="store-plan-section-mobile__plan-name">
                <h4 class="store-plan-section-mobile__plan-title"><?php echo esc_html($plan['plan_name'] ?? 'プラン' . ($index + 1)); ?></h4>
            </div>

            <!-- Plan Description -->
            <div class="store-plan-section-mobile__description">
                <?php
                $description = $plan['plan_description'] ?? '';
                if (!empty($description)) {
                    echo wpautop(wp_kses_post($description));
                }
                ?>
            </div>

            <!-- Plan Details (Bottom) -->
            <div class="store-plan-section-mobile__details">
                <table class="store-plan-section-mobile__details-table">
                    <tr>
                        <th class="store-plan-section-mobile__detail-label">目安時間</th>
                        <th class="store-plan-section-mobile__detail-label">料金</th>
                    </tr>
                    <tr>
                        <td class="store-plan-section-mobile__detail-value"><?php echo esc_html($plan['formatted_duration']); ?></td>
                        <td class="store-plan-section-mobile__detail-value store-plan-section-mobile__price"><?php echo esc_html($plan['formatted_price']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>