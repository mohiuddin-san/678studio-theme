# WordPress Logs Management - 使用方法

## 基本的な使用方法

### 1. ログの確認
```bash
# プロジェクトディレクトリに移動
cd /Users/yoshiharajunichi/Desktop/works/inside/678/html/wp-content/themes/678studio

# 全てのログを表示
npm run wp-logs:analyze

# エラーのみ表示
npm run wp-logs:errors

# 直近1時間のサマリー
npm run wp-logs:summary

# コンポーネント別に表示
npm run wp-logs:component
```

### 2. ログのクリーンアップ
```bash
# 7日以上古いログファイルを削除
npm run wp-logs:cleanup
```

### 3. 開発時のデバッグ出力

**PHP側（functions.php等）:**
```php
// 基本的なログ出力
wp_log_debug('デバッグメッセージ', ['variable' => $value]);
wp_log_info('情報メッセージ');
wp_log_warning('警告メッセージ');
wp_log_error('エラーメッセージ', ['error_code' => 500]);

// WordPressコンテキスト付きログ
$logger = WordPressDebugLogger::getInstance();
$logger->log('ERROR', 'Custom error message', ['custom_data' => $data]);
```

**JavaScript側:**
```javascript
// 基本的なログ出力
WPDebugLogger.debug('デバッグメッセージ', {variable: value});
WPDebugLogger.info('情報メッセージ');
WPDebugLogger.warning('警告メッセージ');
WPDebugLogger.error('エラーメッセージ', {errorCode: 500});

// ユーザーアクション追跡
WPDebugLogger.trackUserAction(element, 'click');

// パフォーマンス追跡
WPDebugLogger.trackPageLoad();
```

## ログレベルの説明

| レベル | 用途 | 例 |
|--------|------|-----|
| `DEBUG` | 開発時の詳細情報 | 変数の値、関数の実行フロー |
| `INFO` | 一般的な情報 | ページロード、ユーザーアクション |
| `WARNING` | 警告（処理は継続） | 非推奨関数の使用、軽微な問題 |
| `ERROR` | エラー（処理に影響） | 例外、致命的な問題 |

## コマンドライン解析オプション

### 基本オプション
```bash
# 直接スクリプトを実行する場合
node /path/to/wp-logs-management/scripts/analyze-wp-logs.js [options]
```

### 利用可能なオプション
- `--help, -h`: ヘルプを表示
- `--errors-only`: エラーレベルのログのみ表示
- `--summary`: サマリー表示
- `--level LEVEL`: 特定レベルのログのみ表示（ERROR, WARNING, INFO, DEBUG）
- `--hours N`: 直近N時間のログを表示
- `--last N`: 最新N件のログエントリを表示
- `--component`: コンポーネント別にグループ化して表示
- `--cleanup`: 古いログファイルをクリーンアップ

### 使用例
```bash
# 直近2時間のエラーログを表示
node analyze-wp-logs.js --level ERROR --hours 2

# 最新50件のログエントリを表示
node analyze-wp-logs.js --last 50

# コンポーネント別にログを表示
node analyze-wp-logs.js --component
```

## ログファイルの構造

### PHPログファイル形式
```json
{
  "timestamp": "2025-09-20 12:00:00",
  "level": "ERROR",
  "session_id": "wp_debug_12345",
  "message": "エラーメッセージ",
  "context": {"key": "value"},
  "wp_context": {
    "is_admin": false,
    "current_user_id": 1,
    "current_theme": "678studio"
  },
  "memory_usage": 4194304,
  "backtrace": [...]
}
```

### JavaScriptログファイル形式
```json
{
  "timestamp": "2025-09-20T12:00:00.000Z",
  "level": "ERROR",
  "sessionId": "js_debug_12345",
  "message": "エラーメッセージ",
  "context": {"key": "value"},
  "url": "http://localhost:8080/page",
  "userAgent": "Mozilla/5.0...",
  "viewport": {"width": 1920, "height": 1080},
  "performance": {"now": 1234.56}
}
```

## トラブルシューティング

### よくある問題

1. **ログが表示されない**
   - ログディレクトリが存在するか確認
   - ファイルの権限を確認
   - WordPressのWP_DEBUGが有効か確認

2. **パフォーマンスの問題**
   - 定期的にログクリーンアップを実行
   - ログレベルを適切に設定
   - 大量のデバッグログ出力を避ける

3. **ログ解析スクリプトがエラーになる**
   - Node.jsのバージョンを確認
   - パッケージの依存関係を確認
   - ファイルパスが正しいか確認

### サポート
問題が解決しない場合は、プロジェクトの `.claude/docs/debugging.md` を参照するか、開発チームにお問い合わせください。