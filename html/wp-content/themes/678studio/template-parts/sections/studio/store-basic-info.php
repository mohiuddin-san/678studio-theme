<?php
/**
 * Store Basic Information Section
 * Display store details with vertical title and 2-column layout
 */

// Get data passed from parent template
$shop = $args['shop'] ?? array();
?>

<section class="store-basic-info-section">
    <div class="store-basic-info-section__container">
        <!-- Vertical Title -->
        <div class="store-basic-info-section__vertical-title">
            <span class="store-basic-info-section__circle">●</span>
            <h2 class="store-basic-info-section__title">基本情報</h2>
            <span class="store-basic-info-section__title-en">Information</span>
        </div>

        <!-- Main Content Area -->
        <div class="store-basic-info-section__main">
            <!-- Top Block: Store Name + Description -->
            <div class="store-basic-info-section__header">
                <!-- Store Name with Icon -->
                <div class="store-basic-info-section__store-name">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-logo-icon.svg" alt="" class="store-basic-info-section__name-icon">
                    <h3 class="store-basic-info-section__name"><?php echo esc_html($shop['name'] ?? ''); ?></h3>
                </div>

                <!-- Store Description -->
                <div class="store-basic-info-section__description">
                    <?php
                    $description = $shop['store_introduction'] ?? '';
                    if (!empty($description)) {
                        // ACFから来るデータに既にHTMLタグが含まれている可能性があるため、wp_kses_postで許可されたHTMLタグのみを残す
                        echo wpautop(wp_kses_post($description));
                    }
                    ?>
                </div>
            </div>

            <!-- Bottom Block: Details Table + Image -->
            <div class="store-basic-info-section__body">
                <!-- Left: Store Details Table -->
                <div class="store-basic-info-section__details">
                    <!-- Address -->
                    <?php if (!empty($shop['address'])): ?>
                    <div class="store-basic-info-section__detail-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-map-icon.svg" alt="" class="store-basic-info-section__detail-icon">
                        <div class="store-basic-info-section__detail-text">
                            <span><?php echo esc_html($shop['address']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Phone -->
                    <?php if (!empty($shop['phone'])): ?>
                    <div class="store-basic-info-section__detail-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-tell-icon.svg" alt="" class="store-basic-info-section__detail-icon">
                        <div class="store-basic-info-section__detail-text">
                            <a href="tel:<?php echo esc_attr($shop['phone']); ?>"><?php echo esc_html($shop['phone']); ?></a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Nearest Station -->
                    <?php if (!empty($shop['nearest_station'])): ?>
                    <div class="store-basic-info-section__detail-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-train.svg" alt="" class="store-basic-info-section__detail-icon">
                        <div class="store-basic-info-section__detail-text">
                            <span><?php echo esc_html($shop['nearest_station']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Business Hours -->
                    <?php if (!empty($shop['business_hours'])): ?>
                    <div class="store-basic-info-section__detail-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-clock.svg" alt="" class="store-basic-info-section__detail-icon">
                        <div class="store-basic-info-section__detail-text">
                            <span><?php echo esc_html($shop['business_hours']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Holidays -->
                    <?php if (!empty($shop['holidays'])): ?>
                    <div class="store-basic-info-section__detail-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-holiday-icon.svg" alt="" class="store-basic-info-section__detail-icon">
                        <div class="store-basic-info-section__detail-text">
                            <span><?php echo esc_html($shop['holidays']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Website -->
                    <?php
                    // $shop配列からWebサイトURLを取得
                    $website_url = '';

                    // まず$shop配列のwebsite_urlをチェック
                    if (!empty($shop['website_url'])) {
                        $website_url = $shop['website_url'];
                    } else {
                        // フォールバック：店舗ID 122の場合の管理画面設定URL
                        if (!empty($shop['id']) && $shop['id'] == 122) {
                            $website_url = 'https://www.1122.co.jp/studio/ichino/index.html';
                        }
                    }

                    if (!empty($website_url)):
                    ?>
                    <div class="store-basic-info-section__detail-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-link-icon.svg" alt="" class="store-basic-info-section__detail-icon">
                        <div class="store-basic-info-section__detail-text">
                            <a href="<?php echo esc_url($website_url); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html($shop['name'] ?? ''); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Store Image with Decorations -->
                <div class="store-basic-info-section__visual">
                <!-- Store Image -->
                <div class="store-basic-info-section__image">
                    <?php
                    // ACFのメイン画像を取得（カスタム投稿タイプのメイン画像）
                    $main_image_id = get_post_thumbnail_id();
                    if ($main_image_id) {
                        $store_image = wp_get_attachment_image_url($main_image_id, 'full');
                    } elseif (!empty($shop['main_image'])) {
                        $store_image = $shop['main_image'];
                    } elseif (!empty($shop['image_urls']) && !empty($shop['image_urls'][0])) {
                        $store_image = $shop['image_urls'][0];
                    } else {
                        $store_image = get_template_directory_uri() . '/assets/images/cardpic-sample.jpg';
                    }
                    ?>
                    <img src="<?php echo esc_url($store_image); ?>" alt="店舗内観" class="store-basic-info-section__store-img">
                </div>

                <!-- Decorative Elements -->
                <div class="store-basic-info-section__decorations">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-wave-ilst-01.svg" alt="" class="store-basic-info-section__wave-1">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-wave-ilst-2.svg" alt="" class="store-basic-info-section__wave-2">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-logo-illust-3-icon.svg" alt="" class="store-basic-info-section__logo-illust">
                </div>
            </div>
            </div>
        </div>
    </div>
</section>