<?php
/**
 * Studio Info Header Component
 * Display store name, rating and certification status above the slider
 *
 * Phase 2: Migrated to use new studio data helpers
 */

// Get shop_id from various sources
$shop_id = 0;
if (isset($args['shop']['id'])) {
    $shop_id = $args['shop']['id'];
} elseif (isset($_GET['shop_id'])) {
    $shop_id = intval($_GET['shop_id']);
}

// Use new helper functions to get data
if ($shop_id > 0) {
    $shop_name = get_studio_shop_field($shop_id, 'store_name');
    $is_certified = (bool)get_studio_shop_field($shop_id, 'is_certified_store');
} else {
    // Fallback to existing data
    $shop = $args['shop'] ?? array();
    $shop_name = $shop['name'] ?? '';
    $is_certified = !empty($shop['is_certified_store']);
}

// Ensure we have a shop name
if (empty($shop_name) && isset($args['shop']['name'])) {
    $shop_name = $args['shop']['name'];
}
?>

<section class="studio-info-header">
    <div class="studio-info-header__container">
        <div class="studio-info-header__content">
            <!-- Store Name -->
            <h1 class="studio-info-header__name">
                <?php echo esc_html($shop_name); ?>
            </h1>

            <!-- Certification Status -->
            <div class="studio-info-header__status">
                <?php if ($is_certified): ?>
                <span class="studio-info-header__badge">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M7.81893 1.90616C8.8333 0.739607 10.3302 0 12 0C13.6697 0 15.1666 0.739535 16.1809 1.90599C17.7232 1.79823 19.3049 2.33373 20.4857 3.51455C21.6665 4.69536 22.202 6.27703 22.0943 7.81931C23.2606 8.83367 24 10.3304 24 12C24 13.6699 23.2603 15.1669 22.0936 16.1813C22.2011 17.7233 21.6656 19.3046 20.485 20.4852C19.3044 21.6659 17.7231 22.2014 16.181 22.0939C15.1667 23.2604 13.6697 24 12 24C10.3303 24 8.33348 23.2605 7.81912 22.0941C6.27682 22.2018 4.69513 21.6663 3.51431 20.4855C2.33349 19.3047 1.79798 17.723 1.90574 16.1807C0.739428 15.1663 0 13.6696 0 12C0 10.3304 0.739503 8.83351 1.90591 7.81915C1.79828 6.27698 2.33379 4.69547 3.51451 3.51476C4.69523 2.33403 6.27676 1.79852 7.81893 1.90616ZM16.4434 9.7673C16.7398 9.35245 16.6437 8.77595 16.2288 8.47963C15.814 8.18331 15.2375 8.2794 14.9412 8.69424L10.9591 14.2691L8.96041 12.2704C8.59992 11.9099 8.01546 11.9099 7.65498 12.2704C7.29449 12.6308 7.29449 13.2153 7.65498 13.5758L10.4242 16.345C10.6161 16.5369 10.8826 16.6346 11.1531 16.6122C11.4235 16.5899 11.6703 16.4496 11.8281 16.2288L16.4434 9.7673Z"
                            fill="currentColor" />
                    <span class="studio-info-header__badge-text">認定店</span>
                </span>
                ロクナナハチ撮影認定店舗
                <?php else: ?>
                ロクナナハチ登録店舗
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>