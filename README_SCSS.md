# 678 Studio SCSS Setup

## 📁 テーマディレクトリでの作業

このファイルは `html/wp-content/themes/678studio/` ディレクトリに配置されています。

## 🚀 初期セットアップ

### 1. テーマディレクトリに移動
```bash
cd html/wp-content/themes/678studio
```

### 2. Node.js依存関係のインストール
```bash
npm install
```

### 3. 開発モードの開始
```bash
npm run dev
# または
gulp
```

## 📋 利用可能なコマンド

### 開発コマンド
```bash
# 開発モード（SCSS コンパイル + BrowserSync + ファイル監視）
npm run dev

# ファイル監視のみ（BrowserSync なし）
npm run watch

# SCSSコンパイルのみ
npm run sass

# 本番ビルド（圧縮・最適化）
npm run build
```

### Gulpコマンド
```bash
# 開発モード
gulp

# 本番ビルド
gulp build

# ファイル監視
gulp watch

# SCSSコンパイル
gulp sass
```

## 📂 ディレクトリ構成

```
678studio/                     # テーマルートディレクトリ
├── package.json              # Node.js設定
├── gulpfile.js               # Gulp設定
├── assets/
│   └── scss/
│       ├── base/
│       │   ├── _variables.scss
│       │   ├── _mixins.scss
│       │   └── _reset.scss
│       ├── components/
│       │   ├── _common.scss
│       │   └── _header.scss
│       └── style.scss         # メインエントリー
├── dist/
│   └── css/
│       ├── style.css         # 開発用CSS
│       └── style.css.map     # ソースマップ
├── style.css                 # WordPressテーマ用CSS（自動生成）
├── index.php                 # WordPressテンプレート
└── functions.php             # WordPressテーマ関数
```

## 🎯 開発ワークフロー

### 1. 開発環境の起動
```bash
# 1. Dockerコンテナを起動（プロジェクトルートで）
make up

# 2. テーマディレクトリに移動
cd html/wp-content/themes/678studio

# 3. 開発モード開始
npm run dev
```

### 2. アクセスURL
- **WordPress**: http://localhost:8080
- **BrowserSync**: http://localhost:3000 (自動リロード)

### 3. 開発中
- SCSSファイルを編集すると自動でCSSにコンパイル
- PHPファイルを変更すると自動でブラウザリロード
- エラーがあればデスクトップ通知

## 📝 SCSS使用例

### 変数の使用
```scss
// _variables.scss の変数を使用
.my-element {
    color: $brand-red;
    background-color: $background-color;
    padding: $spacing-md;
}
```

### レスポンシブデザイン
```scss
// fs() とメディアクエリを使用
.title {
    font-size: fs(24);
    
    @include mq($breakpoint-md, max) {
        font-size: fsm(20);
    }
}
```

### 新しいコンポーネントの追加
```scss
// components/_gallery.scss
.studio-gallery {
    &__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: $spacing-md;
    }
    
    &__item {
        aspect-ratio: 4/3;
        overflow: hidden;
        border-radius: 8px;
        
        &:hover {
            transform: translateY(-4px);
        }
    }
}
```

```scss
// style.scss に追加
@use 'components/gallery';
```

## 🔧 BrowserSync設定

- **プロキシ**: localhost:8080 (WordPress Docker)
- **ポート**: 3000
- **自動リロード**: SCSS、PHP、JSファイルの変更を監視
- **デスクトップ通知**: エラー時に通知

## 🚨 トラブルシューティング

### Node.jsが見つからない
```bash
# Node.jsがインストールされているか確認
node --version
npm --version

# インストールされていない場合
# https://nodejs.org/ からインストール
```

### コンパイルエラー
- SCSSファイルの構文エラーを確認
- インポートパスが正しいか確認
- 変数名のスペルミスを確認

### BrowserSyncが動作しない
```bash
# WordPressコンテナが起動しているか確認
make status

# ポート3000が使用されているか確認
lsof -i :3000
```

### CSSが反映されない
- ブラウザキャッシュをクリア
- WordPressキャッシュをクリア
- `style.css`がテーマルートに生成されているか確認

## 📦 本番デプロイ

### 1. 本番ビルド
```bash
npm run build
```

### 2. 生成されるファイル
- `dist/css/style.min.css` - 圧縮されたCSS
- `style.css` - WordPressテーマ用CSS

### 3. デプロイ
```bash
# テーマディレクトリから移動
cd ../../../../..

# 本番デプロイ
make deploy
```

## 🎨 678 Studio用クラス

### ヒーローセクション
```scss
.studio-hero {
    background: linear-gradient(135deg, $brand-red, $brand-blue);
    color: white;
    padding: $spacing-xl 0;
    text-align: center;
}
```

### ギャラリーグリッド
```scss
.studio-gallery {
    &__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: $spacing-md;
    }
}
```

### コンタクトフォーム
```scss
.studio-contact {
    &__form {
        max-width: 600px;
        margin: 0 auto;
    }
}
```

これでテーマディレクトリ内でGulpとSCSSを使用して開発できるようになりました！