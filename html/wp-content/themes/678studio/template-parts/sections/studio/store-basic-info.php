<?php
/**
 * Store Basic Information Section
 * Display store details with vertical title and 2-column layout
 *
 * Phase 2: Fully migrated to new studio data helpers
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

// Don't render if no shop data
if (empty($shop)) {
    return;
}
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
                    <h3 class="store-basic-info-section__name">
                        <?php if (!empty($shop['store_name'])): ?>
                            <div class="store-basic-info-section__main-name"><?php echo esc_html($shop['store_name']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($shop['branch_name'])): ?>
                            <div class="store-basic-info-section__branch-name"><?php echo esc_html($shop['branch_name']); ?></div>
                        <?php endif; ?>
                    </h3>
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
                    // Website URL (already included in shop data from new helper functions)
                    $website_url = $shop['website_url'] ?? '';

                    // Fallback for shop ID 122 if still needed
                    if (empty($website_url) && !empty($shop['id']) && $shop['id'] == 122) {
                        $website_url = 'https://www.1122.co.jp/studio/ichino/index.html';
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="58" height="35" viewBox="0 0 58 35" fill="none" class="store-basic-info-section__wave-1">
                      <path class="wave-line-1" d="M0 1C5.70965 1 5.70965 8.99 11.4128 8.99C17.116 8.99 17.1225 1 22.8321 1C28.5418 1 28.5418 8.99 34.2514 8.99C39.9611 8.99 39.9611 1 45.6707 1C51.3803 1 51.3803 8.99 57.09 8.99" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      <path class="wave-line-2" d="M0 14C5.70965 14 5.70965 21.99 11.4128 21.99C17.116 21.99 17.1225 14 22.8321 14C28.5418 14 28.5418 21.99 34.2514 21.99C39.9611 21.99 39.9611 14 45.6707 14C51.3803 14 51.3803 21.99 57.09 21.99" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      <path class="wave-line-3" d="M0 26C5.70965 26 5.70965 33.99 11.4128 33.99C17.116 33.99 17.1225 26 22.8321 26C28.5418 26 28.5418 33.99 34.2514 33.99C39.9611 33.99 39.9611 26 45.6707 26C51.3803 26 51.3803 33.99 57.09 33.99" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                    </svg>
                    <svg width="298" height="38" viewBox="0 0 298 38" fill="none" xmlns="http://www.w3.org/2000/svg" class="store-basic-info-section__wave-2">
                      <g clip-path="url(#clip0_14_1137)">
                        <path class="wave-2-line-1" d="M0 27.5C8.81 27.5 8.81 35.49 17.61 35.49C26.41 35.49 26.42 27.5 35.23 27.5C44.04 27.5 44.04 35.49 52.85 35.49C61.66 35.49 61.66 27.5 70.47 27.5C79.28 27.5 79.28 35.49 88.09 35.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip1_14_1137)">
                        <path class="wave-2-line-2" d="M70 27.5C78.81 27.5 78.81 35.49 87.61 35.49C96.41 35.49 96.42 27.5 105.23 27.5C114.04 27.5 114.04 35.49 122.85 35.49C131.66 35.49 131.66 27.5 140.47 27.5C149.28 27.5 149.28 35.49 158.09 35.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip2_14_1137)">
                        <path class="wave-2-line-3" d="M140 27.5C148.81 27.5 148.81 35.49 157.61 35.49C166.41 35.49 166.42 27.5 175.23 27.5C184.04 27.5 184.04 35.49 192.85 35.49C201.66 35.49 201.66 27.5 210.47 27.5C219.28 27.5 219.28 35.49 228.09 35.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip3_14_1137)">
                        <path class="wave-2-line-4" d="M210 27.5C218.81 27.5 218.81 35.49 227.61 35.49C236.41 35.49 236.42 27.5 245.23 27.5C254.04 27.5 254.04 35.49 262.85 35.49C271.66 35.49 271.66 27.5 280.47 27.5C289.28 27.5 289.28 35.49 298.09 35.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip4_14_1137)">
                        <path class="wave-2-line-5" d="M0 15.5C8.81 15.5 8.81 23.49 17.61 23.49C26.41 23.49 26.42 15.5 35.23 15.5C44.04 15.5 44.04 23.49 52.85 23.49C61.66 23.49 61.66 15.5 70.47 15.5C79.28 15.5 79.28 23.49 88.09 23.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip5_14_1137)">
                        <path class="wave-2-line-6" d="M70 15.5C78.81 15.5 78.81 23.49 87.61 23.49C96.41 23.49 96.42 15.5 105.23 15.5C114.04 15.5 114.04 23.49 122.85 23.49C131.66 23.49 131.66 15.5 140.47 15.5C149.28 15.5 149.28 23.49 158.09 23.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip6_14_1137)">
                        <path class="wave-2-line-7" d="M140 15.5C148.81 15.5 148.81 23.49 157.61 23.49C166.41 23.49 166.42 15.5 175.23 15.5C184.04 15.5 184.04 23.49 192.85 23.49C201.66 23.49 201.66 15.5 210.47 15.5C219.28 15.5 219.28 23.49 228.09 23.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip7_14_1137)">
                        <path class="wave-2-line-8" d="M210 15.5C218.81 15.5 218.81 23.49 227.61 23.49C236.41 23.49 236.42 15.5 245.23 15.5C254.04 15.5 254.04 23.49 262.85 23.49C271.66 23.49 271.66 15.5 280.47 15.5C289.28 15.5 289.28 23.49 298.09 23.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip8_14_1137)">
                        <path class="wave-2-line-9" d="M0 2.5C8.81 2.5 8.81 10.49 17.61 10.49C26.41 10.49 26.42 2.5 35.23 2.5C44.04 2.5 44.04 10.49 52.85 10.49C61.66 10.49 61.66 2.5 70.47 2.5C79.28 2.5 79.28 10.49 88.09 10.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip9_14_1137)">
                        <path class="wave-2-line-10" d="M70 2.5C78.81 2.5 78.81 10.49 87.61 10.49C96.41 10.49 96.42 2.5 105.23 2.5C114.04 2.5 114.04 10.49 122.85 10.49C131.66 10.49 131.66 2.5 140.47 2.5C149.28 2.5 149.28 10.49 158.09 10.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip10_14_1137)">
                        <path class="wave-2-line-11" d="M140 2.5C148.81 2.5 148.81 10.49 157.61 10.49C166.41 10.49 166.42 2.5 175.23 2.5C184.04 2.5 184.04 10.49 192.85 10.49C201.66 10.49 201.66 2.5 210.47 2.5C219.28 2.5 219.28 10.49 228.09 10.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <g clip-path="url(#clip11_14_1137)">
                        <path class="wave-2-line-12" d="M210 2.5C218.81 2.5 218.81 10.49 227.61 10.49C236.41 10.49 236.42 2.5 245.23 2.5C254.04 2.5 254.04 10.49 262.85 10.49C271.66 10.49 271.66 2.5 280.47 2.5C289.28 2.5 289.28 10.49 298.09 10.49" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                      </g>
                      <defs>
                        <clipPath id="clip0_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(0 25)"/>
                        </clipPath>
                        <clipPath id="clip1_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(70 25)"/>
                        </clipPath>
                        <clipPath id="clip2_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(140 25)"/>
                        </clipPath>
                        <clipPath id="clip3_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(210 25)"/>
                        </clipPath>
                        <clipPath id="clip4_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(0 13)"/>
                        </clipPath>
                        <clipPath id="clip5_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(70 13)"/>
                        </clipPath>
                        <clipPath id="clip6_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(140 13)"/>
                        </clipPath>
                        <clipPath id="clip7_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(210 13)"/>
                        </clipPath>
                        <clipPath id="clip8_14_1137">
                          <rect width="88" height="13" fill="white"/>
                        </clipPath>
                        <clipPath id="clip9_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(70)"/>
                        </clipPath>
                        <clipPath id="clip10_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(140)"/>
                        </clipPath>
                        <clipPath id="clip11_14_1137">
                          <rect width="88" height="13" fill="white" transform="translate(210)"/>
                        </clipPath>
                      </defs>
                    </svg>
                    <svg width="71" height="32" viewBox="0 0 71 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="store-basic-info-section__logo-illust">
                      <!-- 左の顔（輪郭） -->
                      <path class="face-left-outline" d="M11.2989 12.0823C4.7596 11.2569 -0.743132 16.7596 0.082278 23.2989C0.645349 27.7587 4.24132 31.3547 8.7011 31.9177C15.2404 32.7431 20.7431 27.2404 19.9177 20.7011C19.3547 16.2413 15.7587 12.6453 11.2989 12.0823ZM10.9918 28.936C8.74589 29.2495 6.66636 28.4817 5.1947 27.0868C4.82999 26.7413 4.95796 26.1143 5.44424 25.9799C5.93053 25.8455 6.47441 25.6408 7.02468 25.3336C8.41956 24.5658 9.43693 23.4525 10.0256 22.6846C10.3007 22.3263 10.0512 21.8144 9.60329 21.8144H3.86381C3.38392 21.8144 3.00641 21.3857 3.08319 20.9122C3.60147 17.5594 6.5064 14.9808 10 14.9808C14.1335 14.9808 17.4415 18.5768 16.9616 22.8062C16.6033 25.9287 14.1143 28.4881 10.9982 28.9232L10.9918 28.936Z" fill="#F39556"/>

                      <!-- 右の顔（輪郭） -->
                      <path class="face-right-outline" d="M51.0823 20.7011C50.2569 27.2404 55.7596 32.7431 62.2989 31.9177C66.7587 31.3547 70.3547 27.7587 70.9177 23.2989C71.7431 16.7596 66.2404 11.2569 59.7011 12.0823C55.2413 12.6453 51.6453 16.2413 51.0823 20.7011ZM54.0384 22.819C53.5585 18.5896 56.8665 14.9936 61 14.9936C64.5 14.9936 67.3985 17.5722 67.9168 20.925C67.9872 21.3985 67.6097 21.8272 67.1362 21.8272H61.3967C60.9488 21.8272 60.6993 22.3391 60.9744 22.6974C61.5631 23.4653 62.5804 24.5722 63.9753 25.3464C64.5256 25.6536 65.0631 25.8583 65.5558 25.9927C66.0484 26.1271 66.17 26.7477 65.8053 27.0996C64.3336 28.4945 62.2541 29.2623 60.0082 28.9488C56.8921 28.5137 54.4031 25.9543 54.0448 22.8318L54.0384 22.819Z" fill="#F39556"/>

                      <!-- 真ん中の顔（輪郭） -->
                      <path class="face-center-outline" d="M35 2.99424C38.8644 2.99424 42.0058 6.13564 42.0058 10C42.0058 13.8644 38.8644 17.0058 35 17.0058C31.1356 17.0058 27.9942 13.8644 27.9942 10C27.9942 6.13564 31.1356 2.99424 35 2.99424ZM35 0C29.4786 0 25 4.47857 25 10C25 15.5214 29.4786 20 35 20C40.5214 20 45 15.5214 45 10C45 4.47857 40.5214 0 35 0Z" fill="#F39556"/>

                      <!-- 真ん中の顔の口 -->
                      <path class="mouth-center" d="M38.0582 9.80151C39.1074 9.80151 39.8496 10.9148 39.4082 11.8936C38.6404 13.6019 36.9577 14.7919 35 14.7919C33.0422 14.7919 31.3659 13.6083 30.5918 11.8936C30.1503 10.9148 30.8925 9.80151 31.9417 9.80151H38.0518H38.0582Z" fill="#F39556"/>
                    </svg>
                </div>
            </div>
            </div>
        </div>
    </div>
</section>

<!-- Mobile Layout -->
<section class="store-basic-info-section-mobile">
    <div class="store-basic-info-section-mobile__container">
        <!-- Vertical Title -->
        <div class="store-basic-info-section-mobile__vertical-title">
            <span class="store-basic-info-section-mobile__circle">●</span>
            <h2 class="store-basic-info-section-mobile__title">基本情報</h2>
            <span class="store-basic-info-section-mobile__title-en">Information</span>
        </div>

        <!-- Store Name (Top) -->
        <div class="store-basic-info-section-mobile__store-name">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-logo-icon.svg" alt="" class="store-basic-info-section-mobile__name-icon">
            <h3 class="store-basic-info-section-mobile__name">
                <?php if (!empty($shop['store_name'])): ?>
                    <div class="store-basic-info-section-mobile__main-name"><?php echo esc_html($shop['store_name']); ?></div>
                <?php endif; ?>
                <?php if (!empty($shop['branch_name'])): ?>
                    <div class="store-basic-info-section-mobile__branch-name"><?php echo esc_html($shop['branch_name']); ?></div>
                <?php endif; ?>
            </h3>
        </div>

        <!-- Store Description -->
        <div class="store-basic-info-section-mobile__description">
            <?php
            $description = $shop['store_introduction'] ?? '';
            if (!empty($description)) {
                echo wpautop(wp_kses_post($description));
            }
            ?>
        </div>

        <!-- Store Image Area (Full Width Wrapper) -->
        <div class="store-basic-info-section-mobile__image-area">
            <div class="store-basic-info-section-mobile__image-container">
                <div class="store-basic-info-section-mobile__image">
                    <?php
                    // Same image logic as desktop
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
                    <img src="<?php echo esc_url($store_image); ?>" alt="店舗内観" class="store-basic-info-section-mobile__store-img">
                </div>
            </div>
            <!-- Decorative Elements (Independent) -->
            <div class="store-basic-info-section-mobile__decorations">
                <svg xmlns="http://www.w3.org/2000/svg" width="58" height="35" viewBox="0 0 58 35" fill="none" class="store-basic-info-section-mobile__wave-1">
                  <path class="wave-line-1" d="M0 1C5.70965 1 5.70965 8.99 11.4128 8.99C17.116 8.99 17.1225 1 22.8321 1C28.5418 1 28.5418 8.99 34.2514 8.99C39.9611 8.99 39.9611 1 45.6707 1C51.3803 1 51.3803 8.99 57.09 8.99" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                  <path class="wave-line-2" d="M0 14C5.70965 14 5.70965 21.99 11.4128 21.99C17.116 21.99 17.1225 14 22.8321 14C28.5418 14 28.5418 21.99 34.2514 21.99C39.9611 21.99 39.9611 14 45.6707 14C51.3803 14 51.3803 21.99 57.09 21.99" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                  <path class="wave-line-3" d="M0 26C5.70965 26 5.70965 33.99 11.4128 33.99C17.116 33.99 17.1225 26 22.8321 26C28.5418 26 28.5418 33.99 34.2514 33.99C39.9611 33.99 39.9611 26 45.6707 26C51.3803 26 51.3803 33.99 57.09 33.99" stroke="#F39556" stroke-width="2" stroke-miterlimit="10"/>
                </svg>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-wave-ilst-2-sp.svg" alt="" class="store-basic-info-section-mobile__wave-2">
            </div>
        </div>

        <!-- Store Details List (Bottom) -->
        <div class="store-basic-info-section-mobile__details">
            <div class="store-basic-info-section-mobile__details-list">
                <!-- Address -->
                <?php if (!empty($shop['address'])): ?>
                <div class="store-basic-info-section-mobile__detail-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-map-icon.svg" alt="" class="store-basic-info-section-mobile__detail-icon">
                    <div class="store-basic-info-section-mobile__detail-text">
                        <span><?php echo esc_html($shop['address']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Phone -->
                <?php if (!empty($shop['phone'])): ?>
                <div class="store-basic-info-section-mobile__detail-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-tell-icon.svg" alt="" class="store-basic-info-section-mobile__detail-icon">
                    <div class="store-basic-info-section-mobile__detail-text">
                        <a href="tel:<?php echo esc_attr($shop['phone']); ?>"><?php echo esc_html($shop['phone']); ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Nearest Station -->
                <?php if (!empty($shop['nearest_station'])): ?>
                <div class="store-basic-info-section-mobile__detail-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-train.svg" alt="" class="store-basic-info-section-mobile__detail-icon">
                    <div class="store-basic-info-section-mobile__detail-text">
                        <span><?php echo esc_html($shop['nearest_station']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Business Hours -->
                <?php if (!empty($shop['business_hours'])): ?>
                <div class="store-basic-info-section-mobile__detail-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-clock.svg" alt="" class="store-basic-info-section-mobile__detail-icon">
                    <div class="store-basic-info-section-mobile__detail-text">
                        <span><?php echo esc_html($shop['business_hours']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Holidays -->
                <?php if (!empty($shop['holidays'])): ?>
                <div class="store-basic-info-section-mobile__detail-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-holiday-icon.svg" alt="" class="store-basic-info-section-mobile__detail-icon">
                    <div class="store-basic-info-section-mobile__detail-text">
                        <span><?php echo esc_html($shop['holidays']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Website -->
                <?php
                $website_url = $shop['website_url'] ?? '';
                if (empty($website_url) && !empty($shop['id']) && $shop['id'] == 122) {
                    $website_url = 'https://www.1122.co.jp/studio/ichino/index.html';
                }

                if (!empty($website_url)):
                ?>
                <div class="store-basic-info-section-mobile__detail-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/detail-link-icon.svg" alt="" class="store-basic-info-section-mobile__detail-icon">
                    <div class="store-basic-info-section-mobile__detail-text">
                        <a href="<?php echo esc_url($website_url); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html($shop['name'] ?? ''); ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>