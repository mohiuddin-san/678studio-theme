# Studio Detail ページ スライダー実装計画

## 📸 nii-photo.com スライダーのデザイン分析

### 技術仕様
- **ライブラリ**: Splide.js v4.1.4 (CDN経由)
- **画像形式**: WebP (パフォーマンス最適化)
- **画像枚数**: 10枚
- **HTML構造**: Splideの標準構造を採用

### デザイン特徴
1. **レイアウト**
   - フルワイド表示
   - 縦型の画像が中心
   - 自動再生（ループ）
   - フェード/スライドトランジション

2. **ビジュアル要素**
   - ミニマルなデザイン
   - ナビゲーションコントロールは非表示
   - 画像のみのクリーンな表示
   - キャッチコピー「気軽に、でもちゃんと。」が別要素として配置

3. **レスポンシブ対応**
   - PC/SPで同じ画像使用
   - SPでは画像サイズ自動調整

## 🎯 678 Studio Detail ページへの実装計画

### 1. 実装位置
```
[Header]
↓
[NEW: Hero Slider Section] ← ここに追加
↓
[Store Hero Section]
↓
[その他のコンテンツ]
```

### 2. 技術実装

#### A. 必要なファイル
```
1. /template-parts/sections/studio/hero-slider.php (新規作成)
2. /assets/scss/sections/studio/_hero-slider.scss (新規作成)
3. /assets/js/studio-slider.js (新規作成)
4. page-studio-detail.php (更新)
```

#### B. Splide.js の導入
```php
// functions.php に追加
function enqueue_splide_assets() {
    if (is_page_template('page-studio-detail.php')) {
        // Splide CSS
        wp_enqueue_style(
            'splide-css',
            'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css',
            array(),
            '4.1.4'
        );

        // Splide JS
        wp_enqueue_script(
            'splide-js',
            'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js',
            array(),
            '4.1.4',
            true
        );

        // Custom slider initialization
        wp_enqueue_script(
            'studio-slider-js',
            get_template_directory_uri() . '/assets/js/studio-slider.js',
            array('splide-js'),
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_splide_assets');
```

### 3. HTML構造案

```php
<!-- hero-slider.php -->
<section class="studio-hero-slider">
    <div class="studio-hero-slider__container">
        <div id="studio-gallery-slider" class="splide" role="group" aria-label="店舗ギャラリー">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php foreach ($gallery_images as $image): ?>
                    <li class="splide__slide">
                        <img src="<?php echo esc_url($image); ?>"
                             alt="店舗ギャラリー画像"
                             class="studio-hero-slider__image">
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
```

### 4. JavaScript設定

```javascript
// studio-slider.js
document.addEventListener('DOMContentLoaded', function() {
    const sliderElement = document.getElementById('studio-gallery-slider');

    if (sliderElement) {
        new Splide('#studio-gallery-slider', {
            type: 'fade',        // フェードトランジション
            perPage: 1,          // 1枚ずつ表示
            autoplay: true,      // 自動再生
            interval: 4000,      // 4秒間隔
            speed: 1000,         // トランジション速度
            pauseOnHover: true,  // ホバーで一時停止
            pauseOnFocus: true,  // フォーカスで一時停止
            resetProgress: false,
            arrows: false,       // 矢印なし（nii-photo風）
            pagination: true,    // ページネーション表示
            lazyLoad: 'nearby',  // 遅延読み込み
            keyboard: true,      // キーボード操作対応
            rewind: true,        // ループ再生

            // レスポンシブ設定
            breakpoints: {
                768: {
                    arrows: false,
                    pagination: true
                }
            }
        }).mount();
    }
});
```

### 5. SCSS スタイル案

```scss
// _hero-slider.scss
@use '../../base/mixins' as m;
@use '../../base/variables' as v;

.studio-hero-slider {
    width: 100%;
    position: relative;
    background: #f8f8f8;

    &__container {
        width: 100%;
        max-width: m.vw(1440);
        margin: 0 auto;
        position: relative;
    }

    // Splide カスタマイズ
    .splide {
        &__track {
            height: m.vw(600); // PCビュー高さ

            @include m.mq(md) {
                height: m.vw-sp(400); // SPビュー高さ
            }
        }

        &__slide {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }

    &__image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    // ページネーションカスタマイズ
    .splide__pagination {
        bottom: m.vw(20);

        @include m.mq(md) {
            bottom: m.vw-sp(15);
        }

        &__page {
            width: m.vw(8);
            height: m.vw(8);
            background: rgba(255, 255, 255, 0.5);
            margin: 0 m.vw(4);

            @include m.mq(md) {
                width: m.vw-sp(6);
                height: m.vw-sp(6);
                margin: 0 m.vw-sp(3);
            }

            &.is-active {
                background: white;
            }
        }
    }
}
```

### 6. データ取得の流れ

```php
// page-studio-detail.php での実装
$shop_data = fetch_studio_shop_by_id($shop_id);
$shop = $shop_data['shop'];

// ギャラリー画像の取得
$gallery_images = array();
if (!empty($shop['gallery_images'])) {
    $gallery_images = $shop['gallery_images'];
} else {
    // フォールバック：store-galleryセクションの画像を使用
    // 既存のギャラリー画像取得ロジックを流用
}

// スライダーセクションの読み込み
get_template_part('template-parts/sections/studio/hero-slider', null, [
    'gallery_images' => $gallery_images,
    'shop_name' => $shop['name']
]);
```

## 📋 実装ステップ

1. **Phase 1: 基本実装**
   - [ ] Splide.js の導入
   - [ ] hero-slider.php テンプレート作成
   - [ ] 基本的なスライダー動作確認

2. **Phase 2: スタイリング**
   - [ ] SCSS ファイル作成
   - [ ] レスポンシブデザイン調整
   - [ ] アニメーション調整

3. **Phase 3: 機能拡張**
   - [ ] 遅延読み込み実装
   - [ ] アクセシビリティ対応
   - [ ] パフォーマンス最適化

4. **Phase 4: テスト**
   - [ ] 各デバイスでの動作確認
   - [ ] 画像なし/少ない場合の処理
   - [ ] ページ読み込み速度確認

## 🎨 デザイン仕様

### ミニマル版（nii-photo.com風）
- 画像のみのクリーンな表示
- コントロール最小限（ページネーションのみ）
- 自動再生（4秒間隔）
- フェードトランジション

## 📝 注意事項

1. **パフォーマンス**
   - 画像の最適化（WebP形式推奨）
   - 遅延読み込みの実装
   - 適切な画像サイズの準備

2. **アクセシビリティ**
   - キーボード操作対応
   - スクリーンリーダー対応
   - 適切なARIA属性

3. **SEO**
   - 適切なalt属性
   - 構造化データの実装
   - Core Web Vitals への配慮