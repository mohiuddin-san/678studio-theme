<?php
/**
 * Archive template for Studio Features
 * スタジオ紹介記事一覧ページテンプレート
 */

get_header(); ?>

<main id="main" class="site-main archive-studio-features">
    <!-- ヘッダーセクション -->
    <section class="archive-hero">
        <div class="container">
            <!-- パンくずナビ -->
            <nav class="breadcrumb">
                <a href="<?php echo home_url(); ?>">ホーム</a> &gt; 
                <span>スタジオ紹介</span>
            </nav>
            
            <h1 class="archive-title">
                <span class="title-main">スタジオ紹介</span>
                <span class="title-sub">厳選されたフォトスタジオをご紹介</span>
            </h1>
            
            <p class="archive-description">
                678 Studioが厳選したフォトスタジオの魅力を詳しくご紹介。<br>
                各スタジオの特徴やサービス、料金プランなどの詳細情報をお届けします。
            </p>
        </div>
    </section>
    
    <!-- フィルター・検索セクション -->
    <section class="archive-filters">
        <div class="container">
            <div class="feature-filters">
                <button class="filter-btn active" data-filter="all">すべて</button>
                <button class="filter-btn" data-filter="station_access">駅近</button>
                <button class="filter-btn" data-filter="parking">駐車場完備</button>
                <button class="filter-btn" data-filter="barrier_free">バリアフリー</button>
                <button class="filter-btn" data-filter="kids_space">キッズスペース</button>
                <button class="filter-btn" data-filter="costume_rich">衣装豊富</button>
                <button class="filter-btn" data-filter="price_reasonable">リーズナブル</button>
            </div>
        </div>
    </section>
    
    <!-- 記事一覧セクション -->
    <section class="archive-content">
        <div class="container">
            <?php if ( have_posts() ) : ?>
                <!-- 記事件数表示 -->
                <div class="archive-meta">
                    <span class="article-count"><?php echo $wp_query->found_posts; ?>件のスタジオ</span>
                </div>
                
                <div class="studio-grid" id="studio-grid">
                    <?php while ( have_posts() ) : the_post(); 
                        // ACFデータ取得
                        $feature_highlights = get_field('feature_highlights') ?: array();
                        $promotion_campaign = get_field('promotion_campaign');
                        $studio_contact_info = get_field('studio_contact_info');
                        
                        // フィルター用のデータ属性を準備
                        $filter_attrs = '';
                        foreach ($feature_highlights as $highlight) {
                            $filter_attrs .= ' data-' . $highlight . '="true"';
                        }
                    ?>
                        <article class="studio-card" 
                                 data-title="<?php echo esc_attr(get_the_title()); ?>" 
                                 <?php echo $filter_attrs; ?>>
                            
                            <!-- サムネイル -->
                            <div class="studio-thumbnail">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium_large'); ?>
                                    </a>
                                <?php else : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <div class="no-image">
                                            <span>画像なし</span>
                                        </div>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- キャンペーンバッジ -->
                                <?php if ($promotion_campaign && !empty($promotion_campaign['discount_rate'])) : ?>
                                    <div class="campaign-badge">
                                        <?php echo esc_html($promotion_campaign['discount_rate']); ?>%OFF
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- カード本文 -->
                            <div class="studio-card-content">
                                <h2 class="studio-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <!-- 特徴アイコン -->
                                <?php if (!empty($feature_highlights)) : ?>
                                    <div class="feature-icons">
                                        <?php 
                                        $feature_icons = array(
                                            'station_access' => '🚃',
                                            'parking' => '🅿️',
                                            'barrier_free' => '♿',
                                            'kids_space' => '👶',
                                            'costume_rich' => '👘',
                                            'data_service' => '💾',
                                            'price_reasonable' => '💰',
                                            'staff_professional' => '👨‍💼',
                                            'weekend_available' => '📅',
                                            'early_morning' => '🌅',
                                            'same_day_delivery' => '⚡'
                                        );
                                        $count = 0;
                                        foreach ($feature_highlights as $highlight) : 
                                            if (isset($feature_icons[$highlight]) && $count < 5) : ?>
                                                <span class="feature-icon" title="<?php echo esc_attr($highlight); ?>">
                                                    <?php echo $feature_icons[$highlight]; ?>
                                                </span>
                                            <?php 
                                            $count++;
                                            endif; 
                                        endforeach; ?>
                                        <?php if (count($feature_highlights) > 5) : ?>
                                            <span class="more-features">+<?php echo count($feature_highlights) - 5; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- 抜粋 -->
                                <div class="studio-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 60, '...'); ?>
                                </div>
                                
                                <!-- メタ情報 -->
                                <div class="studio-meta">
                                    <?php if ($studio_contact_info && $studio_contact_info['studio_address']) : ?>
                                        <div class="meta-item location">
                                            <span class="meta-icon">📍</span>
                                            <?php 
                                            $address = $studio_contact_info['studio_address'];
                                            $location = preg_replace('/^.*?([都道府県]{2,3}[市区町村]+).*$/', '$1', $address);
                                            echo esc_html($location);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="meta-item date">
                                        <span class="meta-icon">📅</span>
                                        <?php echo get_the_date('Y.m.d'); ?>
                                    </div>
                                </div>
                                
                                <!-- アクションボタン -->
                                <div class="card-actions">
                                    <a href="<?php the_permalink(); ?>" class="btn-primary">
                                        詳細を見る
                                    </a>
                                    <a href="<?php echo home_url('/studio-reservation/'); ?>" class="btn-secondary">
                                        予約する
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <!-- ページネーション -->
                <?php
                $pagination_args = array(
                    'mid_size' => 2,
                    'prev_text' => '← 前へ',
                    'next_text' => '次へ →',
                    'screen_reader_text' => 'ページナビゲーション'
                );
                echo paginate_links($pagination_args);
                ?>
                
            <?php else : ?>
                <div class="no-posts">
                    <h2>記事が見つかりませんでした</h2>
                    <p>検索条件を変更してお試しください。</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- CTA セクション -->
    <section class="archive-cta">
        <div class="container">
            <div class="cta-content">
                <h2>スタジオをお探しですか？</h2>
                <p>全国のフォトスタジオから、あなたの撮影にぴったりのスタジオを見つけましょう</p>
                <div class="cta-buttons">
                    <a href="<?php echo home_url('/studio-search/'); ?>" class="cta-button primary">
                        スタジオを検索
                    </a>
                    <a href="<?php echo home_url('/studio-inquiry/'); ?>" class="cta-button secondary">
                        お問い合わせ
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_template_part('template-parts/components/footer'); ?>

<script>
// フィルター機能
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const studioCards = document.querySelectorAll('.studio-card');
    
    let currentFilter = 'all';
    
    // フィルターボタンクリック
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            currentFilter = this.dataset.filter;
            applyFilters();
        });
    });
    
    // フィルター適用
    function applyFilters() {
        studioCards.forEach(card => {
            if (currentFilter === 'all' || card.dataset[currentFilter]) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }
});
</script>

<?php get_footer(); ?>