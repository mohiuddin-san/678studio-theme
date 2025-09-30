# Chrome DevTools MCP ツールリファレンス

Chrome DevTools MCPで利用可能な全23種類のツールを機能別に分類して説明します。

## 📱 入力自動化ツール (7種類)

### 1. `click` - 要素をクリック
```
"Click the submit button"
"Click on the navigation menu"
```
- 指定した要素をクリック
- ダブルクリックも対応

### 2. `drag` - ドラッグ操作
```
"Drag the item to the shopping cart"
"Drag the slider to increase volume"
```
- 要素を別の要素にドラッグ&ドロップ

### 3. `fill` - テキスト入力
```
"Fill the email field with 'user@example.com'"
"Enter 'password123' in the password field"
```
- 入力フィールドにテキストを入力

### 4. `fill_form` - フォーム一括入力
```
"Fill out the registration form with name, email, and phone"
```
- 複数のフォームフィールドを一度に入力

### 5. `handle_dialog` - ダイアログ処理
```
"Accept the confirmation dialog"
"Dismiss the alert popup"
```
- ブラウザのアラート、確認ダイアログを処理

### 6. `hover` - ホバー操作
```
"Hover over the dropdown menu"
"Mouse over the tooltip trigger"
```
- 要素の上にマウスカーソルを移動

### 7. `upload_file` - ファイルアップロード
```
"Upload the image file to the profile picture field"
```
- ファイル選択フィールドにファイルをアップロード

## 🧭 ナビゲーション自動化ツール (7種類)

### 1. `close_page` - ページを閉じる
```
"Close the current tab"
```
- 現在のブラウザページを閉じる

### 2. `list_pages` - ページ一覧表示
```
"Show me all open tabs"
```
- 開いているすべてのブラウザページを表示

### 3. `navigate_page` - ページ移動
```
"Go to https://example.com"
"Navigate to the contact page"
```
- 指定したURLに移動

### 4. `navigate_page_history` - 履歴ナビゲーション
```
"Go back to the previous page"
"Move forward in browser history"
```
- ブラウザの戻る/進む操作

### 5. `new_page` - 新しいページを開く
```
"Open a new tab with https://google.com"
```
- 新しいブラウザタブを開く

### 6. `select_page` - ページ切り替え
```
"Switch to the second tab"
```
- 開いているタブ間を切り替え

### 7. `wait_for` - 条件待機
```
"Wait for the page to load completely"
"Wait for the 'Success' message to appear"
```
- 特定の条件が満たされるまで待機

## 🎭 エミュレーションツール (3種類)

### 1. `emulate_cpu` - CPU性能シミュレーション
```
"Simulate slow CPU performance (4x slowdown)"
"Test page performance on low-end device"
```
- CPUスロットリングを適用して性能テスト

### 2. `emulate_network` - ネットワーク条件シミュレーション
```
"Simulate 3G network conditions"
"Test with slow network speed"
```
- ネットワーク速度制限でテスト（3G、4G等）

### 3. `resize_page` - ブラウザサイズ変更
```
"Resize to mobile size (375x667)"
"Change window size to tablet dimensions"
```
- レスポンシブデザインテスト用のサイズ変更

## ⚡ パフォーマンスツール (3種類)

### 1. `performance_analyze_insight` - パフォーマンス分析
```
"Analyze the Core Web Vitals"
"Give me detailed performance insights"
```
- パフォーマンストレースの詳細分析

### 2. `performance_start_trace` - トレース開始
```
"Start recording performance data"
```
- パフォーマンス測定を開始

### 3. `performance_stop_trace` - トレース終了
```
"Stop performance recording and show results"
```
- パフォーマンス測定を終了して結果表示

## 🌐 ネットワークツール (2種類)

### 1. `get_network_request` - ネットワークリクエスト詳細
```
"Show details of the API request to /api/users"
```
- 特定のネットワークリクエストの詳細情報

### 2. `list_network_requests` - ネットワークリクエスト一覧
```
"Show all network requests made by this page"
"List failed network requests"
```
- ページで発生したすべてのネットワークリクエスト

## 🐛 デバッグツール (4種類)

### 1. `evaluate_script` - JavaScript実行
```
"Execute 'document.title' in the browser console"
"Run custom JavaScript to check page state"
```
- ブラウザコンテキストでJavaScriptを実行

### 2. `list_console_messages` - コンソールログ表示
```
"Show me all console messages"
"Check for JavaScript errors"
```
- ブラウザコンソールのメッセージを取得

### 3. `take_screenshot` - スクリーンショット撮影
```
"Take a screenshot of the current page"
"Capture the header section only"
```
- ページ全体または特定要素のスクリーンショット

### 4. `take_snapshot` - ページ状態スナップショット
```
"Take a snapshot of the page structure"
"Capture current DOM state"
```
- ページの現在状態をテキスト形式で取得

## 🎯 組み合わせ使用例

### Webサイト完全チェック
```
1. "Navigate to https://yoursite.com"
2. "Take a screenshot of the homepage"
3. "Start performance trace"
4. "Navigate through main pages"
5. "Stop performance trace and analyze"
6. "Check all console messages"
7. "List network requests"
```

### モバイル対応テスト
```
1. "Resize to mobile size (375x667)"
2. "Navigate to the homepage"
3. "Test the mobile menu"
4. "Fill out the contact form"
5. "Take screenshots of key pages"
```

### パフォーマンス最適化
```
1. "Emulate slow 3G network"
2. "Emulate 4x CPU slowdown"
3. "Start performance trace with page reload"
4. "Analyze performance insights"
5. "Identify bottlenecks"
```

## 📝 使用上の注意

- 各ツールは自然言語で操作可能
- 複数のツールを組み合わせて複雑なテストフローを作成可能
- パフォーマンステストは実際のユーザー環境を模擬
- デバッグツールはリアルタイムでブラウザ状態を監視