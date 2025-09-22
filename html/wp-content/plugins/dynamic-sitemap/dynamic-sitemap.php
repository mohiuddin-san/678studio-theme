<?php
/**
 * Plugin Name: Dynamic Sitemap Generator
 * Description: 自動更新型動的サイトマップ生成システム
 * Version: 1.0
 * Author: Claude Code Assistant
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

// 動的サイトマップ生成器を読み込み
require_once plugin_dir_path(__FILE__) . 'dynamic-sitemap-generator.php';

// 678photo.com用サイトマップスケジューラを読み込み
require_once plugin_dir_path(__FILE__) . 'sitemap-scheduler.php';

// プラグイン有効化時の処理
register_activation_hook(__FILE__, function() {
    // リライトルールをフラッシュ
    flush_rewrite_rules();
});

// プラグイン無効化時の処理
register_deactivation_hook(__FILE__, function() {
    // リライトルールをフラッシュ
    flush_rewrite_rules();
});
