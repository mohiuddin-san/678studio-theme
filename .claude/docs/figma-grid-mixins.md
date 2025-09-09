# Design Grid Layout Mixins ドキュメント

## 概要
デザインツールからGrid layoutを簡単に実装できるSCSS mixinセットです。
デザインの数値をそのまま使用でき、自動的にレスポンシブ対応します。

## 主要なMixins

### 1. `design-grid` - メインのGrid作成mixin

```scss
@include design-grid(
  $cols: (500px, 80px, 1fr),   // カラム幅
  $rows: (100px, 200px, auto),  // 行高さ
  $gap: 20,                     // グリッドギャップ
  $responsive: true             // レスポンシブ対応（デフォルト: true）
);
```

### 2. `grid-item` - グリッドアイテムの配置

```scss
@include grid-item(
  $col-start: 1,
  $col-end: 3,
  $row-start: 1,
  $row-end: 4
);
```

### 3. `grid-area` - 短縮形での配置

```scss
@include grid-area(1, 3, 1, 4);  // col-start, col-end, row-start, row-end
```

### 4. `auto-layout` - デザインのオートレイアウトを再現

```scss
@include auto-layout(
  $direction: 'horizontal',  // または 'vertical'
  $gap: 20,
  $padding: 40,
  $align: center,
  $justify: flex-start
);
```

## 実例：About Sectionを新しいMixinで書き換え

### Before（従来の方法）
```scss
&__container {
  display: grid;
  grid-template-columns: m.fs(528, 0.5) m.fs(80, 0.5) 1fr;
  grid-template-rows: m.fs(48, 0.5) m.fs(88, 0.5) m.fs(306, 0.5) m.fs(406, 0.5) m.fs(72, 0.5);
  gap: 0;
}

&__left {
  grid-column: 1;
  grid-row: 1 / 4;
}
```

### After（新しいMixin使用）
```scss
&__container {
  @include design-grid(
    $cols: (528px, 80px, 1fr),
    $rows: (48px, 88px, 306px, 406px, 72px),
    $gap: 0
  );
}

&__left {
  @include grid-area(1, 2, 1, 4);
}
```

## Hero Sectionの例

### 従来の方法
```scss
.hero-section {
  &__grid-container {
    display: grid;
    grid-template-columns: 47% 53%;
    grid-template-rows: auto;
    align-items: center;
  }
  
  &__content {
    grid-column: 1;
    grid-row: 1;
  }
}
```

### 新しいMixinを使用
```scss
.hero-section {
  &__grid-container {
    @include design-grid(
      $cols: (47%, 53%),
      $rows: (auto),
      $gap: 0
    );
    align-items: center;
  }
  
  &__content {
    @include grid-area(1, 2, 1, 2);
  }
}
```

## 使い方のコツ

### 1. デザインツールから値を取得
1. デザインツールでレイアウトを選択
2. 右パネルでAuto LayoutまたはGrid設定を確認
3. 値をそのままmixinに入力

### 2. レスポンシブ対応
- `$responsive: true`（デフォルト）でpx値が自動的にレスポンシブ対応
- `fs()`関数を内部で使用し、画面サイズに応じて自動調整

### 3. 単位の扱い
- **px値**: 自動的にレスポンシブ化
- **%**: そのまま使用
- **fr**: そのまま使用（flexibleな領域）
- **auto**: そのまま使用（コンテンツに応じた高さ）

## メリット

1. **簡単**: デザインツールの値をそのまま使える
2. **自動レスポンシブ**: px値は自動的にレスポンシブ対応
3. **読みやすい**: 構造が明確
4. **メンテナンスしやすい**: デザイン変更時も値を変えるだけ

## 注意点

- mixinを使用する前に`@use 'base/mixins' as m;`でインポートが必要
- `fs()`関数と組み合わせて使用されるため、`$responsive: false`を指定しない限り自動的にレスポンシブになる