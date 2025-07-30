<?php
// 環境判定（ローカル環境かサーバー環境か）
if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] === 'localhost:8080' || $_SERVER['HTTP_HOST'] === 'localhost') {
    // ローカル環境の設定（WP-CLI含む）
    $host = "db"; // Docker環境のMySQLホスト名
    $db_name = "wordpress_678";
    $username = "wp_user";
    $password = "password";
} else {
    // サーバー環境の設定
    $host = "localhost";
    $db_name = "xb592942_1qqor";
    $username = "xb592942_hwnzr";
    $password = "bplyipjee2";
}

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>