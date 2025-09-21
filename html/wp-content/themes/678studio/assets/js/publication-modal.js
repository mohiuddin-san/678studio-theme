/**
 * Publication Modal Handler
 * 掲載希望モーダルの開閉処理
 */

document.addEventListener('DOMContentLoaded', function() {
    const modalTrigger = document.getElementById('publication-modal-trigger');
    const modal = document.getElementById('publication-modal');
    const modalClose = document.getElementById('publication-modal-close');

    // モーダルを開く
    function openModal() {
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden'; // スクロールを無効化
    }

    // モーダルを閉じる
    function closeModal() {
        modal.classList.remove('is-open');
        document.body.style.overflow = ''; // スクロールを有効化
    }

    // トリガーボタンのクリックイベント
    if (modalTrigger) {
        modalTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    // 閉じるボタンのクリックイベント
    if (modalClose) {
        modalClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal();
        });
    }

    // モーダル背景クリックで閉じる
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // ESCキーで閉じる
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});