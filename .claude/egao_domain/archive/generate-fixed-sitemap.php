<?php
/**
 * Fixed Sitemap Generator - Proper Permalinks
 * Quick fix for egao-salon.jp sitemap
 */

// WordPress settings
require_once 'wp-config.php';

try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASSWORD,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$domain = 'egao-salon.jp';
$protocol = 'https';

// Get all published pages
$stmt = $db->prepare("
    SELECT ID, post_title, post_name, post_modified_gmt
    FROM wp_posts
    WHERE post_type = 'page' AND post_status = 'publish'
    ORDER BY post_modified_gmt DESC
");
$stmt->execute();

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $xml .= "\t<url>\n";

    // Generate proper permalink
    if (!empty($row['post_name'])) {
        $url = $protocol . '://' . $domain . '/' . $row['post_name'] . '/';
    } else {
        $url = $protocol . '://' . $domain . '/?p=' . $row['ID'];
    }

    $xml .= "\t\t<loc>" . htmlspecialchars($url) . "</loc>\n";
    $xml .= "\t\t<lastmod>" . date('c', strtotime($row['post_modified_gmt'])) . "</lastmod>\n";
    $xml .= "\t\t<changefreq>weekly</changefreq>\n";
    $xml .= "\t\t<priority>0.8</priority>\n";
    $xml .= "\t</url>\n";
}

$xml .= '</urlset>';

// Output to file
file_put_contents('sitemap-pages.xml', $xml);

echo "✅ Fixed sitemap generated with proper permalinks\n";
echo "📊 Pages processed: " . $stmt->rowCount() . "\n";
?>