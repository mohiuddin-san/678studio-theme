# Layout Techniques - 複雑なデザイン実装手法

## 🎯 Overview

複雑なデザインレイアウトを実装する際の効率的な手法をまとめています。特に **Flexbox** と **Transform** の組み合わせによる高度なレイアウト制御に焦点を当てています。

## 🔧 基本的な組み合わせパターン

### 1. Flexbox + Transform を使った中央配置

```scss
.container {
	display: flex;
	align-items: center;
	justify-content: center;

	.content {
		transform: translateY(-20px); // 微調整
	}
}
```

### 2. Position + Transform による精密配置

```scss
.hero-section {
	position: relative;

	&__overlay {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%); // 完全中央

		// または微調整
		transform: translate(-45%, -40%);
	}
}
```

## 🎨 実践的な応用例

### ヒーローセクション with オーバーレイ要素

```scss
.hero-section {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	aspect-ratio: 50/39;

	&__background {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		object-fit: cover;
		z-index: 1;
	}

	&__title {
		position: absolute;
		top: 20%;
		left: 10%;
		z-index: 2;
		transform: rotate(-5deg); // 角度調整
	}

	&__button {
		position: absolute;
		bottom: 15%;
		right: 10%;
		z-index: 2;
		transform: scale(1.1); // スケール調整
	}
}
```

### カード型レイアウト with Transform

```scss
.card-grid {
	display: flex;
	flex-wrap: wrap;
	gap: 20px;

	.card {
		flex: 1 1 calc(33.333% - 20px);
		position: relative;
		transition: transform 0.3s ease;

		&:hover {
			transform: translateY(-10px) scale(1.02);
		}

		&__overlay {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.7);
			display: flex;
			align-items: center;
			justify-content: center;
			opacity: 0;
			transform: scale(0.8);
			transition: all 0.3s ease;
		}

		&:hover &__overlay {
			opacity: 1;
			transform: scale(1);
		}
	}
}
```

## 🚀 高度なテクニック

### 1. 複数要素の連動アニメーション

```scss
.complex-layout {
	position: relative;

	&:hover {
		.element-1 {
			transform: translateX(20px) rotate(5deg);
		}

		.element-2 {
			transform: translateX(-20px) scale(1.1);
		}

		.element-3 {
			transform: translateY(-10px) rotateY(15deg);
		}
	}
}
```

### 2. 3D Transform の活用

```scss
.card-3d {
	perspective: 1000px;

	&__inner {
		position: relative;
		width: 100%;
		height: 100%;
		transform-style: preserve-3d;
		transition: transform 0.6s;

		&:hover {
			transform: rotateY(180deg);
		}
	}

	&__front,
	&__back {
		position: absolute;
		width: 100%;
		height: 100%;
		backface-visibility: hidden;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	&__back {
		transform: rotateY(180deg);
	}
}
```

### 3. Grid + Transform による複雑なレイアウト

```scss
.masonry-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;

	.item {
		position: relative;

		&:nth-child(2n) {
			transform: translateY(20px);
		}

		&:nth-child(3n) {
			transform: translateY(-20px);
		}

		&:nth-child(4n) {
			transform: translateY(40px);
		}
	}
}
```

## 🔄 Transform の組み合わせパターン

### 基本的な組み合わせ

```scss
// 移動 + 拡大
transform: translate(20px, 30px) scale(1.1);

// 回転 + 移動
transform: rotate(45deg) translate(50px, 0);

// 3D回転 + 拡大
transform: rotateX(30deg) rotateY(45deg) scale(1.2);

// 複数軸の変形
transform: translateX(20px) rotateZ(15deg) scaleX(1.1);
```

### 順序の重要性

```scss
// ❌ 期待した結果にならない
transform: scale(2) translateX(50px); // 100px移動される

// ✅ 正しい順序
transform: translateX(50px) scale(2); // 50px移動してから2倍
```

## 💡 実践的な Tips

### 1. パフォーマンス最適化

```scss
// GPU加速を促す
.optimized-element {
	transform: translateZ(0); // または translate3d(0, 0, 0)
	will-change: transform;
}

// アニメーション用
.animated-element {
	will-change: transform;

	&.animating {
		transition: transform 0.3s ease;
	}

	&:not(.animating) {
		will-change: auto; // メモリ節約
	}
}
```

### 2. レスポンシブ対応

```scss
.responsive-transform {
	transform: translateX(50px);

	@include m.mq(md, max) {
		transform: translateX(20px) scale(0.8);
	}

	@include m.mq(sm, max) {
		transform: translateX(10px) scale(0.6);
	}
}
```

### 3. 複雑なレイアウトのデバッグ

```scss
.debug-layout {
	// 開発中のみ表示
	&.debug {
		* {
			outline: 1px solid red;
		}

		[class*='__'] {
			outline: 2px solid blue;
		}
	}
}
```

## 🎯 678 Studio での実装例

### ヒーローセクション

```scss
.hero-section {
	position: relative;
	aspect-ratio: 50/39;
	overflow: hidden;

	&__image {
		width: 100%;
		height: 100%;
		object-fit: contain;
	}

	&__title-image {
		position: absolute;
		top: m.fs(90, 0.6);
		left: m.fs(150, 0.6);
		width: m.fs(285, 0.6);
		z-index: 2;
	}

	&__content {
		position: absolute;
		bottom: m.fs(100, 0.6);
		right: m.fs(86, 0.6);
		z-index: 2;
	}
}
```

### カメラボタン with シャドウエフェクト

```scss
.camera-button {
	position: relative;

	&::before {
		content: '';
		position: absolute;
		top: m.fs(5);
		left: m.fs(5);
		width: 100%;
		height: 100%;
		background: #fff;
		z-index: -1;
		border-radius: m.fs(15);
	}
}
```

## 🚨 注意点とベストプラクティス

### 1. Transform の制限事項

```scss
// ❌ インライン要素にはtransformが効かない
span {
	transform: translateX(20px); // 効果なし
}

// ✅ display プロパティを変更
span {
	display: inline-block; // または block, flex など
	transform: translateX(20px);
}
```

### 2. z-index の管理

```scss
// z-index を管理する変数
$z-indexes: (
	background: -1,
	content: 1,
	overlay: 2,
	modal: 10,
	notification: 100,
);

.element {
	z-index: map-get($z-indexes, overlay);
}
```

### 3. アクセシビリティ配慮

```scss
// アニメーション無効化の対応
@media (prefers-reduced-motion: reduce) {
	* {
		animation-duration: 0.01ms !important;
		animation-iteration-count: 1 !important;
		transition-duration: 0.01ms !important;
	}
}
```

## 📊 パフォーマンスチェックリスト

- [ ] `will-change` を適切に使用
- [ ] 不要な `transform` の削除
- [ ] GPU 加速の活用（`translateZ(0)`）
- [ ] 複雑なセレクターの最適化
- [ ] メディアクエリでの適切な調整

---

_このドキュメントは、複雑なデザインレイアウトの実装において、Flexbox と Transform を効果的に組み合わせるための実践的なガイドです。678 Studio プロジェクトでの具体的な実装例を含め、継続的にアップデートされます。_
