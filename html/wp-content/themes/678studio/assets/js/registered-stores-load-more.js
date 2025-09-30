/**
 * Registered Stores Load More Functionality
 * SP版の登録店舗カードのロードモア機能
 */

document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.querySelector('.registered-stores-list-sp__load-more-btn');

    if (!loadMoreBtn) {
        return; // ボタンがない場合は終了
    }

    const totalCards = parseInt(loadMoreBtn.getAttribute('data-total'), 10);
    let currentlyVisible = 3; // 初期表示は3枚

    loadMoreBtn.addEventListener('click', function() {
        // 次の5枚のカードを表示
        const cardsToShow = 5;
        const cards = document.querySelectorAll('.registered-store-card-sp');

        let shownCount = 0;
        cards.forEach(function(card, index) {
            if (index >= currentlyVisible && index < currentlyVisible + cardsToShow) {
                card.style.display = 'flex';
                shownCount++;
            }
        });

        // 表示中のカード数を更新
        currentlyVisible += shownCount;

        // 全てのカードが表示されたらボタンを非表示
        if (currentlyVisible >= totalCards) {
            loadMoreBtn.classList.add('hidden');
        }
    });
});