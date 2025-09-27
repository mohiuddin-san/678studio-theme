<?php
/*
Template Name: Helper Test Page
*/

get_header();

// 管理者権限チェック
if (!current_user_can('administrator')) {
    echo '<p>管理者権限が必要です。</p>';
    get_footer();
    exit;
}

echo '<div style="max-width: 800px; margin: 50px auto; padding: 20px; font-family: monospace;">';
echo do_shortcode('[studio_helpers_test shop_id="122"]');
echo '</div>';

get_footer();
?>