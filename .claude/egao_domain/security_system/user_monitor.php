<?php
/**
 * WordPress User Account Creation Monitor
 * Alerts on new user account creation across all domains
 * Created: Sep 13, 2025
 */

// Configuration
$domains = [
    '678photo.com',
    'egao-salon.jp', 
    'sugamo-navi.com'
];

$alert_email = 'yoshiharajunichi@gmail.com'; // 通知先メール
$log_file = '/home/xb592942/security_monitor/user_alerts.log';
$last_check_file = '/home/xb592942/security_monitor/last_check.txt';

// 前回チェック時刻取得
$last_check = file_exists($last_check_file) ? 
    trim(file_get_contents($last_check_file)) : 
    date('Y-m-d H:i:s', strtotime('-1 hour'));

$current_time = date('Y-m-d H:i:s');
$new_users_found = [];

foreach ($domains as $domain) {
    $config_path = "/home/xb592942/$domain/public_html/wp-config.php";
    
    if (!file_exists($config_path)) continue;
    
    // wp-config.phpからDB情報を読み取り
    $config = file_get_contents($config_path);
    preg_match("/define\('DB_NAME', '([^']+)'\)/", $config, $db_name);
    preg_match("/define\('DB_USER', '([^']+)'\)/", $config, $db_user);
    preg_match("/define\('DB_PASSWORD', '([^']+)'\)/", $config, $db_pass);
    preg_match("/define\('DB_HOST', '([^']+)'\)/", $config, $db_host);
    
    if (!isset($db_name[1])) continue;
    
    try {
        $pdo = new PDO("mysql:host=" . $db_host[1] . ";dbname=" . $db_name[1], 
                       $db_user[1], $db_pass[1]);
        
        // 前回チェック以降の新規ユーザーを検索
        $stmt = $pdo->prepare("
            SELECT user_login, user_email, user_registered, display_name 
            FROM wp_users 
            WHERE user_registered > ? 
            ORDER BY user_registered DESC
        ");
        
        $stmt->execute([$last_check]);
        $new_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($new_users as $user) {
            $new_users_found[] = [
                'domain' => $domain,
                'user_login' => $user['user_login'],
                'user_email' => $user['user_email'],
                'user_registered' => $user['user_registered'],
                'display_name' => $user['display_name']
            ];
        }
        
    } catch (Exception $e) {
        error_log("Monitor error for $domain: " . $e->getMessage());
    }
}

// 新規ユーザーが発見された場合、アラート送信
if (!empty($new_users_found)) {
    $message = "🚨 NEW USER ACCOUNT ALERT 🚨\n\n";
    $message .= "New WordPress user accounts detected:\n\n";
    
    foreach ($new_users_found as $user) {
        $message .= "Domain: " . $user['domain'] . "\n";
        $message .= "Username: " . $user['user_login'] . "\n";
        $message .= "Email: " . $user['user_email'] . "\n";
        $message .= "Created: " . $user['user_registered'] . "\n";
        $message .= "Display Name: " . $user['display_name'] . "\n";
        $message .= "---\n";
    }
    
    $message .= "\nPlease verify if these accounts are legitimate.\n";
    $message .= "Time: $current_time\n";
    
    // メール送信
    mail($alert_email, "[SECURITY ALERT] New WordPress User Created", $message);
    
    // ログ記録
    file_put_contents($log_file, "[$current_time] " . count($new_users_found) . 
                     " new users detected\n" . $message . "\n\n", FILE_APPEND);
}

// 最終チェック時刻を更新
file_put_contents($last_check_file, $current_time);

echo "Monitor check completed. New users found: " . count($new_users_found) . "\n";
