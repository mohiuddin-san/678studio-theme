<?php
/**
 * Template for displaying single SEO Articles
 * SEO戦略記事の個別表示テンプレート
 */

get_header(); 

// ACFフィールドデータ取得
$primary_keyword = get_field('primary_keyword');
$secondary_keywords = get_field('secondary_keywords');
$content_strategy = get_field('content_strategy');
$target_conversion = get_field('target_conversion');
?>

<main id="main" class="site-main seo-article-single">
    <?php while ( have_posts() ) : the_post(); ?>
        
        <!-- ヘッダーセクション -->
        <section class="seo-article-hero">
            <div class="container">
                <!-- パンくずナビ -->
                <nav class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
                    <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a itemprop="item" href="<?php echo home_url(); ?>">
                            <span itemprop="name">トップ</span>
                        </a>
                        <meta itemprop="position" content="1" />
                    </span> <span class="separator">•</span> 
                    <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a itemprop="item" href="<?php echo get_post_type_archive_link('seo_articles'); ?>">
                            <span itemprop="name">お役立ち情報</span>
                        </a>
                        <meta itemprop="position" content="2" />
                    </span> <span class="separator">•</span> 
                    <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span itemprop="name" class="current-page"><?php the_title(); ?></span>
                        <meta itemprop="position" content="3" />
                    </span>
                </nav>
                
                <h1 class="seo-article-title"><?php the_title(); ?></h1>
                
                <!-- メタ情報 -->
                <div class="article-meta">
                    <time datetime="<?php echo get_the_date('c'); ?>">
                        <?php echo get_the_date('Y.m.d'); ?>
                    </time>
                    
                    <!-- カテゴリーとタグを統一デザインで表示 -->
                    <div class="article-tags">
                        <?php 
                        // カテゴリー表示（ゴールド）
                        $article_categories = get_the_terms(get_the_ID(), 'article_category');
                        if ($article_categories && !is_wp_error($article_categories)) {
                            foreach ($article_categories as $category) {
                                $archive_url = get_post_type_archive_link('seo_articles') . '?category=' . urlencode($category->slug);
                                echo '<a href="' . $archive_url . '" class="tag-item category-tag">' . esc_html($category->name) . '</a>';
                            }
                        }
                        
                        // タグ表示（ハッシュタグ風）
                        $article_tags = get_the_terms(get_the_ID(), 'article_tag');
                        if ($article_tags && !is_wp_error($article_tags)) {
                            foreach ($article_tags as $tag) {
                                $archive_url = get_post_type_archive_link('seo_articles') . '?tags=' . urlencode($tag->slug);
                                echo '<a href="' . $archive_url . '" class="tag-item tag-tag">' . esc_html($tag->name) . '</a>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="seo-article-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- メインコンテンツ -->
        <section class="seo-article-content">
            <div class="container">
                <div class="content-wrapper">
                    <!-- 左カラム: 記事本文 -->
                    <article class="main-content">
                        <!-- モバイル用目次 -->
                        <div class="mobile-toc-section">
                            <div class="mobile-toc-widget">
                                <h3>目次</h3>
                                <nav id="mobile-toc" class="mobile-toc">
                                    <!-- JavaScriptで動的に生成 -->
                                </nav>
                            </div>
                        </div>
                        
                        <!-- 記事本文 -->
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                        
                        <!-- CTA セクション -->
                        <?php if ($target_conversion && !empty($target_conversion)) : ?>
                            <div class="conversion-cta">
                                <h3>📸 撮影のご相談はこちら</h3>
                                <div class="cta-buttons">
                                    <?php foreach ($target_conversion as $conversion) : ?>
                                        <?php if ($conversion === 'studio_reservation') : ?>
                                            <a href="<?php echo home_url('/studio-reservation/'); ?>" class="cta-button cta-primary">
                                                スタジオを予約する
                                            </a>
                                        <?php elseif ($conversion === 'studio_search') : ?>
                                            <a href="<?php echo home_url('/studio-search/'); ?>" class="cta-button cta-secondary">
                                                スタジオを探す
                                            </a>
                                        <?php elseif ($conversion === 'contact') : ?>
                                            <a href="<?php echo home_url('/contact/'); ?>" class="cta-button cta-secondary">
                                                お問い合わせ
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- 関連キーワードタグ -->
                        <?php if ($secondary_keywords) : 
                            $keywords = array_map('trim', explode(',', $secondary_keywords));
                            if (!empty($keywords)) :
                        ?>
                            <div class="keyword-tags">
                                <h4>関連キーワード</h4>
                                <div class="tags-list">
                                    <?php foreach ($keywords as $keyword) : ?>
                                        <span class="keyword-tag"><?php echo esc_html($keyword); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; endif; ?>
                        
                        <!-- 記事メタ情報（カテゴリーとタグ） -->
                        <div class="article-meta-section">
                            <?php
                            $article_categories = get_the_terms(get_the_ID(), 'article_category');
                            $article_tags = get_the_terms(get_the_ID(), 'article_tag');
                            ?>
                            
                            <?php if ($article_categories && !is_wp_error($article_categories)) : ?>
                                <div class="meta-group">
                                    <h4>カテゴリー</h4>
                                    <div class="meta-tags">
                                        <?php foreach ($article_categories as $category) : ?>
                                            <?php $archive_url = get_post_type_archive_link('seo_articles') . '?category=' . urlencode($category->slug); ?>
                                            <a href="<?php echo $archive_url; ?>" class="tag-item category-tag">
                                                <?php echo esc_html($category->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($article_tags && !is_wp_error($article_tags)) : ?>
                                <div class="meta-group">
                                    <h4>タグ</h4>
                                    <div class="meta-tags">
                                        <?php foreach ($article_tags as $tag) : ?>
                                            <?php $archive_url = get_post_type_archive_link('seo_articles') . '?tags=' . urlencode($tag->slug); ?>
                                            <a href="<?php echo $archive_url; ?>" class="tag-item tag-tag">
                                                <?php echo esc_html($tag->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                    
                    <!-- 右カラム: サイドバー（動的目次） -->
                    <aside class="article-sidebar">
                        <!-- 動的目次 -->
                        <div class="sidebar-widget sticky-toc">
                            <h3>目次</h3>
                            <nav id="dynamic-toc" class="dynamic-toc">
                                <!-- JavaScriptで動的に生成 -->
                            </nav>
                        </div>
                        
                    </aside>
                </div>
            </div>
        </section>
        
        <!-- 関連記事 -->
        <?php
        // 現在の記事のカテゴリーとタグを取得
        $current_categories = get_the_terms(get_the_ID(), 'article_category');
        $current_tags = get_the_terms(get_the_ID(), 'article_tag');
        
        $related_posts = array();
        
        // 1. 同じカテゴリーの記事を優先して取得
        if ($current_categories && !is_wp_error($current_categories)) {
            $category_ids = wp_list_pluck($current_categories, 'term_id');
            $category_args = array(
                'post_type' => 'seo_articles',
                'posts_per_page' => 6,
                'post__not_in' => array(get_the_ID()),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'article_category',
                        'field'    => 'term_id',
                        'terms'    => $category_ids,
                    ),
                ),
            );
            $category_query = new WP_Query($category_args);
            if ($category_query->have_posts()) {
                while ($category_query->have_posts()) {
                    $category_query->the_post();
                    $related_posts[get_the_ID()] = get_post();
                }
                wp_reset_postdata();
            }
        }
        
        // 2. 同じタグの記事を取得（カテゴリーで足りない場合）
        if (count($related_posts) < 4 && $current_tags && !is_wp_error($current_tags)) {
            $tag_ids = wp_list_pluck($current_tags, 'term_id');
            $tag_args = array(
                'post_type' => 'seo_articles',
                'posts_per_page' => 6,
                'post__not_in' => array_merge(array(get_the_ID()), array_keys($related_posts)),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'article_tag',
                        'field'    => 'term_id',
                        'terms'    => $tag_ids,
                    ),
                ),
            );
            $tag_query = new WP_Query($tag_args);
            if ($tag_query->have_posts()) {
                while ($tag_query->have_posts()) {
                    $tag_query->the_post();
                    if (count($related_posts) < 4) {
                        $related_posts[get_the_ID()] = get_post();
                    }
                }
                wp_reset_postdata();
            }
        }
        
        // 3. コンテンツ戦略で取得（まだ足りない場合）
        if (count($related_posts) < 4 && $content_strategy) {
            $strategy_args = array(
                'post_type' => 'seo_articles',
                'posts_per_page' => 6,
                'post__not_in' => array_merge(array(get_the_ID()), array_keys($related_posts)),
                'meta_query' => array(
                    array(
                        'key' => 'content_strategy',
                        'value' => $content_strategy,
                        'compare' => '='
                    )
                )
            );
            $strategy_query = new WP_Query($strategy_args);
            if ($strategy_query->have_posts()) {
                while ($strategy_query->have_posts()) {
                    $strategy_query->the_post();
                    if (count($related_posts) < 4) {
                        $related_posts[get_the_ID()] = get_post();
                    }
                }
                wp_reset_postdata();
            }
        }
        
        // 4. 最新記事で補完（まだ足りない場合）
        if (count($related_posts) < 4) {
            $recent_args = array(
                'post_type' => 'seo_articles',
                'posts_per_page' => 6,
                'post__not_in' => array_merge(array(get_the_ID()), array_keys($related_posts)),
                'orderby' => 'date',
                'order' => 'DESC'
            );
            $recent_query = new WP_Query($recent_args);
            if ($recent_query->have_posts()) {
                while ($recent_query->have_posts()) {
                    $recent_query->the_post();
                    if (count($related_posts) < 4) {
                        $related_posts[get_the_ID()] = get_post();
                    }
                }
                wp_reset_postdata();
            }
        }
        
        // 最大4件に制限
        $related_posts = array_slice($related_posts, 0, 4);
        
        if (!empty($related_posts)) : ?>
            <section class="related-articles">
                <div class="container">
                    <h2>関連記事</h2>
                    <div class="articles-grid">
                        <?php foreach ($related_posts as $related_post) : 
                            // グローバルな$postを一時的に変更
                            $GLOBALS['post'] = $related_post;
                            setup_postdata($related_post);
                            
                            // 関連記事のカテゴリーとタグを取得
                            $rel_categories = get_the_terms(get_the_ID(), 'article_category');
                            $rel_tags = get_the_terms(get_the_ID(), 'article_tag');
                        ?>
                            <article class="article-card">
                                <!-- サムネイル -->
                                <div class="article-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <?php the_post_thumbnail('medium_large', array('alt' => get_the_title())); ?>
                                        <?php else : ?>
                                            <div class="default-thumbnail">
                                                <span class="thumbnail-icon">📝</span>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                
                                <!-- 記事情報 -->
                                <div class="article-card-content">
                                    <!-- 日付を上部に -->
                                    <div class="article-date">
                                        <?php echo get_the_date('Y.m.d'); ?>
                                    </div>
                                    
                                    <h3 class="article-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>
                                    
                                    <!-- カテゴリーとタグを下部に -->
                                    <div class="article-tags">
                                        <?php 
                                        // カテゴリー表示（ゴールド）
                                        if ($rel_categories && !is_wp_error($rel_categories)) {
                                            foreach ($rel_categories as $category) {
                                                echo '<span class="tag-item category-tag">' . esc_html($category->name) . '</span>';
                                            }
                                        }
                                        
                                        // タグ表示（ハッシュタグ風）
                                        if ($rel_tags && !is_wp_error($rel_tags)) {
                                            foreach ($rel_tags as $tag) {
                                                echo '<span class="tag-item tag-tag">' . esc_html($tag->name) . '</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; 
                        wp_reset_postdata(); ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        
    <?php endwhile; ?>
</main>

<?php get_template_part('template-parts/components/footer'); ?>

<!-- 構造化データ (JSON-LD) -->
<script type="application/ld+json">
<?php
$structured_data = array(
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => get_the_title(),
    'description' => get_the_excerpt(),
    'datePublished' => get_the_date('c'),
    'dateModified' => get_the_modified_date('c'),
    'author' => array(
        '@type' => 'Organization',
        'name' => '678 Studio'
    ),
    'publisher' => array(
        '@type' => 'Organization',
        'name' => '678 Studio',
        'logo' => array(
            '@type' => 'ImageObject',
            'url' => get_theme_file_uri('/assets/images/logo.png')
        )
    ),
    'mainEntityOfPage' => array(
        '@type' => 'WebPage',
        '@id' => get_permalink()
    )
);

if (has_post_thumbnail()) {
    $structured_data['image'] = get_the_post_thumbnail_url(null, 'full');
}

if ($primary_keyword) {
    $structured_data['keywords'] = $primary_keyword;
    if ($secondary_keywords) {
        $structured_data['keywords'] .= ', ' . $secondary_keywords;
    }
}

echo json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
</script>


<!-- 動的目次スクリプト -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const content = document.querySelector('.entry-content');
    const dynamicToc = document.getElementById('dynamic-toc');
    const mobileToc = document.getElementById('mobile-toc');
    
    if (!content) return;
    
    const headings = content.querySelectorAll('h2, h3, h4');
    const headingData = [];
    
    if (headings.length === 0) return;
    
    // 見出しデータを準備
    headings.forEach(function(heading, index) {
        const id = 'heading-' + index;
        heading.id = id;
        
        headingData.push({
            id: id,
            text: heading.textContent,
            level: parseInt(heading.tagName.charAt(1)),
            element: heading
        });
    });
    
    
    // 目次を生成する共通関数
    function createTocList(headingData) {
        const tocList = document.createElement('ul');
        
        headingData.forEach(function(item) {
            const li = document.createElement('li');
            li.className = 'toc-h' + item.level;
            
            const a = document.createElement('a');
            a.href = '#' + item.id;
            a.textContent = item.text;
            a.setAttribute('data-target', item.id);
            
            li.appendChild(a);
            tocList.appendChild(li);
        });
        
        return tocList;
    }
    
    // サイドバーの動的目次を生成
    if (dynamicToc) {
        const tocList = createTocList(headingData);
        dynamicToc.appendChild(tocList);
    }
    
    // モバイル用目次を生成
    if (mobileToc) {
        const mobileTocList = createTocList(headingData);
        mobileToc.appendChild(mobileTocList);
    }
    
    // スクロール追従機能の対象を両方の目次に適用
    const allTocs = [];
    if (dynamicToc) allTocs.push(dynamicToc);
    if (mobileToc) allTocs.push(mobileToc);
    
    if (allTocs.length > 0) {
        
        // スクロール追従機能
        let currentActiveId = null;
        
        function updateActiveHeading() {
            let activeHeading = null;
            const scrollY = window.scrollY + 150; // オフセット
            
            // 現在のスクロール位置に最も近い見出しを見つける
            headingData.forEach(function(item) {
                const rect = item.element.getBoundingClientRect();
                const elementTop = rect.top + window.scrollY;
                
                if (elementTop <= scrollY) {
                    activeHeading = item.id;
                }
            });
            
            // アクティブ状態を更新
            if (activeHeading !== currentActiveId) {
                // 前のアクティブ要素を非アクティブに
                if (currentActiveId) {
                    allTocs.forEach(function(toc) {
                        const prevActive = toc.querySelector('[data-target="' + currentActiveId + '"]');
                        if (prevActive) {
                            prevActive.classList.remove('active');
                            prevActive.parentElement.classList.remove('active'); // li要素からもactiveクラスを削除
                        }
                    });
                }
                
                // 新しいアクティブ要素を設定
                if (activeHeading) {
                    allTocs.forEach(function(toc) {
                        const newActive = toc.querySelector('[data-target="' + activeHeading + '"]');
                        if (newActive) {
                            newActive.classList.add('active');
                            newActive.parentElement.classList.add('active'); // li要素にもactiveクラスを追加
                        }
                    });
                }
                
                currentActiveId = activeHeading;
            }
        }
        
        // スクロールイベントリスナー
        let ticking = false;
        function onScroll() {
            if (!ticking) {
                requestAnimationFrame(function() {
                    updateActiveHeading();
                    ticking = false;
                });
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', onScroll);
        
        // 初期状態の設定
        updateActiveHeading();
        
        // 目次リンクのクリック処理
        allTocs.forEach(function(toc) {
            toc.addEventListener('click', function(e) {
                if (e.target.tagName === 'A') {
                    e.preventDefault();
                    const targetId = e.target.getAttribute('data-target');
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        const offsetTop = targetElement.getBoundingClientRect().top + window.scrollY - 120;
                        window.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    }
});
</script>

<?php get_footer(); ?>