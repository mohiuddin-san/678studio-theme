/**
 * Desktop Publication Modal Handler (PC専用)
 * PC用の右からスライドするモーダル制御
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

    safeLog('Desktop publication modal script loaded', {component: 'navigation-desktop'});

    // PC用モーダル要素を取得
    function getDesktopModalElements() {
        return {
            trigger: document.getElementById('desktop-publication-modal-trigger'),
            modal: document.getElementById('desktop-publication-modal'),
            close: document.getElementById('desktop-publication-modal-close')
        };
    }

    // 初期チェック
    let elements = getDesktopModalElements();

    // 要素が見つからない場合のみエラーログ
    if (!elements.trigger || !elements.modal || !elements.close) {
        safeError('Desktop publication modal elements not found', {component: 'navigation-desktop'});
        return; // PC専用なので、要素がなければ処理終了
    }

    // PC用モーダルを開く
    function openModal() {
        const elements = getDesktopModalElements();
        if (!elements.modal) return;

        safeLog('Opening desktop publication modal', {component: 'navigation-desktop'});
        elements.modal.classList.add('is-open');
        document.body.style.overflow = 'hidden'; // スクロールを無効化
    }

    // PC用モーダルを閉じる
    function closeModal() {
        const elements = getDesktopModalElements();
        if (!elements.modal) return;

        safeLog('Closing desktop publication modal', {component: 'navigation-desktop'});
        elements.modal.classList.remove('is-open');
        document.body.style.overflow = ''; // スクロールを有効化
    }

    // イベント委譲を使用してクリックイベントを処理
    document.addEventListener('click', function(e) {
        // PC用掲載希望ボタンがクリックされた場合
        if (e.target.id === 'desktop-publication-modal-trigger' || e.target.closest('#desktop-publication-modal-trigger')) {
            safeLog('Desktop publication modal trigger clicked', {component: 'navigation-desktop'});
            e.preventDefault();
            e.stopPropagation();
            openModal();
            return;
        }

        // 閉じるボタンがクリックされた場合
        if (e.target.id === 'desktop-publication-modal-close' || e.target.closest('#desktop-publication-modal-close')) {
            safeLog('Desktop publication modal close clicked', {component: 'navigation-desktop'});
            e.preventDefault();
            e.stopPropagation();
            closeModal();
            return;
        }

        // モーダル背景クリックで閉じる
        const elements = getDesktopModalElements();
        if (elements.modal && e.target === elements.modal) {
            closeModal();
        }
    });

    // ESCキーで閉じる
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const elements = getDesktopModalElements();

            if (elements.modal && elements.modal.classList.contains('is-open')) {
                closeModal();
            }
        }
    });
});