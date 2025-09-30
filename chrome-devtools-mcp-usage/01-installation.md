# Chrome DevTools MCP インストール・セットアップ

## 📋 システム要件

- **Node.js**: 22.12.0以上
- **Chrome**: 最新安定版以上
- **npm**: Node.jsに含まれるパッケージマネージャー

## 🚀 インストール方法

### Claude Code

```bash
claude mcp add chrome-devtools npx chrome-devtools-mcp@latest
```

### VS Code / Copilot

```bash
code --add-mcp '{"name":"chrome-devtools","command":"npx","args":["chrome-devtools-mcp@latest"]}'
```

### Cursor

"Install in Cursor"ボタンを使用するか、手動設定：

```json
{
  "mcpServers": {
    "chrome-devtools": {
      "command": "npx",
      "args": ["chrome-devtools-mcp@latest"]
    }
  }
}
```

### 手動設定（他のMCPクライアント）

MCPクライアントの設定ファイルに以下を追加：

```json
{
  "mcpServers": {
    "chrome-devtools": {
      "command": "npx",
      "args": ["chrome-devtools-mcp@latest"]
    }
  }
}
```

## ⚙️ 設定オプション

### 基本オプション

- `--headless`: ヘッドレスモードで実行（デフォルト: false）
- `--isolated`: 一時的なユーザーデータディレクトリを作成
- `--executablePath`: カスタムChrome実行パスを指定

### 高度な設定

- `--browserUrl`: 既存のChromeインスタンスに接続
- `--channel`: Chromeチャンネルを選択（stable, canary, beta, dev）
- `--logFile`: デバッグログファイルのパス

### 設定例

```json
{
  "mcpServers": {
    "chrome-devtools": {
      "command": "npx",
      "args": [
        "chrome-devtools-mcp@latest",
        "--headless",
        "--isolated"
      ]
    }
  }
}
```

## 🧪 インストール確認

初回テストコマンド：

```
"Check the performance of https://developers.chrome.com"
```

正常にインストールされていれば、Chromeが自動起動してパフォーマンス分析が開始されます。

## 🔧 トラブルシューティング

### Node.jsバージョンエラー

```bash
# Node.jsバージョン確認
node --version

# 22.12.0未満の場合、アップデートが必要
```

### Chrome実行パスエラー

```json
{
  "args": [
    "chrome-devtools-mcp@latest",
    "--executablePath=/path/to/chrome"
  ]
}
```

### ポート競合エラー

Chromeが既に起動している場合は一度終了してから再実行してください。

## 📝 注意事項

- MCPサーバーは必要に応じて自動的にブラウザを起動します
- ヘッドレスモードでは一部の機能が制限される場合があります
- isolatedオプションを使用すると、既存のChromeデータには影響しません