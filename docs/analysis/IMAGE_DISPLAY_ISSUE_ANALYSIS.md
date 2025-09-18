# 画像表示問題 - 調査レポート

## 🚨 問題概要
登録したギャラリー画像とメイン画像が管理画面に表示されない

## 🔍 調査結果

### データベース状況
```sql
-- メイン画像: studio_shops.main_image
id=45, name="??????", main_image=NULL

-- ギャラリー画像: studio_shop_images
gallery_count = 0 (画像データが存在しない)
```

### 🎯 根本原因分析

#### 問題1: フロントエンド⇔バックエンドの処理不整合

**フロントエンド (studio-shops-plugin.php:173-177)**
```php
// 新しい最適化されたコード
$main_image_files = process_and_save_uploaded_files([$_FILES['main_image']], $shop_id ?: 0);
if (!empty($main_image_files)) {
    $main_image_processed = $main_image_files[0]['url'];
}
```

**バックエンド API (api-helper.php:357-365)**
```php
// 古いBase64処理を期待するコード
if (isset($data['main_image']) && !empty($data['main_image'])) {
    $processed_main_images = process_and_save_images([$data['main_image']], $shop_id, null);
    // ↑ Base64データを期待しているが、URLが送信されている
}
```

#### 問題2: APIデータ形式の不整合

**送信側 (studio-shops-plugin.php:230-232)**
```php
// URLが送信される
if ($main_image_processed) {
    $api_data['main_image'] = $main_image_processed; // URL形式
}
```

**受信側 (api-helper.php:359)**
```php
// Base64処理を実行
$processed_main_images = process_and_save_images([$data['main_image']], $shop_id, null);
// ↑ URLをBase64として処理しようとして失敗
```

### 🎯 問題の構造

1. **フロントエンド最適化**: 直接ファイル処理 → URL生成
2. **バックエンドAPI**: Base64処理期待 → URL受信で処理失敗
3. **結果**: データベースに画像データが保存されない

## 💡 修正方針

### Option 1: API層の修正（推奨）
バックエンドAPIをURL処理に対応させる

**メリット:**
- 最適化された処理を維持
- パフォーマンス向上を保持
- 一元的な修正

**デメリット:**
- APIの互換性要考慮

### Option 2: フロントエンド処理の調整
フロントエンドでBase64変換を復活

**メリット:**
- 既存API互換性維持

**デメリット:**
- 最適化効果の無効化
- メモリ使用量増加

### Option 3: ハイブリッド対応
両方の形式に対応

## 📋 推奨実装手順

### 1. API層でURL処理対応
```php
// api-helper.php内で
if (isset($data['main_image']) && !empty($data['main_image'])) {
    // URL形式かBase64形式かを判定
    if (filter_var($data['main_image'], FILTER_VALIDATE_URL)) {
        // URL形式: 直接データベースに保存
        $main_image_url = $data['main_image'];
    } else {
        // Base64形式: 既存処理
        $processed_main_images = process_and_save_images([$data['main_image']], $shop_id, null);
    }
}
```

### 2. ギャラリー画像も同様に対応

### 3. 互換性テスト

## 🚨 影響範囲
- **リスク**: 中（API互換性）
- **緊急度**: 高（画像管理機能が使用不可）
- **修正範囲**: `api-helper.php`の画像処理部分

## ✅ 修正完了

### 実装した改善点

#### 1. メイン画像処理の両形式対応
```php
// URL形式かBase64形式かを自動判定
if (filter_var($data['main_image'], FILTER_VALIDATE_URL) || strpos($data['main_image'], '/wp-content/') === 0) {
    // URL format: 直接使用
    $main_image_url = $data['main_image'];
} else {
    // Base64 format: 既存処理
    $processed_main_images = process_and_save_images([$data['main_image']], $shop_id, null);
}
```

#### 2. ギャラリー画像処理の両形式対応
```php
// 画像を形式別に分類
foreach ($data['gallery_images'] as $image) {
    if (filter_var($image, FILTER_VALIDATE_URL) || strpos($image, '/wp-content/') === 0) {
        $url_images[] = ['url' => $image];
    } else {
        $base64_images[] = $image;
    }
}
```

#### 3. デバッグログの強化
- 処理形式の判定結果をログ出力
- 各ステップの成功/失敗を詳細記録
- トラブルシューティングの効率化

### 🎯 修正効果
- **完全互換性**: URL・Base64両形式に対応
- **最適化維持**: 新しい処理の効率性を保持
- **安定性向上**: エラーハンドリングとログ強化
- **データ保存**: 確実な画像データベース保存

### 📊 対象ファイル
- `api-helper.php`: create_studio_shop(), update_studio_shop() 関数
- 新規作成・更新両方の処理で対応完了