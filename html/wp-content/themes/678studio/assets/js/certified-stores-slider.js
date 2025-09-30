/**
 * Certified Stores Slider Initialization
 * Using Splide.js for infinite card carousel on stores page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if Splide is available
    if (typeof Splide === 'undefined') {
        console.warn('Splide.js is not loaded');
        return;
    }

    const sliderElement = document.getElementById('certified-stores-slider');

    if (!sliderElement) {
        return; // No slider found, exit gracefully
    }

    // Check if slider has slides
    const slides = sliderElement.querySelectorAll('.splide__slide');
    if (slides.length === 0) {
        console.warn('No slides found in certified stores slider');
        return;
    }

    // Initialize Splide slider with infinite loop configuration
    const slider = new Splide('#certified-stores-slider', {
        // Core settings
        type: 'loop',          // 無限ループ
        rewind: false,         // ループモードなので不要

        // Layout
        fixedWidth: '25vw',    // 固定幅: 360px相当 (1440pxの25%)
        perMove: 1,            // 1枚ずつ移動
        gap: 0,                // ボーダーで区切るので隙間なし
        focus: 0,              // 左寄せ（左端固定）
        trimSpace: false,      // ループのため余白維持
        autoWidth: false,      // 自動幅計算を無効化

        // Navigation
        arrows: false,         // カスタム矢印を使用
        pagination: false,     // ページネーション非表示

        // Interaction
        drag: false,           // PCではドラッグ無効（ボタンのみ）
        keyboard: true,        // キーボード操作有効

        // Animation
        speed: 600,            // トランジション速度
        easing: 'cubic-bezier(0.25, 1, 0.5, 1)',

        // Accessibility
        live: true,

        // Responsive settings
        breakpoints: {
            768: {
                perPage: 1,     // SP: 1枚表示
                gap: '16px',    // SP: カード間に隙間
                drag: true,     // SP: スワイプ有効
                arrows: false   // SP: 矢印非表示
            }
        }
    });

    // Custom arrow buttons (now outside slider element)
    const prevBtn = document.querySelector('.certified-stores-list__arrow--left');
    const nextBtn = document.querySelector('.certified-stores-list__arrow--right');

    if (prevBtn && nextBtn) {
        // Left arrow click handler
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            slider.go('-1');
        });

        // Right arrow click handler
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            slider.go('+1');
        });

        // Note: ループモードなので矢印の無効化は不要
        // 左右どちらも常に有効
    }

    // Handle slider events
    slider.on('mounted', function() {
        console.log('Certified stores slider mounted successfully');

        // Add accessibility improvements
        const track = sliderElement.querySelector('.splide__track');
        if (track) {
            track.setAttribute('tabindex', '0');
            track.setAttribute('role', 'region');
            track.setAttribute('aria-label', '認定店舗一覧');
        }
    });

    slider.on('moved', function(newIndex, prevIndex, destIndex) {
        // Update slide info for debugging if needed
        // console.log('Moved to slide:', newIndex);
    });

    // Handle errors gracefully
    try {
        slider.mount();
    } catch (error) {
        console.error('Error mounting certified stores slider:', error);

        // Fallback: Show cards in grid layout if slider fails
        const splideTrack = sliderElement.querySelector('.splide__track');
        if (splideTrack) {
            splideTrack.style.display = 'none';
        }
        const cardsList = sliderElement.querySelector('.splide__list');
        if (cardsList) {
            cardsList.style.display = 'grid';
        }
    }
});