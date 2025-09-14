/**
 * Viewport Controller
 * 320px以下のデバイスでも320pxの見た目を維持するためのviewport制御
 */

(function() {
    'use strict';

    function adjustViewport() {
        const windowWidth = window.innerWidth;
        let viewportMeta = document.querySelector('meta[name="viewport"]');

        console.log('Viewport Controller: window width =', windowWidth);

        // viewport metaタグが存在しない場合は作成
        if (!viewportMeta) {
            viewportMeta = document.createElement('meta');
            viewportMeta.name = 'viewport';
            document.head.appendChild(viewportMeta);
            console.log('Created viewport meta tag');
        }

        // 320px以下の場合は固定幅に設定
        if (windowWidth <= 320) {
            const content = 'width=320, user-scalable=yes';
            viewportMeta.setAttribute('content', content);
            console.log('Applied fixed 320px viewport:', content);

            // CSSでも強制的に320px最小幅を設定
            const htmlElement = document.documentElement;
            htmlElement.style.minWidth = '320px';
            document.body.style.minWidth = '320px';
        } else {
            // 320px以上の場合は通常のレスポンシブ
            const content = 'width=device-width, initial-scale=1';
            viewportMeta.setAttribute('content', content);
            console.log('Applied responsive viewport:', content);

            // 最小幅制限を解除
            const htmlElement = document.documentElement;
            htmlElement.style.minWidth = '';
            document.body.style.minWidth = '';
        }
    }

    // 即座に実行
    adjustViewport();

    // DOM読み込み完了後にも実行
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', adjustViewport);
    }

    // リサイズ時にも実行（遅延なしで即座に）
    window.addEventListener('resize', adjustViewport);
    window.addEventListener('orientationchange', function() {
        setTimeout(adjustViewport, 50);
    });

    // 定期的にチェック（初期表示で確実に適用するため）
    let checkCount = 0;
    const intervalCheck = setInterval(function() {
        checkCount++;
        adjustViewport();
        if (checkCount > 10) {
            clearInterval(intervalCheck);
        }
    }, 100);

})();