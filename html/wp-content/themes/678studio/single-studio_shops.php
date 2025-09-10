<?php
/**
 * Single Studio Shop Template
 * 
 * @package 678studio
 */

get_header(); ?>

<!-- Breadcrumb Section -->
<?php get_template_part('template-parts/components/breadcrumb', null, [
    'items' => [
        ['text' => 'TOP', 'url' => home_url()],
        ['text' => 'スタジオ一覧', 'url' => home_url('/stores/')],
        ['text' => get_the_title(), 'url' => '']
    ]
]); ?>

<main class="studio-shop-detail">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            
            <?php
            // ACFデータを取得
            $is_certified = get_field('is_certified_store') ?: false;
            $store_introduction = get_field('store_introduction') ?: '';
            $address = get_field('address') ?: '';
            $phone = get_field('phone') ?: '';
            $nearest_station = get_field('nearest_station') ?: '';
            $business_hours = get_field('business_hours') ?: '';
            $holidays = get_field('holidays') ?: '';
            $company_email = get_field('company_email') ?: '';
            $map_url = get_field('map_url') ?: '';
            $main_image = get_field('main_image');
            ?>
            
            <!-- Studio Header -->
            <section class="studio-header">
                <div class="studio-header__content">
                    <div class="studio-header__title-wrapper">
                        <h1 class="studio-header__title">
                            <?php the_title(); ?>
                            <?php if ($is_certified): ?>
                                <span class="certified-badge">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.81893 1.90616C8.8333 0.739607 10.3302 0 12 0C13.6697 0 15.1666 0.739535 16.1809 1.90599C17.7232 1.79823 19.3049 2.33373 20.4857 3.51455C21.6665 4.69536 22.202 6.27703 22.0943 7.81931C23.2606 8.83367 24 10.3304 24 12C24 13.6699 23.2603 15.1669 22.0936 16.1813C22.2011 17.7233 21.6656 19.3046 20.485 20.4852C19.3044 21.6659 17.7231 22.2014 16.181 22.0939C15.1667 23.2604 13.6697 24 12 24C10.3303 24 8.33348 23.2605 7.81912 22.0941C6.27682 22.2018 4.69513 21.6663 3.51431 20.4855C2.33349 19.3047 1.79798 17.723 1.90574 16.1807C0.739428 15.1663 0 13.6696 0 12C0 10.3304 0.739503 8.83351 1.90591 7.81915C1.79828 6.27698 2.33379 4.69547 3.51451 3.51476C4.69523 2.33403 6.27676 1.79852 7.81893 1.90616ZM16.4434 9.7673C16.7398 9.35245 16.6437 8.77595 16.2288 8.47963C15.814 8.18331 15.2375 8.2794 14.9412 8.69424L10.9591 14.2691L8.96041 12.2704C8.59992 11.9099 8.01546 11.9099 7.65498 12.2704C7.29449 12.6308 7.29449 13.2153 7.65498 13.5758L10.4242 16.345C10.6161 16.5369 10.8826 16.6346 11.1531 16.6122C11.4235 16.5899 11.6703 16.4496 11.8281 16.2288L16.4434 9.7673Z" fill="#3A89FF"/>
                                    </svg>
                                    <span class="certified-text">認定店</span>
                                </span>
                            <?php endif; ?>
                        </h1>
                        
                        <?php if ($store_introduction): ?>
                            <div class="studio-header__introduction">
                                <?php echo nl2br(esc_html($store_introduction)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($main_image): ?>
                        <div class="studio-header__image">
                            <img src="<?php echo esc_url($main_image['url']); ?>" alt="<?php echo esc_attr(get_the_title()); ?>のメイン画像">
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Studio Information -->
            <section class="studio-info">
                <h2 class="studio-info__title">店舗情報</h2>
                <div class="studio-info__grid">
                    <?php if ($address): ?>
                        <div class="studio-info__item">
                            <dt class="studio-info__label">住所</dt>
                            <dd class="studio-info__value"><?php echo esc_html($address); ?></dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($phone): ?>
                        <div class="studio-info__item">
                            <dt class="studio-info__label">電話番号</dt>
                            <dd class="studio-info__value">
                                <a href="tel:<?php echo esc_attr(str_replace(['-', ' '], '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                            </dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($nearest_station): ?>
                        <div class="studio-info__item">
                            <dt class="studio-info__label">最寄り駅</dt>
                            <dd class="studio-info__value"><?php echo esc_html($nearest_station); ?></dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($business_hours): ?>
                        <div class="studio-info__item">
                            <dt class="studio-info__label">営業時間</dt>
                            <dd class="studio-info__value"><?php echo esc_html($business_hours); ?></dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($holidays): ?>
                        <div class="studio-info__item">
                            <dt class="studio-info__label">定休日</dt>
                            <dd class="studio-info__value"><?php echo esc_html($holidays); ?></dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($company_email): ?>
                        <div class="studio-info__item">
                            <dt class="studio-info__label">メールアドレス</dt>
                            <dd class="studio-info__value">
                                <a href="mailto:<?php echo esc_attr($company_email); ?>"><?php echo esc_html($company_email); ?></a>
                            </dd>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <?php if ($is_certified): ?>
                <!-- Certified Store Plans -->
                <section class="studio-plans">
                    <h2 class="studio-plans__title">撮影プラン</h2>
                    <div class="studio-plans__grid">
                        <?php
                        // 撮影プランを取得
                        for ($i = 1; $i <= 3; $i++) {
                            $plan_name = get_field("plan{$i}_name");
                            $plan_price = get_field("plan{$i}_price");
                            $plan_duration = get_field("plan{$i}_duration");
                            $plan_description = get_field("plan{$i}_description");
                            
                            if (!empty($plan_name) || !empty($plan_price)):
                        ?>
                            <div class="studio-plan">
                                <div class="studio-plan__header">
                                    <?php if ($plan_name): ?>
                                        <h3 class="studio-plan__name"><?php echo esc_html($plan_name); ?></h3>
                                    <?php endif; ?>
                                    
                                    <div class="studio-plan__details">
                                        <?php if ($plan_price): ?>
                                            <span class="studio-plan__price">¥<?php echo number_format($plan_price); ?>円</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($plan_duration): ?>
                                            <span class="studio-plan__duration"><?php echo esc_html($plan_duration); ?>分</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($plan_description): ?>
                                    <div class="studio-plan__description">
                                        <?php echo nl2br(esc_html($plan_description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php
                            endif;
                        }
                        ?>
                    </div>
                </section>
                
                <!-- Gallery Section (for certified stores only) -->
                <section class="studio-gallery">
                    <h2 class="studio-gallery__title">ギャラリー</h2>
                    <div class="studio-gallery__grid" id="studio-gallery">
                        <?php
                        // ギャラリー画像を取得
                        $gallery_image_ids = get_post_meta(get_the_ID(), '_gallery_image_ids', true);
                        if (!empty($gallery_image_ids)) {
                            $image_ids = explode(',', $gallery_image_ids);
                            foreach ($image_ids as $image_id) {
                                $image_id = trim($image_id);
                                if ($image_id && is_numeric($image_id)) {
                                    $image_url = wp_get_attachment_url($image_id);
                                    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                                    if ($image_url):
                        ?>
                                        <div class="gallery-item">
                                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt ?: get_the_title()); ?>" loading="lazy">
                                        </div>
                        <?php
                                    endif;
                                }
                            }
                        }
                        ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <!-- Staff Section -->
            <section class="studio-staff">
                <h2 class="studio-staff__title">スタッフ紹介</h2>
                <div class="studio-staff__grid">
                    <?php
                    // スタッフ情報を取得
                    for ($i = 1; $i <= 2; $i++) {
                        $staff_name = get_field("staff{$i}_name");
                        $staff_position = get_field("staff{$i}_position");
                        $staff_message = get_field("staff{$i}_message");
                        $staff_image = get_field("staff{$i}_image");
                        
                        if (!empty($staff_name)):
                    ?>
                        <div class="staff-member">
                            <?php if ($staff_image): ?>
                                <div class="staff-member__image">
                                    <img src="<?php echo esc_url($staff_image['url']); ?>" alt="<?php echo esc_attr($staff_name); ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="staff-member__info">
                                <h3 class="staff-member__name"><?php echo esc_html($staff_name); ?></h3>
                                
                                <?php if ($staff_position): ?>
                                    <p class="staff-member__position"><?php echo esc_html($staff_position); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($staff_message): ?>
                                    <div class="staff-member__message">
                                        <?php echo nl2br(esc_html($staff_message)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php
                        endif;
                    }
                    ?>
                </div>
            </section>
            
            <!-- Map Section -->
            <?php if ($map_url): ?>
                <section class="studio-map">
                    <h2 class="studio-map__title">アクセス</h2>
                    <div class="studio-map__container">
                        <?php echo wp_kses_post($map_url); ?>
                    </div>
                </section>
            <?php endif; ?>
            
        <?php endwhile; ?>
    </div>
</main>

<!-- Contact & Booking Section -->
<?php get_template_part('template-parts/components/contact-booking'); ?>

<?php get_footer(); ?>