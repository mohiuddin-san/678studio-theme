/**
 * Gallery Lightbox functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('galleryLightbox');
    if (!lightbox) return;
    
    const galleryItems = document.querySelectorAll('.gallery-grid__item');
    
    // 各ギャラリーアイテムにクリックイベントを追加
    galleryItems.forEach((item) => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const img = this.querySelector('img');
            if (img) {
                const src = img.dataset.fullImage || img.src;
                
                // Lightboxを開く
                const lightboxImage = lightbox.querySelector('.lightbox__image');
                if (lightboxImage) {
                    lightboxImage.src = src;
                    lightboxImage.alt = img.alt;
                }
                
                lightbox.classList.add('lightbox--active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // 閉じるボタン
    const closeBtn = lightbox.querySelector('.lightbox__close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            closeLightbox();
        });
    }
    
    // オーバーレイクリックで閉じる
    lightbox.addEventListener('click', function(e) {
        // 画像以外の場所をクリックしたら閉じる
        if (!e.target.classList.contains('lightbox__image')) {
            closeLightbox();
        }
    });
    
    // ESCキーで閉じる
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox.classList.contains('lightbox--active')) {
            closeLightbox();
        }
    });
    
    // Lightboxを閉じる共通関数
    function closeLightbox() {
        lightbox.classList.remove('lightbox--active');
        document.body.style.overflow = '';
    }
});