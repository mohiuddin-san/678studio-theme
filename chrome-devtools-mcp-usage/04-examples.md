# Chrome DevTools MCP 実用例集

Chrome DevTools MCPを使った実際の開発・テストシナリオを紹介します。

## 🚀 Web開発ワークフロー

### シナリオ1: 新機能デプロイ後の品質チェック

```
ステップ1: 本番サイトの確認
"Navigate to https://yoursite.com"
"Take a screenshot of the homepage"

ステップ2: パフォーマンステスト
"Start performance trace with page reload"
"Stop performance trace and analyze insights"

ステップ3: エラーチェック
"Check for any console errors"
"List all network requests and identify failed ones"

ステップ4: モバイル対応確認
"Resize to mobile size (375x667)"
"Take a screenshot of the mobile layout"
"Test the mobile navigation menu"

ステップ5: フォーム機能テスト
"Fill out the contact form with test data"
"Submit the form and verify success message"
```

### シナリオ2: A/Bテストページの比較

```
ページA のテスト:
"Navigate to https://yoursite.com/version-a"
"Start performance trace"
"Take a screenshot"
"Measure Core Web Vitals"
"Stop performance trace"

ページB のテスト:
"Open new tab with https://yoursite.com/version-b"
"Start performance trace"
"Take a screenshot"
"Measure Core Web Vitals"
"Stop performance trace"

結果比較:
"Compare the performance metrics between both versions"
"Identify which version has better user experience"
```

## 🔧 デバッグ・トラブルシューティング

### シナリオ3: JavaScriptエラーの特定と修正

```
問題の特定:
"Navigate to the problematic page"
"Check for JavaScript errors in console"
"Take a snapshot of current page state"

詳細調査:
"Evaluate script: 'console.log(window.myApp)'"
"List all network requests to check for failed API calls"
"Take screenshot of any error messages visible to users"

修正後の確認:
"Refresh the page after code fixes"
"Verify no console errors remain"
"Test the functionality that was previously broken"
```

### シナリオ4: ネットワーク問題の診断

```
ネットワーク状況の確認:
"Navigate to the slow-loading page"
"List all network requests"
"Identify requests taking longer than 3 seconds"

低速環境でのテスト:
"Emulate slow 3G network conditions"
"Navigate to the homepage"
"Measure how long it takes to load"
"Take screenshots during loading process"

最適化の検証:
"Reset network conditions to no emulation"
"Clear cache and reload"
"Compare performance before and after optimization"
```

## 🎨 UI/UXテスト

### シナリオ5: レスポンシブデザインテスト

```
デスクトップ表示:
"Resize to desktop size (1920x1080)"
"Take a screenshot of the main page"
"Test navigation menu functionality"

タブレット表示:
"Resize to tablet size (768x1024)"
"Take a screenshot"
"Verify layout adapts correctly"
"Test touch-friendly elements"

モバイル表示:
"Resize to mobile size (375x667)"
"Take a screenshot"
"Test hamburger menu"
"Verify text readability and button sizes"

異なるモバイルサイズ:
"Resize to iPhone SE size (320x568)"
"Take a screenshot"
"Check for any layout breaking points"
```

### シナリオ6: ユーザージャーニーテスト

```
新規ユーザーフロー:
"Navigate to the homepage"
"Click on 'Sign Up' button"
"Fill registration form with test data"
"Submit the form"
"Verify welcome message appears"
"Take screenshots at each step"

購入フロー:
"Navigate to the product page"
"Click 'Add to Cart'"
"Go to shopping cart"
"Proceed to checkout"
"Fill shipping information"
"Take screenshots of each checkout step"
"Verify order confirmation"
```

## 📊 パフォーマンス最適化

### シナリオ7: Core Web Vitals改善

```
現状測定:
"Navigate to the target page"
"Start performance trace with reload"
"Stop trace and analyze LCP, FID, CLS metrics"
"Take note of current scores"

低速環境での測定:
"Emulate slow 4G network"
"Emulate 4x CPU slowdown"
"Repeat performance measurement"
"Identify performance bottlenecks"

改善後の検証:
"Reset emulation settings"
"Clear cache"
"Measure performance again"
"Compare with baseline metrics"
"Document improvements achieved"
```

### シナリオ8: 画像最適化の効果測定

```
最適化前:
"Navigate to image-heavy page"
"List all network requests"
"Filter requests by image type"
"Calculate total image payload size"
"Measure page load time"

最適化後:
"Clear cache and reload"
"List network requests again"
"Compare image sizes and formats"
"Measure new page load time"
"Calculate bandwidth savings"
```

## 🧪 自動テスト

### シナリオ9: 定期的なサイトヘルスチェック

```
毎日の自動チェック:
"Navigate to https://yoursite.com"
"Check for console errors"
"Verify main navigation links work"
"Test search functionality"
"Measure page load time"
"Take screenshot for visual comparison"

週次の詳細チェック:
"Perform full performance audit"
"Test all forms on the site"
"Check mobile responsiveness"
"Verify SSL certificate status"
"Test contact form submission"
```

### シナリオ10: 競合他社サイト分析

```
競合サイトの分析:
"Navigate to competitor website"
"Start performance trace"
"Take screenshots of key pages"
"Analyze their Core Web Vitals"
"List their technology stack"

比較レポート作成:
"Compare performance metrics with our site"
"Identify features they have that we don't"
"Note UX/UI differences"
"Document potential improvements"
```

## 🔍 SEO・アクセシビリティ

### シナリオ11: アクセシビリティチェック

```
基本的なチェック:
"Navigate to the main page"
"Evaluate script to check alt attributes: '$('img:not([alt])').length'"
"Test keyboard navigation"
"Check color contrast ratios"

詳細な検証:
"Test screen reader compatibility"
"Verify ARIA labels are present"
"Check focus indicators"
"Test with high contrast mode"
```

### シナリオ12: ページ速度とSEOの関係分析

```
SEO重要ページの測定:
"Navigate to top landing pages"
"Measure Core Web Vitals for each"
"Check mobile-friendliness"
"Verify meta tags are present"
"Measure Time to Interactive"

改善提案の作成:
"Identify pages with poor performance"
"Suggest specific optimizations"
"Prioritize changes by SEO impact"
"Create action plan for improvements"
```

## 💡 開発者向けTips

### 効率的な使い方

1. **バッチ処理**: 複数のテストを一度に実行
2. **条件分岐**: エラーが発生した場合の代替フロー
3. **結果保存**: スクリーンショットとメトリクスの記録
4. **自動化**: 定期的なチェックスクリプトの作成

### よく使うコマンド組み合わせ

```
# 完全なページ分析
"Open page → Performance trace → Screenshot → Console check → Network analysis"

# モバイル最適化テスト
"Mobile resize → Performance test → UI screenshot → Touch testing"

# デプロイ後確認
"Navigate → Error check → Performance → Visual verification"
```

これらの例を参考に、プロジェクトに最適なテストフローを構築してください。