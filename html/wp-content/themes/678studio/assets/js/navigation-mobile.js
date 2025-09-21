/**
 * Mobile Navigation Handler (SP専用)
 * SP用の展開式メニュー制御
 */

document.addEventListener('DOMContentLoaded', function() {

    // WordPressデバッグロガーが利用可能になるまで待つ
    function safeLog(message, context) {
        if (typeof wpDebugLogger !== 'undefined') {
            wpDebugLogger.debug(message, context);
        } else {
            console.log('[DEBUG]', message, context);
        }
    }

    function safeError(message, context) {
        if (typeof wpDebugLogger !== 'undefined') {
            wpDebugLogger.error(message, context);
        } else {
            console.error('[DEBUG]', message, context);
        }
    }

    safeLog('Mobile navigation script loaded', {component: 'navigation-mobile'});

    // SP用展開メニュー要素を取得
    function getMobileElements() {
        return {
            toggleButton: document.getElementById('mobile-publication-toggle'),
            submenu: document.getElementById('mobile-publication-submenu')
        };
    }

    // 初期チェック
    let elements = getMobileElements();

    // 要素が見つからない場合のみエラーログ
    if (!elements.toggleButton || !elements.submenu) {
        safeError('Mobile publication elements not found', {component: 'navigation-mobile'});
        return; // SP専用なので、要素がなければ処理終了
    }

    // SP用サブメニューを開く
    function openSubmenu() {
        const elements = getMobileElements();
        if (!elements.submenu || !elements.toggleButton) {
            safeError('Mobile submenu elements not found', {component: 'navigation-mobile'});
            return;
        }

        safeLog('Opening mobile submenu', {component: 'navigation-mobile'});
        elements.submenu.classList.add('is-open');
        elements.toggleButton.classList.add('is-open');
        elements.toggleButton.setAttribute('aria-expanded', 'true');
        elements.submenu.setAttribute('aria-hidden', 'false');
    }

    // SP用サブメニューを閉じる
    function closeSubmenu() {
        const elements = getMobileElements();
        if (!elements.submenu || !elements.toggleButton) return;

        safeLog('Closing mobile submenu', {component: 'navigation-mobile'});
        elements.submenu.classList.remove('is-open');
        elements.toggleButton.classList.remove('is-open');
        elements.toggleButton.setAttribute('aria-expanded', 'false');
        elements.submenu.setAttribute('aria-hidden', 'true');
    }

    // SP用サブメニューをトグル
    function toggleSubmenu() {
        const elements = getMobileElements();

        if (!elements.submenu) {
            safeError('Mobile submenu element not found', {component: 'navigation-mobile'});
            return;
        }

        safeLog('Toggling mobile submenu', {
            component: 'navigation-mobile',
            currentState: elements.submenu.classList.contains('is-open')
        });

        if (elements.submenu.classList.contains('is-open')) {
            closeSubmenu();
        } else {
            openSubmenu();
        }
    }

    // イベント委譲を使用してクリックイベントを処理
    document.addEventListener('click', function(e) {
        // SP用掲載希望ボタンがクリックされた場合
        if (e.target.id === 'mobile-publication-toggle' || e.target.closest('#mobile-publication-toggle')) {
            safeLog('Mobile publication toggle button clicked', {component: 'navigation-mobile'});
            e.preventDefault();
            e.stopPropagation();
            toggleSubmenu();
            return;
        }
    });

    // ESCキーで閉じる
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const elements = getMobileElements();

            if (elements.submenu && elements.submenu.classList.contains('is-open')) {
                closeSubmenu();
            }
        }
    });
});