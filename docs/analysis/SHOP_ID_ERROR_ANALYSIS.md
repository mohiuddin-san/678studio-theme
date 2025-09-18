# Shop ID Missing Error - 調査レポート

## 🚨 エラー詳細
```
Error: Shop ID is missing during update.
```

## 🔍 問題の原因分析

### エラー発生箇所 (studio-shops-plugin.php:166-167)
```php
// Validate shop_id for update mode
if ($is_update_mode && empty($shop_id)) {
    echo '<div class="error"><p>' . esc_html__('Error: Shop ID is missing during update.', 'studio-shops') . '</p></div>';
}
```

### 問題の構造

#### 1. 更新モード判定
```php
$is_update_mode = isset($_POST['update_mode']) && $_POST['update_mode'] === 'on';
```

#### 2. Shop ID取得
```php
$shop_id = isset($_POST['shop_id']) ? sanitize_text_field($_POST['shop_id']) : '';
```

#### 3. HTML フォーム構造
```html
<input type="checkbox" id="update-mode" name="update_mode"> Update Existing Shop
<select name="shop_id" id="shop-id-select">
    <option value="">Select a Shop</option>
    <!-- JavaScriptで動的に選択肢が追加される -->
</select>
```

### 🎯 根本原因

**チェックボックスとPOSTデータの不整合:**

1. **チェックボックスの仕様**: HTMLのcheckboxは`checked`状態の時のみPOSTデータに含まれる
   - チェック時: `$_POST['update_mode'] = 'on'`
   - 未チェック時: `$_POST['update_mode']`は存在しない

2. **現在のコード問題点**:
   - ユーザーが「Update Existing Shop」をチェック
   - ショップを選択せずに送信
   - `$_POST['update_mode'] = 'on'`で`$is_update_mode = true`
   - `$_POST['shop_id']`が空文字列でエラー発生

## 🛠️ 修正方針

### Option 1: バリデーション強化（推奨）
- フロントエンドとバックエンドの両方でバリデーション
- ユーザビリティを重視した段階的チェック

### Option 2: JavaScript必須化
- 更新モード時のショップ選択を必須に
- リアルタイムバリデーション

### Option 3: デフォルト値設定
- 更新モード時の安全なフォールバック

## 📋 推奨実装手順

### 1. フロントエンドバリデーション追加
```javascript
// フォーム送信前のチェック
if (updateMode.checked && !shopSelect.value) {
    alert('更新モードではショップを選択してください。');
    return false;
}
```

### 2. バックエンドエラーメッセージ改善
```php
if ($is_update_mode && empty($shop_id)) {
    echo '<div class="error"><p>更新モードではショップを選択してください。</p></div>';
    return; // 処理を中断
}
```

### 3. UI/UX改善
- ショップ未選択時の視覚的フィードバック
- 必須項目の明示

## 🔧 影響範囲

### 低リスク
- バリデーションロジックの改善のみ
- 既存データへの影響なし
- フロントエンド表示への影響なし

### 修正対象ファイル
- `studio-shops-plugin.php` (lines: 142, 166-167)
- JavaScript部分でのバリデーション追加

## ✅ 修正完了

### 実装した改善点

#### 1. フロントエンドバリデーション
```javascript
// フォーム送信前のリアルタイムチェック
form.addEventListener('submit', function(e) {
    if (updateModeCheckbox.checked && !shopSelect.value) {
        e.preventDefault();
        alert('更新モードではショップを選択してください。');
        shopSelect.focus();
        return false;
    }
});
```

#### 2. バックエンドエラーメッセージ改善
```php
if ($is_update_mode && empty($shop_id)) {
    echo '<div class="error"><p>更新モードではショップを選択してください。</p></div>';
    return; // 処理を中断してさらなるエラーを防止
}
```

#### 3. UI/UX改善
- 日本語エラーメッセージ
- 必須項目の明示（「ショップを選択してください（必須）」）
- 選択時の視覚的フィードバック（青いボーダーと背景色）
- フォーカス制御による使いやすさ向上

### 🎯 効果
- **エラー防止**: フォーム送信前に問題を検出
- **ユーザビリティ**: 親切なメッセージとガイダンス
- **処理安定性**: バックエンドでの適切なエラーハンドリング