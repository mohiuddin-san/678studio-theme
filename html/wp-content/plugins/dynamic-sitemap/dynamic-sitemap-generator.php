<?php
/**
 * WordPress Dynamic Sitemap Generator
 * 自動更新型サイトマップ生成システム
 *
 * Features:
 * - リアルタイム更新
 * - 画像サイトマップ対応
 * - ページ優先度自動計算
 * - カスタム投稿タイプ対応
 * - SEO最適化
 */

class DynamicSitemapGenerator {

    private $domain;
    private $protocol;
    private $exclude_ids = [];

    public function __construct() {
        $this->domain = $_SERVER['HTTP_HOST'];
        $this->protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';

        // WordPress hooks
        add_action('init', [$this, 'add_sitemap_rewrite_rules']);
        add_action('template_redirect', [$this, 'handle_sitemap_request']);
        add_action('save_post', [$this, 'clear_sitemap_cache']);
        add_action('delete_post', [$this, 'clear_sitemap_cache']);
    }

    /**
     * サイトマップ用のリライトルールを追加
     */
    public function add_sitemap_rewrite_rules() {
        add_rewrite_rule(
            '^sitemap\.xml$',
            'index.php?sitemap=main',
            'top'
        );
        add_rewrite_rule(
            '^sitemap-([^/]+)\.xml$',
            'index.php?sitemap=$matches[1]',
            'top'
        );

        // クエリ変数を追加
        add_filter('query_vars', function($vars) {
            $vars[] = 'sitemap';
            return $vars;
        });
    }

    /**
     * サイトマップリクエストを処理
     */
    public function handle_sitemap_request() {
        $sitemap = get_query_var('sitemap');

        if (!$sitemap) {
            return;
        }

        // キャッシュをチェック
        $cache_key = 'dynamic_sitemap_' . $sitemap;
        $cached_sitemap = wp_cache_get($cache_key);

        if ($cached_sitemap === false) {
            switch ($sitemap) {
                case 'main':
                    $cached_sitemap = $this->generate_main_sitemap();
                    break;
                case 'posts':
                    $cached_sitemap = $this->generate_posts_sitemap();
                    break;
                case 'pages':
                    $cached_sitemap = $this->generate_pages_sitemap();
                    break;
                case 'images':
                    $cached_sitemap = $this->generate_images_sitemap();
                    break;
                case 'stores':
                    $cached_sitemap = $this->generate_custom_post_type_sitemap('stores');
                    break;
                case 'studio_shops':
                    $cached_sitemap = $this->generate_custom_post_type_sitemap('studio_shops');
                    break;
                case 'seo_articles':
                    $cached_sitemap = $this->generate_custom_post_type_sitemap('seo_articles');
                    break;
                default:
                    // Try to handle as custom post type
                    $cached_sitemap = $this->generate_custom_post_type_sitemap($sitemap);
                    if (!$cached_sitemap) {
                        wp_die('Invalid sitemap', 404);
                    }
            }

            // 30分キャッシュ
            wp_cache_set($cache_key, $cached_sitemap, '', 1800);
        }

        $this->output_sitemap($cached_sitemap);
    }

    /**
     * メインサイトマップ（サイトマップインデックス）を生成
     */
    private function generate_main_sitemap() {
        $sitemaps = [];

        // 投稿サイトマップ
        if (wp_count_posts('post')->publish > 0) {
            $sitemaps[] = [
                'loc' => $this->protocol . '://' . $this->domain . '/sitemap-posts.xml',
                'lastmod' => $this->get_latest_post_date('post')
            ];
        }

        // ページサイトマップ
        if (wp_count_posts('page')->publish > 0) {
            $sitemaps[] = [
                'loc' => $this->protocol . '://' . $this->domain . '/sitemap-pages.xml',
                'lastmod' => $this->get_latest_post_date('page')
            ];
        }

        // 画像サイトマップ
        $image_count = $this->count_images();
        if ($image_count > 0) {
            $sitemaps[] = [
                'loc' => $this->protocol . '://' . $this->domain . '/sitemap-images.xml',
                'lastmod' => $this->get_latest_image_date()
            ];
        }

        // カスタム投稿タイプ
        $custom_post_types = get_post_types(['public' => true, '_builtin' => false]);
        foreach ($custom_post_types as $post_type) {
            if (wp_count_posts($post_type)->publish > 0) {
                $sitemaps[] = [
                    'loc' => $this->protocol . '://' . $this->domain . '/sitemap-' . $post_type . '.xml',
                    'lastmod' => $this->get_latest_post_date($post_type)
                ];
            }
        }

        return $this->build_sitemap_index($sitemaps);
    }

    /**
     * 投稿サイトマップを生成
     */
    private function generate_posts_sitemap() {
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'modified',
            'order' => 'DESC'
        ]);

        $urls = [];
        foreach ($posts as $post) {
            if (in_array($post->ID, $this->exclude_ids)) {
                continue;
            }

            $urls[] = [
                'loc' => get_permalink($post->ID),
                'lastmod' => mysql2date('c', $post->post_modified_gmt),
                'changefreq' => $this->calculate_changefreq($post),
                'priority' => $this->calculate_priority($post),
                'images' => $this->get_post_images($post->ID)
            ];
        }

        return $this->build_urlset($urls, true);
    }

    /**
     * ページサイトマップを生成
     */
    private function generate_pages_sitemap() {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ]);

        $urls = [];
        foreach ($pages as $page) {
            if (in_array($page->ID, $this->exclude_ids)) {
                continue;
            }

            $urls[] = [
                'loc' => get_permalink($page->ID),
                'lastmod' => mysql2date('c', $page->post_modified_gmt),
                'changefreq' => $this->calculate_changefreq($page),
                'priority' => $this->calculate_priority($page),
                'images' => $this->get_post_images($page->ID)
            ];
        }

        return $this->build_urlset($urls, true);
    }

    /**
     * カスタム投稿タイプサイトマップを生成
     */
    private function generate_custom_post_type_sitemap($post_type) {
        if (!post_type_exists($post_type)) {
            return false;
        }

        $posts = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'modified',
            'order' => 'DESC'
        ]);

        if (empty($posts)) {
            return false;
        }

        $urls = [];
        foreach ($posts as $post) {
            if (in_array($post->ID, $this->exclude_ids)) {
                continue;
            }

            $urls[] = [
                'loc' => get_permalink($post->ID),
                'lastmod' => mysql2date('c', $post->post_modified_gmt),
                'changefreq' => $this->calculate_changefreq($post),
                'priority' => $this->calculate_priority_for_post_type($post, $post_type),
                'images' => $this->get_post_images($post->ID)
            ];
        }

        return $this->build_urlset($urls, true);
    }

    /**
     * 投稿タイプ別の優先度を計算
     */
    private function calculate_priority_for_post_type($post, $post_type) {
        switch ($post_type) {
            case 'stores':
                return '0.9'; // 店舗情報は高優先度
            case 'studio_shops':
                return '0.7'; // スタジオショップは中優先度
            case 'seo_articles':
                return '0.5'; // SEO記事は標準優先度
            default:
                return $this->calculate_priority($post);
        }
    }

    /**
     * 画像サイトマップを生成
     */
    private function generate_images_sitemap() {
        $images = get_posts([
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'numberposts' => -1
        ]);

        $urls = [];
        foreach ($images as $image) {
            $parent_id = $image->post_parent;
            $parent_url = $parent_id ? get_permalink($parent_id) : home_url();

            $urls[] = [
                'loc' => $parent_url,
                'images' => [[
                    'loc' => wp_get_attachment_url($image->ID),
                    'title' => get_the_title($image->ID),
                    'caption' => $image->post_excerpt
                ]]
            ];
        }

        return $this->build_urlset($urls, true);
    }

    /**
     * 変更頻度を自動計算
     */
    private function calculate_changefreq($post) {
        $modified_date = strtotime($post->post_modified_gmt);
        $published_date = strtotime($post->post_date_gmt);
        $current_date = current_time('timestamp', 1);

        $days_since_modified = ($current_date - $modified_date) / DAY_IN_SECONDS;
        $days_since_published = ($current_date - $published_date) / DAY_IN_SECONDS;

        if ($days_since_modified < 1) return 'hourly';
        if ($days_since_modified < 7) return 'daily';
        if ($days_since_modified < 30) return 'weekly';
        if ($days_since_modified < 365) return 'monthly';

        return 'yearly';
    }

    /**
     * 優先度を自動計算
     */
    private function calculate_priority($post) {
        $priority = 0.5; // デフォルト

        // ページタイプによる調整
        if ($post->post_type === 'page') {
            $priority = 0.8;

            // フロントページは最高優先度
            if (get_option('page_on_front') == $post->ID) {
                $priority = 1.0;
            }
        }

        // コメント数による調整
        $comment_count = wp_count_comments($post->ID);
        if ($comment_count->approved > 10) {
            $priority += 0.1;
        }

        // 最近の更新による調整
        $modified_date = strtotime($post->post_modified_gmt);
        $days_since_modified = (current_time('timestamp', 1) - $modified_date) / DAY_IN_SECONDS;

        if ($days_since_modified < 7) {
            $priority += 0.1;
        }

        return min(1.0, round($priority, 1));
    }

    /**
     * 投稿に含まれる画像を取得
     */
    private function get_post_images($post_id) {
        $images = [];

        // アイキャッチ画像
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            $images[] = [
                'loc' => wp_get_attachment_url($thumbnail_id),
                'title' => get_the_title($thumbnail_id),
                'caption' => wp_get_attachment_caption($thumbnail_id)
            ];
        }

        // 本文中の画像
        $content = get_post_field('post_content', $post_id);
        preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*alt=[\'"]([^\'"]*)[\'"][^>]*>/i', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $src) {
                if (strpos($src, $this->domain) !== false) {
                    $images[] = [
                        'loc' => $src,
                        'title' => !empty($matches[2][$index]) ? $matches[2][$index] : '',
                        'caption' => ''
                    ];
                }
            }
        }

        return $images;
    }

    /**
     * サイトマップインデックスXMLを構築
     */
    private function build_sitemap_index($sitemaps) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemaps as $sitemap) {
            $xml .= "\t<sitemap>\n";
            $xml .= "\t\t<loc>" . esc_url($sitemap['loc']) . "</loc>\n";
            if (!empty($sitemap['lastmod'])) {
                $xml .= "\t\t<lastmod>" . esc_html($sitemap['lastmod']) . "</lastmod>\n";
            }
            $xml .= "\t</sitemap>\n";
        }

        $xml .= '</sitemapindex>';
        return $xml;
    }

    /**
     * URLセットXMLを構築
     */
    private function build_urlset($urls, $include_images = false) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';

        if ($include_images) {
            $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        }

        $xml .= ">\n";

        foreach ($urls as $url) {
            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . esc_url($url['loc']) . "</loc>\n";

            if (!empty($url['lastmod'])) {
                $xml .= "\t\t<lastmod>" . esc_html($url['lastmod']) . "</lastmod>\n";
            }

            if (!empty($url['changefreq'])) {
                $xml .= "\t\t<changefreq>" . esc_html($url['changefreq']) . "</changefreq>\n";
            }

            if (!empty($url['priority'])) {
                $xml .= "\t\t<priority>" . esc_html($url['priority']) . "</priority>\n";
            }

            // 画像情報
            if ($include_images && !empty($url['images'])) {
                foreach ($url['images'] as $image) {
                    $xml .= "\t\t<image:image>\n";
                    $xml .= "\t\t\t<image:loc>" . esc_url($image['loc']) . "</image:loc>\n";

                    if (!empty($image['title'])) {
                        $xml .= "\t\t\t<image:title>" . esc_html($image['title']) . "</image:title>\n";
                    }

                    if (!empty($image['caption'])) {
                        $xml .= "\t\t\t<image:caption>" . esc_html($image['caption']) . "</image:caption>\n";
                    }

                    $xml .= "\t\t</image:image>\n";
                }
            }

            $xml .= "\t</url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }

    /**
     * サイトマップを出力
     */
    private function output_sitemap($xml) {
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex, follow');
        echo $xml;
        exit;
    }

    /**
     * 最新投稿日を取得
     */
    private function get_latest_post_date($post_type = 'post') {
        global $wpdb;

        $latest = $wpdb->get_var($wpdb->prepare("
            SELECT post_modified_gmt
            FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_status = 'publish'
            ORDER BY post_modified_gmt DESC
            LIMIT 1
        ", $post_type));

        return $latest ? mysql2date('c', $latest) : date('c');
    }

    /**
     * 画像数をカウント
     */
    private function count_images() {
        return wp_count_posts('attachment')->inherit ?? 0;
    }

    /**
     * 最新画像日を取得
     */
    private function get_latest_image_date() {
        global $wpdb;

        $latest = $wpdb->get_var("
            SELECT post_date_gmt
            FROM {$wpdb->posts}
            WHERE post_type = 'attachment'
            AND post_mime_type LIKE 'image/%'
            ORDER BY post_date_gmt DESC
            LIMIT 1
        ");

        return $latest ? mysql2date('c', $latest) : date('c');
    }

    /**
     * キャッシュクリア
     */
    public function clear_sitemap_cache() {
        wp_cache_delete('dynamic_sitemap_main');
        wp_cache_delete('dynamic_sitemap_posts');
        wp_cache_delete('dynamic_sitemap_pages');
        wp_cache_delete('dynamic_sitemap_images');

        // カスタム投稿タイプのキャッシュもクリア
        $custom_post_types = get_post_types(['public' => true, '_builtin' => false]);
        foreach ($custom_post_types as $post_type) {
            wp_cache_delete('dynamic_sitemap_' . $post_type);
        }
    }
}

// システム初期化
new DynamicSitemapGenerator();