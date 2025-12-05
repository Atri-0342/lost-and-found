<?php
// ------------------------------------
// Dynamic Sitemap Generator for LAF
// ------------------------------------
header("Content-Type: application/xml; charset=UTF-8");

$base_url = "http://localhost/laf/";

// Collect all PHP files from root and admin folder
function get_php_files($dir, $base_url, $prefix = '') {
    $urls = [];
    foreach (glob($dir . "/*.php") as $file) {
        $filename = basename($file);
        // Skip technical files that shouldn’t appear
        if (in_array($filename, ['db.php', 'nav.php', 'style.php'])) continue;

        $url = $base_url . $prefix . $filename;
        $lastmod = date("Y-m-d", filemtime($file));

        $urls[] = [
            'loc' => $url,
            'lastmod' => $lastmod,
            'priority' => (strpos($prefix, 'admin') !== false) ? '0.6' : '0.8',
            'changefreq' => (strpos($prefix, 'admin') !== false) ? 'weekly' : 'daily'
        ];
    }
    return $urls;
}

// Fetch files
$root_files = get_php_files(__DIR__, $base_url);
$admin_files = get_php_files(__DIR__ . '/admin', $base_url, 'admin/');

// Combine all URLs
$all_urls = array_merge($root_files, $admin_files);

// XML Header
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($all_urls as $page): ?>
    <url>
        <loc><?= htmlspecialchars($page['loc']) ?></loc>
        <lastmod><?= $page['lastmod'] ?></lastmod>
        <changefreq><?= $page['changefreq'] ?></changefreq>
        <priority><?= $page['priority'] ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
