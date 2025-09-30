# Chrome DevTools MCP 基本的な使い方

## 🎯 基本コンセプト

Chrome DevTools MCPは、AIエージェントが自然言語でブラウザを操作できるツールです。コマンドを入力するだけで、複雑なブラウザ操作を自動実行できます。

## 🚀 基本的な操作方法

### 1. Webページを開く

```
"Open https://example.com"
```

### 2. ページのスクリーンショットを撮る

```
"Take a screenshot of the current page"
```

### 3. パフォーマンス分析

```
"Check the performance of https://yoursite.com"
```

### 4. フォーム入力

```
"Fill out the contact form with name 'John Doe' and email 'john@example.com'"
```

### 5. 要素をクリック

```
"Click the submit button"
```

## 📱 レスポンシブテスト

### モバイルサイズでテスト

```
"Resize the page to mobile size (375x667) and take a screenshot"
```

### タブレットサイズでテスト

```
"Resize to tablet size (768x1024) and check the layout"
```

## 🔍 デバッグ・検査

### コンソールエラーの確認

```
"Check for any console errors on this page"
```

### ネットワークリクエストの分析

```
"Show me all network requests made by this page"
```

### パフォーマンス問題の特定

```
"Analyze the performance and identify any issues"
```

## 🎨 UI/UXテスト

### ユーザーフローのテスト

```
"Navigate to the homepage, click on 'Products', then select the first item"
```

### アクセシビリティチェック

```
"Check this page for accessibility issues"
```

### 読み込み速度の測定

```
"Measure page load time and Core Web Vitals"
```

## 🌐 マルチページテスト

### 複数ページの比較

```
"Compare the performance of page A and page B"
```

### サイト全体のクロール

```
"Check the main navigation links for broken pages"
```

## 💡 実用的なワークフロー

### 1. 開発後のチェック

```
1. "Open http://localhost:3000"
2. "Take a screenshot of the homepage"
3. "Check for console errors"
4. "Test the contact form"
5. "Measure performance"
```

### 2. 本番サイトの監視

```
1. "Check the performance of https://yoursite.com"
2. "Verify all main navigation links work"
3. "Check for any JavaScript errors"
4. "Test mobile responsiveness"
```

### 3. 競合分析

```
1. "Analyze the performance of https://competitor.com"
2. "Take screenshots of their key pages"
3. "Check their Core Web Vitals scores"
4. "Compare with our site performance"
```

## 📊 結果の解釈

### パフォーマンスメトリクス

- **LCP (Largest Contentful Paint)**: 最大コンテンツの描画時間
- **FID (First Input Delay)**: 初回入力までの遅延
- **CLS (Cumulative Layout Shift)**: レイアウトのずれ

### 推奨される値

- LCP: 2.5秒以下
- FID: 100ミリ秒以下
- CLS: 0.1以下

## 🔧 トラブルシューティング

### よくある問題

1. **ページが読み込まれない**
   ```
   "Check if the page is accessible and reload if needed"
   ```

2. **要素が見つからない**
   ```
   "Take a snapshot to see the current page structure"
   ```

3. **パフォーマンステストが失敗**
   ```
   "Clear browser cache and retry the performance test"
   ```

## 📝 ベストプラクティス

1. **段階的なテスト**: 一度に複数の操作を実行するより、段階的に進める
2. **明確な指示**: 具体的で明確なコマンドを使用する
3. **結果の確認**: 各操作後にスクリーンショットや状態確認を行う
4. **エラーハンドリング**: 問題が発生した場合の対処法を準備する