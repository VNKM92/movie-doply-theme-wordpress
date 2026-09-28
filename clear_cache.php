<?php
require_once __DIR__ . '/wp-load.php';

// Clear WordPress Object Cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "WordPress object cache flushed." . PHP_EOL;
}

// Clear OPcache if enabled
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset." . PHP_EOL;
}

// Delete all transient cache from options table
global $wpdb;
$deleted_transients = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
echo "Deleted {$deleted_transients} transient records from database." . PHP_EOL;

// Clean wp-content/cache directory
$cache_dir = WP_CONTENT_DIR . '/cache';
if (is_dir($cache_dir)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cache_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    $deleted_files = 0;
    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        if (@$todo($fileinfo->getRealPath())) {
            $deleted_files++;
        }
    }
    echo "Cleaned cache directory: {$deleted_files} files/folders removed." . PHP_EOL;
}

// Check active plugins
$plugins = get_option('active_plugins');
echo "Active plugins: " . PHP_EOL;
foreach ($plugins as $plugin) {
    echo " - " . $plugin . PHP_EOL;
}

echo "Active theme: " . get_template() . PHP_EOL;
echo "Site URL: " . get_option('siteurl') . PHP_EOL;
