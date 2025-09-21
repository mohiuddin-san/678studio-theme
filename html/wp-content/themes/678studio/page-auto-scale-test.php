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
  <div class="main-container">
    <h1>Auto Scale Test</h1>
    <p>レスポンシブ自動スケールテスト - SP: 380px基準 / PC: 1440px基準</p>

    <div class="info-panel">
      現在幅: <span id="current-width">計算中...</span><br>
      範囲: <span id="current-range">計算中...</span><br>
      設計幅: <span id="figma-base">計算中...</span><br>
      倍率: <span id="scale-value">計算中...</span>
    </div>

    <div class="test-content">
      <div class="test-box">
        <h2>テストボックス</h2>
        <p>この要素は画面幅に応じて自動スケールします</p>
        <button class="test-btn">ボタン</button>
      </div>

      <div class="test-grid">
        <div class="test-card">
          <h3>カード1</h3>
          <p>自動スケール対応</p>
        </div>
        <div class="test-card">
          <h3>カード2</h3>
          <p>レスポンシブ</p>
        </div>
        <div class="test-card">
          <h3>カード3</h3>
          <p>Figma値使用</p>
        </div>
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

  // デバッグ情報
  console.log(`Width: ${width}px, Range: ${isMobile ? 'SP' : 'PC'}, Scale: ${scale.toFixed(3)}x`);
}

updateInfo();
window.addEventListener('resize', updateInfo);
</script>

<?php get_footer(); ?>