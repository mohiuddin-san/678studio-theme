<?php
/**
 * Template Name: レスポンシブ関数テストページ
 * 
 * シンプルな f() 関数テスト
 */

get_header(); ?>

<main class="test-responsive-page">
    
    <h1 class="test-title">🚀 f() 関数テスト</h1>
    
    <div class="test-box">
        <h3>f(800) - 自動スケーリング</h3>
        <p>
            width: f(800) だけで<br>
            PC:800px → SP:600px（自動）<br>
            大画面でも比例拡大！
        </p>
    </div>
    
    <div class="test-special">
        <h3>SP側だけ特別指定</h3>
        <p>
            width: f(600) + @include mq(md) { width: 100%; }<br>
            PC側は自動スケーリング、SP側は100%幅
        </p>
    </div>
    
    <div class="test-box">
        <h3>f(24, 18) - 明示的指定</h3>
        <p>
            font-size: f(24, 18)<br>
            PC:24px、SP:18px を明確に指定
        </p>
    </div>
    
    <!-- 3列グリッドテスト -->
    <div class="test-grid-container">
        <div class="grid-item item1">
            <h4>Item 1</h4>
            <p>通常のグリッドアイテム<br>z-index: 1</p>
        </div>
        
        <div class="grid-item item2">
            <h4>Item 2 (重なり)</h4>
            <p>Item1の上に重なっています<br>transform: translateY(f(-30, -20))<br>z-index: 2</p>
        </div>
        
        <div class="grid-item item3">
            <h4>Item 3</h4>
            <p>通常のグリッドアイテム<br>z-index: 1</p>
        </div>
    </div>

</main>

<?php get_footer(); ?>