# Studio Shop Manager 仕様書

## 概要
Studio Shop Managerは、写真スタジオの店舗情報と画像を管理するWordPressプラグインです。

## 主な機能

### 1. 店舗管理
- **新規店舗登録**: 店舗の基本情報と画像を登録
- **店舗更新**: 既存店舗の情報を更新
- **画像管理**: メインギャラリー画像とカテゴリー別画像の管理

### 2. 店舗情報
- **name**: 店舗名
- **address**: 住所
- **phone**: 電話番号
- **nearest_station**: 最寄り駅
- **business_hours**: 営業時間
- **holidays**: 定休日
- **map_url**: 地図埋め込みコード（Google Maps iframe）
- **company_email**: 会社メールアドレス

### 3. 画像管理
- **メインギャラリー画像**: 店舗の一般的な画像（複数可）
- **カテゴリー別画像**: カテゴリーごとに分類された画像
  - 既存カテゴリーから選択または新規作成
  - カテゴリーごとに複数画像アップロード可能

## データベース構造

### テーブル一覧
1. **studio_shops**: 店舗基本情報
2. **studio_shop_images**: メインギャラリー画像
3. **studio_shop_categories**: カテゴリーマスター
4. **studio_shop_catgorie_images**: カテゴリー別画像

## APIエンドポイント

### 1. GET /api/get_all_studio_shop.php
全店舗情報を取得（画像URL含む）

### 2. POST /api/studio_shop.php
新規店舗登録（メインギャラリー画像含む）

### 3. POST /api/category_image_uploader.php
カテゴリー別画像アップロード

### 4. POST /api/update_shop_details.php
店舗情報更新

### 5. POST /api/update_shop_category_images.php
カテゴリー別画像更新

### 6. DELETE /api/delete_category.php
カテゴリー削除

### 7. DELETE /api/delete_category_image.php
カテゴリー画像削除

### 8. POST /api/delete_shop_main_image.php
メインギャラリー画像削除

## 環境対応
- ローカル環境: `http://localhost:8080/api/`
- 本番環境: `https://678photo.com/api/`
- 自動環境判定により適切なAPIエンドポイントを使用

## 画像アップロード仕様
- Base64エンコードされた画像データを送信
- 対応フォーマット: PNG, JPG, JPEG, GIF
- 保存先: `/studio_shop_galary/` ディレクトリ
- ファイル名: `shop_{shop_id}_{timestamp}.{extension}`

## 管理画面
- WordPress管理画面の左メニュー「Studio Shops」から アクセス
- 新規登録/更新モードの切り替え機能
- リアルタイムプレビュー機能
- ドラッグ&ドロップによる画像アップロード対応

## 特徴
- トランザクション管理によるデータ整合性保証
- エラーハンドリングとロールバック機能
- デバッグログ出力機能
- レスポンシブ対応の管理画面UI