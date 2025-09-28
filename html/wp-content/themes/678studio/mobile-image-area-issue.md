# モバイル画像エリアの横スクロール問題

## 問題の概要
モバイル版のstore-basic-info-sectionで、画像エリアを画面全幅に表示しようとすると横スクロールが発生する問題。

## 制約条件

### 1. overflow-x: hidden の制約
- `overflow-x: hidden` を設定すると `position: sticky` が機能しなくなる
- 縦書きタイトルのsticky positioning が必須要件

### 2. 現在の構造
```scss
.store-basic-info-section-mobile {
    padding-left: m.vw-sp(20); // セクション左パディング

    &__container {
        padding: 0 m.vw-sp(20);
        padding-left: m.vw-sp(20); // 実質的に左40px、右20px
    }

    &__image-area {
        // ここで画面全幅にしたいが横スクロールが発生
    }
}
```

## 試行した解決策

### 1. calc()による幅計算
```scss
width: calc(100vw - m.vw-sp(20));
margin-left: calc(-1 * m.vw-sp(40));
```
**結果**: 右側に余白が残る

### 2. 100vw + マージン調整
```scss
width: 100vw;
margin-left: calc(-1 * m.vw-sp(40));
margin-right: calc(-1 * m.vw-sp(20));
```
**結果**: 横スクロール発生

### 3. leftプロパティによる位置調整
```scss
left: calc(-1 * m.vw-sp(40));
width: calc(100vw - m.vw-sp(20));
```
**結果**: 右側カット + 横スクロール

### 4. breakout-bgアプローチ
```scss
left: calc(-50vw + 50%);
width: 100vw;
```
**結果**: 横スクロール発生

## 根本原因
- パディング構造が複雑（セクション20px + コンテナ40px + 20px）
- 画面全幅（100vw）を使うとパディング分を考慮できない
- CSS計算の精度やブラウザレンダリングの違い

## 必要な要件
1. 画像エリアが画面左端（セクション左パディング分除く）から右端まで表示
2. 横スクロール発生なし
3. sticky positioning 維持
4. 動的画像対応

## 次のアプローチ候補
1. 固定ピクセル値の使用
2. 新しいmixin作成
3. 構造の根本的見直し