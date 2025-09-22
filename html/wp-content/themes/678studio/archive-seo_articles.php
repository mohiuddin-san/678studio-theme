<?php
/**
 * Archive template for SEO Articles
 * SEO記事一覧ページテンプレート
 */

get_header(); ?>

<main id="main" class="site-main archive-seo-articles">
    <!-- ヘッダーセクション -->
    <section class="archive-hero" style="background: linear-gradient(rgba(255, 255, 255, 0.6), rgba(248, 249, 250, 0.7)), url('<?php echo get_template_directory_uri(); ?>/assets/images/fv.jpg') center/cover no-repeat;">
        <div class="container">
            <h1 class="archive-title">
                <span class="title-english">Useful Information</span>
                <span class="title-main">お役立ち情報</span>
            </h1>
            
            <!-- パンくずナビ -->
            <nav class="breadcrumb">
                <a href="<?php echo home_url(); ?>">トップ</a> <span class="separator">•</span> 
                <span>お役立ち情報</span>
            </nav>
        </div>
    </section>
    
    <!-- フィルターセクション -->
    <section class="filter-section">
        <div class="container">
            <div class="filter-card">
                <div class="filter-row">
                    <label class="filter-label">カテゴリー</label>
                    <div class="filter-options">
                        <button class="filter-pill category-pill active" data-category="all">
                            すべて
                        </button>
                        <?php
                        $article_categories = get_terms(array(
                            'taxonomy' => 'article_category',
                            'hide_empty' => false,
                        ));
                        if ($article_categories && !is_wp_error($article_categories)) {
                            foreach ($article_categories as $category) {
                                echo '<button class="filter-pill category-pill" data-category="' . esc_attr($category->slug) . '">';
                                echo esc_html($category->name);
                                echo '</button>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="filter-row">
                    <label class="filter-label">タグ</label>
                    <div class="filter-options">
                        <?php
                        $article_tags = get_terms(array(
                            'taxonomy' => 'article_tag',
                            'hide_empty' => false,
                        ));
                        if ($article_tags && !is_wp_error($article_tags)) {
                            foreach ($article_tags as $tag) {
                                echo '<button class="filter-pill tag-pill" data-tag="' . esc_attr($tag->slug) . '">';
                                echo esc_html($tag->name);
                                echo '</button>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button class="search-btn">検索</button>
                    <button class="reset-btn">リセット</button>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 記事一覧セクション -->
    <section class="archive-content">
        <div class="container">
            <?php if ( have_posts() ) : ?>
                <!-- 記事件数表示 -->
                <div class="archive-meta">
                    <span class="article-count"><?php echo $wp_query->found_posts; ?>件の記事</span>
                </div>
                
                <div class="articles-grid" id="articles-grid">
                    <?php while ( have_posts() ) : the_post(); 
                        // ACFデータ取得
                        $primary_keyword = get_field('primary_keyword');
                        $content_strategy = get_field('content_strategy');
                        $reading_time = ceil(str_word_count(strip_tags(get_the_content())) / 200);
                    ?>
                        <?php
                        // Get taxonomy data for filtering
                        $article_categories = get_the_terms(get_the_ID(), 'article_category');
                        $article_tags = get_the_terms(get_the_ID(), 'article_tag');
                        
                        $category_slugs = array();
                        $tag_slugs = array();
                        
                        if ($article_categories && !is_wp_error($article_categories)) {
                            foreach ($article_categories as $cat) {
                                $category_slugs[] = $cat->slug;
                            }
                        }
                        if ($article_tags && !is_wp_error($article_tags)) {
                            foreach ($article_tags as $tag) {
                                $tag_slugs[] = $tag->slug;
                            }
                        }
                        ?>
                        <article class="article-card" 
                                 data-categories="<?php echo esc_attr(implode(',', $category_slugs)); ?>"
                                 data-tags="<?php echo esc_attr(implode(',', $tag_slugs)); ?>"
                                 data-strategy="<?php echo esc_attr($content_strategy); ?>"
                                 data-title="<?php echo esc_attr(get_the_title()); ?>">
                            
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
                                
                                <h2 class="article-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <div class="article-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 30, '...'); ?>
                                </div>
                                
                                <!-- カテゴリーとタグを下部に -->
                                <div class="article-tags">
                                    <?php 
                                    // カテゴリー表示（緑色）
                                    $article_categories = get_the_terms(get_the_ID(), 'article_category');
                                    if ($article_categories && !is_wp_error($article_categories)) {
                                        foreach ($article_categories as $category) {
                                            echo '<span class="tag-item category-tag">' . esc_html($category->name) . '</span>';
                                        }
                                    }
                                    
                                    // タグ表示（グレー）
                                    $article_tags = get_the_terms(get_the_ID(), 'article_tag');
                                    if ($article_tags && !is_wp_error($article_tags)) {
                                        foreach ($article_tags as $tag) {
                                            echo '<span class="tag-item tag-tag">' . esc_html($tag->name) . '</span>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <!-- ページネーション -->
                <div class="pagination-wrapper">
                    <?php
                    $pagination_args = array(
                        'mid_size' => 2,
                        'prev_text' => '← 前のページ',
                        'next_text' => '次のページ →',
                    );
                    echo paginate_links($pagination_args);
                    ?>
                </div>
                
            <?php else : ?>
                <div class="no-posts">
                    <div class="no-posts-icon">📄</div>
                    <h2>記事が見つかりませんでした</h2>
                    <p>検索条件を変更してお試しください。</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- 関連リンクセクション -->
    <section class="related-links">
        <div class="container">
            <div class="links-grid">
                <div class="link-card dark-card">
                    <div class="card-content">
                        <div class="card-text">
                            <span class="card-english">Studio Reservation</span>
                            <h3>スタジオ予約 <span class="inline-arrow">→</span></h3>
                        </div>
                        <div class="card-image">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-1.jpg" alt="スタジオ予約" />
                        </div>
                    </div>
                    <a href="<?php echo home_url('/studio-reservation/'); ?>" class="card-link"></a>
                </div>
                
                <div class="link-card green-card">
                    <div class="card-content">
                        <div class="card-text">
                            <span class="card-english">Contact Us</span>
                            <h3>お問い合わせ <span class="inline-arrow">→</span></h3>
                        </div>
                        <div class="card-image">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-2.jpg" alt="お問い合わせ" />
                        </div>
                    </div>
                    <a href="<?php echo home_url('/contact/'); ?>" class="card-link"></a>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// ピルボタンフィルター機能（URL パラメータ対応）
document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.querySelector('.search-btn');
    const resetBtn = document.querySelector('.reset-btn');
    const categoryPills = document.querySelectorAll('.category-pill');
    const tagPills = document.querySelectorAll('.tag-pill');
    const articleCards = document.querySelectorAll('.article-card');
    
    // URLパラメータから初期状態を設定
    function initializeFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const categoryParam = urlParams.get('category');
        const tagsParam = urlParams.get('tags');
        
        // カテゴリーフィルターの設定
        if (categoryParam) {
            categoryPills.forEach(pill => pill.classList.remove('active'));
            const categoryPill = document.querySelector(`[data-category="${categoryParam}"]`);
            if (categoryPill) {
                categoryPill.classList.add('active');
            }
        }
        
        // タグフィルターの設定
        if (tagsParam) {
            const tags = tagsParam.split(',');
            tagPills.forEach(pill => {
                if (tags.includes(pill.dataset.tag)) {
                    pill.classList.add('active');
                }
            });
        }
        
        // フィルターを適用
        applyFilters();
    }
    
    // URLを更新する関数
    function updateURL() {
        const activeCategory = document.querySelector('.category-pill.active')?.dataset.category;
        const activeTags = Array.from(document.querySelectorAll('.tag-pill.active'))
            .map(pill => pill.dataset.tag).filter(Boolean);
        
        const urlParams = new URLSearchParams();
        
        if (activeCategory && activeCategory !== 'all') {
            urlParams.set('category', activeCategory);
        }
        
        if (activeTags.length > 0) {
            urlParams.set('tags', activeTags.join(','));
        }
        
        const newURL = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.pushState({}, '', newURL);
    }
    
    // カテゴリーピルクリック（単一選択）
    categoryPills.forEach(pill => {
        pill.addEventListener('click', function() {
            categoryPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            updateURL();
        });
    });
    
    // タグピルクリック（複数選択可能）
    tagPills.forEach(pill => {
        pill.addEventListener('click', function() {
            this.classList.toggle('active');
            updateURL();
        });
    });
    
    // 検索ボタンクリック
    searchBtn.addEventListener('click', function() {
        applyFilters();
        updateURL();
    });
    
    // リセットボタンクリック
    resetBtn.addEventListener('click', function() {
        // 全てのピルを非アクティブに
        categoryPills.forEach(pill => pill.classList.remove('active'));
        tagPills.forEach(pill => pill.classList.remove('active'));
        
        // デフォルトを選択（すべて）
        if (categoryPills[0]) categoryPills[0].classList.add('active'); // "すべて"
        
        applyFilters();
        updateURL();
    });
    
    // フィルター適用
    function applyFilters() {
        const activeCategory = document.querySelector('.category-pill.active')?.dataset.category;
        const activeTags = Array.from(document.querySelectorAll('.tag-pill.active'))
            .map(pill => pill.dataset.tag).filter(Boolean);
        
        let visibleCount = 0;
        
        articleCards.forEach(card => {
            let shouldShow = true;
            
            // カテゴリーフィルター
            if (activeCategory && activeCategory !== 'all') {
                const cardCategories = card.dataset.categories ? card.dataset.categories.split(',') : [];
                if (!cardCategories.includes(activeCategory)) {
                    shouldShow = false;
                }
            }
            
            // タグフィルター（複数選択の場合はOR条件）
            if (activeTags.length > 0) {
                const cardTags = card.dataset.tags ? card.dataset.tags.split(',') : [];
                const hasMatchingTag = activeTags.some(tag => cardTags.includes(tag));
                if (!hasMatchingTag) {
                    shouldShow = false;
                }
            }
            
            if (shouldShow) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });
        
        // 件数表示を更新
        const countElement = document.querySelector('.article-count');
        if (countElement) {
            countElement.textContent = `${visibleCount}件の記事`;
        }
    }
    
    // 初期化
    initializeFromURL();
});
</script>

<?php get_footer(); ?>