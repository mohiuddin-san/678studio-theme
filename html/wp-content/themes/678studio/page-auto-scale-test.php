<?php
/**
 * Template Name: Auto Scale Test
 *
 * 自動スケールmixinテスト
 * SP: 320px〜767px (380px基準), PC: 768px〜∞ (1440px基準)
 */

get_header();
?>

<div class="auto-scale-test-page">
	<!-- 情報パネル -->
	<div class="auto-scale-info-panel">
		<div>現在: <span id="current-width">-</span></div>
		<div>範囲: <span id="current-range">-</span></div>
		<div>基準: <span id="figma-base">-</span></div>
		<div>倍率: <span id="scale-value">-</span></div>
	</div>

	<!-- SP用コンテンツ (380px基準) -->
	<div class="sp-scale-container">
		<h1>SP Layout (380px基準)</h1>
		<p>320px〜767pxで自動スケール - Mixin版</p>
		<p style="background: red; color: white; padding: 10px;">
			現在幅: <span id="current-sp-width">計算中...</span><br>
			設計幅: 380px<br>
			倍率: <span id="current-sp-scale">計算中...</span>
		</p>

		<div class="test-box">
			<h2>テストボックス</h2>
			<p>このボックスは380pxで設計されています</p>
			<button class="test-btn">ボタン</button>
			<div style="width: 100px; height: 50px; background: yellow; margin: 10px 0; border: 2px solid black;">
				100px × 50px BOX
			</div>
			<div style="font-size: 16px; color: green;">16px テキスト</div>
		</div>
	</div>

	<!-- PC用コンテンツ (1440px基準) -->
	<div class="pc-scale-container">
		<h1>PC Layout (1440px基準)</h1>
		<p>768px以上で自動スケール - Mixin版</p>
		<p style="background: blue; color: white; padding: 20px;">
			現在幅: <span id="current-pc-width">計算中...</span><br>
			設計幅: 1440px<br>
			倍率: <span id="current-pc-scale">計算中...</span>
		</p>

		<div class="test-grid">
			<div class="test-card">
				<h3>カード1</h3>
				<p>1440px基準のカード</p>
			</div>
			<div class="test-card">
				<h3>カード2</h3>
				<p>自動スケール対応</p>
			</div>
			<div class="test-card">
				<h3>カード3</h3>
				<p>レスポンシブ</p>
			</div>
		</div>
	</div>
</div>

<script>
function updateInfo() {
	const width = window.innerWidth;
	const isMobile = width <= 767;

	document.getElementById('current-width').textContent = width + 'px';
	document.getElementById('current-range').textContent = isMobile ? 'SP' : 'PC';
	document.getElementById('figma-base').textContent = isMobile ? '380px' : '1440px';

	let scale;
	if (isMobile) {
		scale = width / 380;
	} else {
		scale = width / 1440;
	}

	document.getElementById('scale-value').textContent = scale.toFixed(3) + 'x';

	// 実際に適用されているtransformを確認
	const spContainer = document.querySelector('.sp-scale-container');
	const pcContainer = document.querySelector('.pc-scale-container');

	if (isMobile && spContainer) {
		const computedStyle = window.getComputedStyle(spContainer);
		console.log('SP Container transform:', computedStyle.transform);
		console.log('SP Container display:', computedStyle.display);

		// SP用の詳細表示
		const spWidthEl = document.getElementById('current-sp-width');
		const spScaleEl = document.getElementById('current-sp-scale');
		if (spWidthEl) spWidthEl.textContent = width + 'px';
		if (spScaleEl) spScaleEl.textContent = scale.toFixed(3) + 'x';
	} else if (!isMobile && pcContainer) {
		const computedStyle = window.getComputedStyle(pcContainer);
		console.log('PC Container transform:', computedStyle.transform);
		console.log('PC Container display:', computedStyle.display);

		// PC用の詳細表示
		const pcWidthEl = document.getElementById('current-pc-width');
		const pcScaleEl = document.getElementById('current-pc-scale');
		if (pcWidthEl) pcWidthEl.textContent = width + 'px';
		if (pcScaleEl) pcScaleEl.textContent = scale.toFixed(3) + 'x';
	}
}

updateInfo();
window.addEventListener('resize', updateInfo);
</script>

<?php get_footer(); ?>