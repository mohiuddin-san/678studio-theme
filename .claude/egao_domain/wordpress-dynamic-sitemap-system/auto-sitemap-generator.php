<?php
/**
 * 自動サイトマップ生成システム
 * WordPress フック対応版
 */

class AutoSitemapGenerator {

    private $domain = 'egao-salon.jp';
    private $protocol = 'https';
    private $db;
    private $sitemaps_dir;

    public function __construct() {
        // データベース接続
        try {
            require_once 'wp-config.php';
            $this->db = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
                DB_USER,
                DB_PASSWORD,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch (PDOException $e) {
            error_log('Sitemap Generator DB Error: ' . $e->getMessage());
            return false;
        }

        $this->sitemaps_dir = dirname(__DIR__) . '/sitemaps/';
    }

    /**
     * 全サイトマップを生成
     */
    public function generate_all_sitemaps() {
        $this->log_action('Generating all sitemaps...');

        $this->generate_pages_sitemap();
        $this->generate_posts_sitemap();
        $this->generate_news_sitemap();
        $this->generate_achievement_sitemap();
        $this->generate_staffs_sitemap();
        $this->generate_reading_glasses_news_sitemap();
        $this->generate_models_sitemap();
        $this->generate_archives_sitemap();
        $this->generate_main_sitemap();

        $this->log_action('All sitemaps generated successfully');
    }

    /**
     * ページサイトマップ生成
     */
    private function generate_pages_sitemap() {
        $posts = $this->get_posts('page');
        $xml = $this->build_urlset($posts);
        file_put_contents($this->sitemaps_dir . 'sitemap-pages.xml', $xml);
    }

    /**
     * 投稿サイトマップ生成
     */
    private function generate_posts_sitemap() {
        $posts = $this->get_posts('post');
        $xml = $this->build_urlset($posts);
        file_put_contents($this->sitemaps_dir . 'sitemap-posts.xml', $xml);
    }

    /**
     * ニュースサイトマップ生成
     */
    private function generate_news_sitemap() {
        $posts = $this->get_posts('news');
        $xml = $this->build_urlset($posts);
        file_put_contents($this->sitemaps_dir . 'sitemap-news.xml', $xml);
    }

    /**
     * 実績サイトマップ生成
     */
    private function generate_achievement_sitemap() {
        $posts = $this->get_posts('achievement');
        $xml = $this->build_urlset($posts);
        file_put_contents($this->sitemaps_dir . 'sitemap-achievement.xml', $xml);
    }

    /**
     * スタッフサイトマップ生成
     */
    private function generate_staffs_sitemap() {
        $posts = $this->get_posts('staffs');
        $xml = $this->build_urlset($posts);
        file_put_contents($this->sitemaps_dir . 'sitemap-staffs.xml', $xml);
    }

    /**
     * 老眼鏡ニュースサイトマップ生成
     */
    private function generate_reading_glasses_news_sitemap() {
        $posts = $this->get_posts('reading_glasses_news');
        $xml = $this->build_urlset($posts);
        file_put_contents($this->sitemaps_dir . 'sitemap-reading_glasses_news.xml', $xml);
    }

    /**
     * モデルサイトマップ生成
     */
    private function generate_models_sitemap() {
        $posts = $this->get_posts('models');
        $xml = $this->build_urlset($posts);
        file_put_contents($this->sitemaps_dir . 'sitemap-models.xml', $xml);
    }

    /**
     * アーカイブサイトマップ生成
     */
    private function generate_archives_sitemap() {
        $archives = [
            'tips' => ['priority' => '0.8', 'changefreq' => 'daily'],
            'news' => ['priority' => '0.8', 'changefreq' => 'weekly'],
            'achievement' => ['priority' => '0.7', 'changefreq' => 'monthly'],
            'reading-glasses-news' => ['priority' => '0.6', 'changefreq' => 'weekly'],
            'staffs' => ['priority' => '0.6', 'changefreq' => 'monthly']
        ];

        $xml = $this->build_archives_urlset($archives);
        file_put_contents($this->sitemaps_dir . 'sitemap-archives.xml', $xml);
    }

    /**
     * メインサイトマップインデックス生成
     */
    private function generate_main_sitemap() {
        $sitemap_files = [
            'sitemap-pages.xml',
            'sitemap-posts.xml',
            'sitemap-archives.xml',
            'sitemap-news.xml',
            'sitemap-achievement.xml',
            'sitemap-staffs.xml',
            'sitemap-reading_glasses_news.xml',
            'sitemap-models.xml',
            'sitemap-images.xml'
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemap_files as $file) {
            if (file_exists($this->sitemaps_dir . $file)) {
                $xml .= "\t<sitemap>\n";
                $xml .= "\t\t<loc>" . $this->protocol . '://' . $this->domain . '/sitemaps/' . $file . "</loc>\n";
                $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
                $xml .= "\t</sitemap>\n";
            }
        }

        $xml .= '</sitemapindex>';

        // ルートディレクトリのsitemap.xmlを更新
        file_put_contents(dirname($this->sitemaps_dir) . '/sitemap.xml', $xml);
    }

    /**
     * 投稿データを取得
     */
    private function get_posts($post_type) {
        $stmt = $this->db->prepare("
            SELECT ID, post_title, post_name, post_modified_gmt
            FROM wp_posts
            WHERE post_type = ? AND post_status = 'publish'
            ORDER BY post_modified_gmt DESC
        ");
        $stmt->execute([$post_type]);

        $posts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // 適切なパーマリンク生成
            if (!empty($row['post_name'])) {
                if ($post_type === 'page') {
                    $url = $this->protocol . '://' . $this->domain . '/' . $row['post_name'] . '/';
                } else {
                    $url = $this->protocol . '://' . $this->domain . '/' . $post_type . '/' . $row['post_name'] . '/';
                }
            } else {
                $url = $this->protocol . '://' . $this->domain . '/?p=' . $row['ID'];
            }

            $posts[] = [
                'loc' => $url,
                'lastmod' => date('c', strtotime($row['post_modified_gmt'])),
                'changefreq' => $this->get_changefreq($post_type),
                'priority' => $this->get_priority($post_type)
            ];
        }

        return $posts;
    }

    /**
     * URLセットXMLを構築
     */
    private function build_urlset($posts) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($posts as $post) {
            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . htmlspecialchars($post['loc']) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . $post['lastmod'] . "</lastmod>\n";
            $xml .= "\t\t<changefreq>" . $post['changefreq'] . "</changefreq>\n";
            $xml .= "\t\t<priority>" . $post['priority'] . "</priority>\n";
            $xml .= "\t</url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }

    /**
     * アーカイブ用URLセットXMLを構築
     */
    private function build_archives_urlset($archives) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($archives as $archive_name => $settings) {
            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . $this->protocol . '://' . $this->domain . '/' . $archive_name . '/</loc>' . "\n";
            $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
            $xml .= "\t\t<changefreq>" . $settings['changefreq'] . "</changefreq>\n";
            $xml .= "\t\t<priority>" . $settings['priority'] . "</priority>\n";
            $xml .= "\t</url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }

    /**
     * 変更頻度を取得
     */
    private function get_changefreq($post_type) {
        $freq_map = [
            'page' => 'weekly',
            'post' => 'monthly',
            'news' => 'weekly',
            'achievement' => 'monthly',
            'staffs' => 'monthly',
            'reading_glasses_news' => 'weekly',
            'models' => 'monthly'
        ];
        return isset($freq_map[$post_type]) ? $freq_map[$post_type] : 'monthly';
    }

    /**
     * 優先度を取得
     */
    private function get_priority($post_type) {
        $priority_map = [
            'page' => '0.8',
            'post' => '0.6',
            'news' => '0.7',
            'achievement' => '0.6',
            'staffs' => '0.5',
            'reading_glasses_news' => '0.6',
            'models' => '0.5'
        ];
        return isset($priority_map[$post_type]) ? $priority_map[$post_type] : '0.5';
    }

    /**
     * ログ出力
     */
    private function log_action($message) {
        $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
        file_put_contents($this->sitemaps_dir . 'sitemap.log', $log_message, FILE_APPEND | LOCK_EX);
    }
}

// WordPressコンテキスト外での実行
$generator = new AutoSitemapGenerator();
if (isset($argv[1]) && $argv[1] === 'generate') {
    $generator->generate_all_sitemaps();
    echo "All sitemaps generated successfully\n";
}
?>