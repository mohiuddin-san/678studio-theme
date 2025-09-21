<?php
/**
 * Template Name: Auto Scale Test
 *
 * 統合vw()関数 + g()関数 組み合わせテスト
 * SP: 320px〜767px (380px基準), PC: 768px〜∞ (1440px基準)
 */

get_header();
?>

<div class="auto-scale-test-page">
  <div class="main-container">
    <h1>vw() + g() 統合テスト</h1>
    <p>統合vw()関数とg()関数の組み合わせテスト</p>

    <div class="info-panel">
      現在幅: <span id="current-width">計算中...</span><br>
      範囲: <span id="current-range">計算中...</span><br>
      設計幅: <span id="figma-base">計算中...</span><br>
      倍率: <span id="scale-value">計算中...</span>
    </div>

    <!-- vw() + g() 組み合わせサンプル -->
    <div class="vw-grid-sample">
      <h2>vw() + g() 組み合わせ</h2>

      <!-- グリッドカード -->
      <div class="grid-cards">
        <div class="card">
          <h3>カード1</h3>
          <p>vw()でサイズ設定<br>g()でグリッド配置</p>
        </div>
        <div class="card">
          <h3>カード2</h3>
          <p>レスポンシブ対応<br>自動スケール</p>
        </div>
        <div class="card">
          <h3>カード3</h3>
          <p>Figma値直接使用<br>シンプル記述</p>
        </div>
      </div>

      <!-- フレックスグリッド -->
      <div class="flex-grid">
        <div class="flex-item">
          <h4>アイテム1</h4>
          <p>テキスト</p>
        </div>
        <div class="flex-item">
          <h4>アイテム2</h4>
          <p>テキスト</p>
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