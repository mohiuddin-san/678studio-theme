<?php
/**
 * Critical File Change Monitor
 * Monitors wp-config.php, functions.php, and other critical files
 */

$critical_files = [
    '/home/xb592942/678photo.com/public_html/wp-config.php',
    '/home/xb592942/678photo.com/public_html/wp-includes/functions.php',
    '/home/xb592942/egao-salon.jp/public_html/wp-config.php',
    '/home/xb592942/sugamo-navi.com/public_html/wp-config.php'
];

$alert_email = 'yoshiharajunichi@gmail.com';
$checksum_file = '/home/xb592942/security_monitor/file_checksums.txt';

// 既存のチェックサムを読み込み
$existing_checksums = [];
if (file_exists($checksum_file)) {
    $lines = file($checksum_file, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            list($file, $checksum) = explode(':', $line, 2);
            $existing_checksums[$file] = $checksum;
        }
    }
}

$changes_detected = [];
$current_checksums = [];

foreach ($critical_files as $file) {
    if (!file_exists($file)) continue;
    
    $current_checksum = md5_file($file);
    $current_checksums[$file] = $current_checksum;
    
    if (isset($existing_checksums[$file]) && $existing_checksums[$file] !== $current_checksum) {
        $changes_detected[] = $file;
    }
}

// 変更が検出された場合のアラート
if (!empty($changes_detected)) {
    $message = "🚨 CRITICAL FILE CHANGE ALERT 🚨\n\n";
    $message .= "The following critical files have been modified:\n\n";
    
    foreach ($changes_detected as $file) {
        $message .= "- $file\n";
    }
    
    $message .= "\nPlease verify these changes are legitimate.\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
    
    mail($alert_email, "[CRITICAL] WordPress Core Files Modified", $message);
    echo "ALERT: " . count($changes_detected) . " files changed\n";
}

// チェックサムファイルを更新
$checksum_data = '';
foreach ($current_checksums as $file => $checksum) {
    $checksum_data .= "$file:$checksum\n";
}
file_put_contents($checksum_file, $checksum_data);

echo "File integrity check completed\n";
