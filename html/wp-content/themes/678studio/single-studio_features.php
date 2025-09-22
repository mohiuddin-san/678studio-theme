<?php
/**
 * Template for displaying single Studio Feature articles
 * スタジオ紹介記事の個別表示テンプレート
 */

get_header(); 

// ACFフィールドデータ取得
$target_studio = get_field('target_studio');
$feature_highlights = get_field('feature_highlights');
$promotion_campaign = get_field('promotion_campaign');
$studio_contact_info = get_field('studio_contact_info');
?>

<main id="main" class="site-main studio-feature-single">
    <?php while ( have_posts() ) : the_post(); ?>
        
        <!-- ヘッダーセクション -->
        <section class="studio-feature-hero">
            <div class="container">
                <!-- パンくずナビ -->
                <nav class="breadcrumb">
                    <a href="<?php echo home_url(); ?>">ホーム</a> &gt; 
                    <a href="<?php echo get_post_type_archive_link('studio_features'); ?>">スタジオ紹介</a> &gt; 
                    <span><?php the_title(); ?></span>
                </nav>
                
                <h1 class="studio-feature-title"><?php the_title(); ?></h1>
                
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="studio-feature-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- メインコンテンツ -->
        <section class="studio-feature-content">
            <div class="container">
                <div class="content-wrapper">
                    <!-- 左カラム: 記事本文 -->
                    <div class="main-content">
                        <!-- アピールポイント -->
                        <?php if ($feature_highlights) : ?>
                            <div class="feature-highlights">
                                <h2>このスタジオの特徴</h2>
                                <ul class="highlights-list">
                                    <?php 
                                    $highlight_labels = array(
                                        'station_access' => '駅近アクセス抜群',
                                        'parking' => '無料駐車場完備',
                                        'barrier_free' => 'バリアフリー対応',
                                        'kids_space' => 'キッズスペースあり',
                                        'costume_rich' => '衣装レンタル豊富',
                                        'data_service' => 'データ納品サービス',
                                        'price_reasonable' => 'リーズナブル料金',
                                        'staff_professional' => '経験豊富スタッフ',
                                        'weekend_available' => '土日祝日営業',
                                        'early_morning' => '早朝撮影対応',
                                        'same_day_delivery' => '当日納品可能',
                                    );
                                    foreach ($feature_highlights as $highlight) : 
                                        if (isset($highlight_labels[$highlight])) :
                                    ?>
                                        <li class="highlight-item highlight-<?php echo esc_attr($highlight); ?>">
                                            <span class="highlight-icon">✓</span>
                                            <?php echo esc_html($highlight_labels[$highlight]); ?>
                                        </li>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- 記事本文 -->
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                        
                        <!-- キャンペーン情報 -->
                        <?php if ($promotion_campaign && !empty($promotion_campaign['campaign_title'])) : ?>
                            <div class="promotion-campaign">
                                <h2>🎉 キャンペーン情報</h2>
                                <div class="campaign-box">
                                    <h3><?php echo esc_html($promotion_campaign['campaign_title']); ?></h3>
                                    <?php if ($promotion_campaign['discount_rate']) : ?>
                                        <div class="discount-rate">
                                            <span class="rate-number"><?php echo esc_html($promotion_campaign['discount_rate']); ?>%</span>
                                            <span class="rate-label">OFF</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($promotion_campaign['campaign_period_start'] || $promotion_campaign['campaign_period_end']) : ?>
                                        <div class="campaign-period">
                                            期間: 
                                            <?php echo esc_html($promotion_campaign['campaign_period_start']); ?> 〜 
                                            <?php echo esc_html($promotion_campaign['campaign_period_end']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($promotion_campaign['campaign_description']) : ?>
                                        <div class="campaign-description">
                                            <?php echo wp_kses_post($promotion_campaign['campaign_description']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 右カラム: スタジオ情報 -->
                    <aside class="studio-sidebar">
                        <!-- スタジオ基本情報 -->
                        <div class="studio-info-box">
                            <h3>スタジオ情報</h3>
                            
                            <?php if ($studio_contact_info) : ?>
                                <?php if ($studio_contact_info['studio_address']) : ?>
                                    <div class="info-item">
                                        <span class="info-label">📍 住所</span>
                                        <span class="info-value"><?php echo esc_html($studio_contact_info['studio_address']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($studio_contact_info['studio_phone']) : ?>
                                    <div class="info-item">
                                        <span class="info-label">📞 電話</span>
                                        <span class="info-value">
                                            <a href="tel:<?php echo esc_attr(str_replace('-', '', $studio_contact_info['studio_phone'])); ?>">
                                                <?php echo esc_html($studio_contact_info['studio_phone']); ?>
                                            </a>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($studio_contact_info['business_hours']) : ?>
                                    <div class="info-item">
                                        <span class="info-label">🕐 営業時間</span>
                                        <span class="info-value"><?php echo esc_html($studio_contact_info['business_hours']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($studio_contact_info['studio_holidays']) : ?>
                                    <div class="info-item">
                                        <span class="info-label">📅 定休日</span>
                                        <span class="info-value"><?php echo esc_html($studio_contact_info['studio_holidays']); ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- CTA ボタン -->
                            <div class="studio-cta">
                                <a href="<?php echo home_url('/studio-reservation/'); ?>" class="cta-button cta-reservation">
                                    このスタジオを予約する
                                </a>
                                <a href="<?php echo home_url('/studio-inquiry/'); ?>" class="cta-button cta-inquiry">
                                    お問い合わせ
                                </a>
                            </div>
                        </div>
                        
                        <!-- 関連記事 -->
                        <?php
                        $related_args = array(
                            'post_type' => 'studio_features',
                            'posts_per_page' => 3,
                            'post__not_in' => array(get_the_ID()),
                            'orderby' => 'rand'
                        );
                        $related_query = new WP_Query($related_args);
                        
                        if ($related_query->have_posts()) : ?>
                            <div class="related-studios">
                                <h3>他のスタジオ紹介</h3>
                                <ul class="related-list">
                                    <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                                        <li>
                                            <a href="<?php the_permalink(); ?>">
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <?php the_post_thumbnail('thumbnail'); ?>
                                                <?php endif; ?>
                                                <span class="related-title"><?php the_title(); ?></span>
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                            <?php wp_reset_postdata(); ?>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        </section>
        
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>