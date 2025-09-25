/**
 * Header Height Manager
 * ヘッダーの高さを動的に計測してCSS変数を更新
 * これにより全ページでヘッダー高さの変更に自動対応
 */

(function() {
    'use strict';

    /**
     * ヘッダーの高さを計測してCSS変数を更新
     */
    function updateHeaderHeight() {
        const header = document.querySelector('.header__container');
        if (!header) return;

        // 実際のヘッダー高さを取得
        const headerHeight = header.offsetHeight;

        // CSS変数を更新（全ページで利用可能）
        document.documentElement.style.setProperty('--header-height', `${headerHeight}px`);

        // デバッグ用（本番環境では削除）
        // console.log('Header height updated:', headerHeight + 'px');
    }

    /**
     * 初期化
     */
    function init() {
        // ページ読み込み時
        updateHeaderHeight();

        // ウィンドウリサイズ時（レスポンシブ対応）
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(updateHeaderHeight, 250);
        });

        // フォントロード完了後（ロゴサイズが変わる可能性）
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(updateHeaderHeight);
        }

        // 画像ロード完了後（ロゴ画像のサイズ確定）
        const logoImage = document.querySelector('.branding__image');
        if (logoImage) {
            if (logoImage.complete) {
                updateHeaderHeight();
            } else {
                logoImage.addEventListener('load', updateHeaderHeight);
            }
        }
    }

    // DOMContentLoaded時に初期化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

/**
 * 使用方法:
 * 1. このスクリプトを読み込むだけで自動的にヘッダー高さを計測
 * 2. CSS側では var(--header-height) を使用
 * 3. ヘッダーのデザインを変更しても自動的に全ページが調整される
 *
 * メリット:
 * - ヘッダー変更時に全ページを手動調整不要
 * - レスポンシブ対応（画面サイズ変更時も自動調整）
 * - ロゴ画像やフォント読み込み後も再計算
 */