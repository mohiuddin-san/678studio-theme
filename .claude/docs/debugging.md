# WordPress Debug System (Claude Code自動分析対応)

## 🎯 システム概要

Claude Codeが自立的にWordPressテーマの動作を分析・デバッグできる包括的なロギングシステム。

### 対象範囲
- **PHP**: functions.php、テンプレート、フック実行
- **JavaScript**: DOM操作、ユーザーアクション、Ajax通信
- **WordPress**: データベースクエリ、プラグイン競合、パフォーマンス
- **エラー**: 全レベルのエラー・警告の自動収集

## 🛠️ 実装計画

### 1. PHP デバッグログクラス
**ファイル**: `html/wp-content/themes/678studio/lib/debug-logger.php`

```php
<?php
class WordPressDebugLogger {
    private static $instance = null;
    private $log_dir;
    private $log_file;
    private $session_id;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function log($level, $message, $context = []) {
        // 構造化ログ出力
        // セッション追跡
        // メモリ使用量監視
    }
    
    public function info($message, $context = []) {}
    public function debug($message, $context = []) {}
    public function error($message, $context = []) {}
    public function warn($message, $context = []) {}
}

// グローバル関数
function wp_debug_log($level, $message, $context = []) {
    WordPressDebugLogger::getInstance()->log($level, $message, $context);
}
```

### 2. JavaScript デバッグログクラス
**ファイル**: `html/wp-content/themes/678studio/assets/js/debug-logger.js`

```javascript
class WordPressJSLogger {
    constructor() {
        this.logs = [];
        this.sessionId = this.generateSessionId();
        this.maxBufferSize = 50;
        this.flushInterval = 5000;
        this.init();
    }
    
    init() {
        // エラーキャッチ
        window.addEventListener('error', (e) => {
            this.error('JavaScript Error', {
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                stack: e.error?.stack
            });
        });
        
        // 自動フラッシュ
        setInterval(() => this.flush(), this.flushInterval);
    }
    
    trackUserAction(action, element, context = {}) {
        this.info('User Action', {
            action: action,
            element: {
                tagName: element.tagName,
                id: element.id,
                className: element.className
            },
            ...context
        });
    }
    
    async flush() {
        // サーバーにログを送信
        // localStorage バックアップ
    }
}

// グローバルインスタンス
window.wpDebugLogger = new WordPressJSLogger();
```

### 3. WordPress統合設定
**functions.php への追加**:

```php
// デバッグログシステムの初期化
require_once get_template_directory() . '/lib/debug-logger.php';

// Ajax エンドポイント
add_action('wp_ajax_wp_debug_log_js', 'handle_js_debug_logs');
add_action('wp_ajax_nopriv_wp_debug_log_js', 'handle_js_debug_logs');

// JavaScript ログ受信
function handle_js_debug_logs() {
    if (!wp_verify_nonce($_POST['nonce'], 'wp_debug_nonce')) {
        wp_die('Security check failed');
    }
    
    $logs = json_decode(stripslashes($_POST['logs']), true);
    $log_file = WP_CONTENT_DIR . '/debug-logs/js-debug-' . date('Y-m-d') . '.log';
    
    foreach ($logs as $log) {
        $formatted_log = json_encode($log, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($log_file, $formatted_log, FILE_APPEND | LOCK_EX);
    }
    
    wp_send_json_success('Logs saved');
}

// デバッグ情報をJavaScriptに渡す
add_action('wp_enqueue_scripts', 'enqueue_debug_scripts');
function enqueue_debug_scripts() {
    wp_enqueue_script('wp-debug-logger', 
        get_template_directory_uri() . '/assets/js/debug-logger.js', 
        ['jquery'], '1.0.0', true);
    
    wp_localize_script('wp-debug-logger', 'wpDebugAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_debug_nonce')
    ]);
}
```

## 📊 ログ分析システム

### 自動分析スクリプト
**ファイル**: `scripts/analyze-wp-logs.js`

```javascript
#!/usr/bin/env node
class WordPressLogAnalyzer {
    constructor(options = {}) {
        this.logDir = options.logDir || 'html/wp-content/debug-logs/';
        this.hours = options.hours || 24;
        this.level = options.level || 'ALL';
    }
    
    async analyze() {
        const phpLogs = this.readLogs('wp-debug-');
        const jsLogs = this.readLogs('js-debug-');
        
        const errors = [...phpLogs, ...jsLogs].filter(log => log.level === 'ERROR');
        const warnings = [...phpLogs, ...jsLogs].filter(log => log.level === 'WARN');
        
        console.log('📊 分析結果サマリー:');
        console.log(`📝 PHP ログ: ${phpLogs.length} 件`);
        console.log(`🌐 JS ログ: ${jsLogs.length} 件`);
        console.log(`❌ エラー: ${errors.length} 件`);
        console.log(`⚠️  警告: ${warnings.length} 件`);
        
        // 詳細分析
        this.analyzeErrors(errors);
        this.analyzeUserActions(jsLogs);
        this.analyzePerformance(phpLogs);
        this.generateRecommendations(errors, warnings);
    }
}
```

### 分析コマンド
```bash
# 基本分析
npm run wp-logs:analyze

# エラーのみ表示
npm run wp-logs:errors

# 直近1時間のログ
npm run wp-logs:summary

# 特定機能の分析
npm run wp-logs:component --component="theme_feature"

# 古いログの削除
npm run wp-logs:cleanup
```

## 🔄 開発ワークフロー

### 3-Step Process (必須)

#### Step 1: 🔍 LOG - 詳細ログ追加
```php
// PHP側
wp_debug_log('DEBUG', 'Feature started', [
    'feature' => 'user_action',
    'user_id' => get_current_user_id(),
    'page' => get_the_ID(),
    'timestamp' => current_time('mysql')
]);

wp_debug_log('INFO', 'Database query executed', [
    'query_type' => 'custom',
    'post_count' => $query->post_count,
    'execution_time' => $query_time
]);
```

```javascript
// JavaScript側
wpDebugLogger.debug('User interaction started', {
    element: 'button',
    action: 'click',
    page: window.location.pathname
});

wpDebugLogger.info('Ajax request completed', {
    url: ajaxurl,
    method: 'POST',
    response: response,
    duration: performance.now() - startTime
});
```

#### Step 2: 🧪 TEST - 徹底的動作確認
```bash
# 1. WordPressサイトでユーザーに動作テストを依頼
# 2. ログ収集・分析
npm run wp-logs:analyze

# 3. エラーチェック
npm run wp-logs:errors

# 4. 直近の動作確認
npm run wp-logs:summary
```

**テスト成功基準:**
- ✅ PHPエラー0件
- ✅ JavaScriptエラー0件
- ✅ 期待通りのユーザーフロー
- ✅ データベースクエリ正常実行
- ✅ ページロード性能OK

#### Step 3: 🧹 CLEAN - プロダクション用クリーンアップ
```php
// ❌ 削除対象のデバッグログ
wp_debug_log('DEBUG', 'Function entry', $debug_vars); // 削除

// ✅ 保持すべき本番ログ
wp_debug_log('ERROR', 'Database error', $error_details); // 保持
wp_debug_log('INFO', 'User action completed', $result); // 保持
```

## 🚫 禁止事項

**絶対に使用禁止:**
- `var_dump()`, `print_r()`, `echo` (デバッグ出力)
- `console.log()`, `console.error()` (JavaScript)
- エラーログなしでのテーマ修正
- ログ分析スキップでの機能完了宣言
- デバッグログ残留でのテーマ公開

## 🎯 専用分析機能

### テーマ固有分析
- **テンプレート階層**: 使用されたテンプレートファイルの追跡
- **フック実行**: add_action, add_filter の実行状況
- **プラグイン競合**: 他プラグインとの干渉検出
- **パフォーマンス**: データベースクエリ効率性

### ユーザー体験分析
- **ページロード時間**: サーバー・クライアント別計測
- **インタラクション**: ユーザーの操作パターン
- **エラー発生**: 具体的なエラー箇所とタイミング
- **レスポンシブ**: デバイス別動作確認

## 💡 実装の利点

1. **完全自動化**: Claude Codeによる画面なし完全デバッグ
2. **WordPress特化**: テーマ開発固有の問題を的確に検出
3. **リアルタイム**: 即座にログ収集・分析・フィードバック
4. **保守性**: クリーンなプロダクションコードの維持
5. **品質保証**: エラー0件での安定したテーマ公開

---

*この実装により、678 Studio WordPressテーマの開発・保守が飛躍的に効率化されます。*