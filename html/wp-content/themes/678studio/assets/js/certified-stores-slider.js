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

    // Get the number of original stores (not including clones)
    const storeCount = slides.length;

    // Determine slider configuration based on store count
    let sliderConfig = {
        // Layout
        fixedWidth: '25vw',    // 固定幅: 360px相当 (1440pxの25%)
        perMove: 1,            // 1枚ずつ移動
        gap: '-1px',           // ボーダーを重ねる（負のマージン）
        focus: 0,              // 左寄せ（左端固定）
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
    };

    // Configure based on store count
    if (storeCount >= 4) {
        // 4店舗以上: 無限ループ有効
        sliderConfig.type = 'loop';
        sliderConfig.rewind = false;
        sliderConfig.trimSpace = false;
    } else if (storeCount >= 2) {
        // 2-3店舗: ループ無効、端で停止
        sliderConfig.type = 'slide';
        sliderConfig.rewind = false;
        sliderConfig.trimSpace = true;
    } else {
        // 1店舗のみ: スライダー無効化、静的表示
        console.log('Only 1 store found, displaying statically without slider');

        // スライダー構造を解除して通常表示
        const track = sliderElement.querySelector('.splide__track');
        const cardsList = sliderElement.querySelector('.splide__list');

        if (track && cardsList) {
            // Splideクラスを削除
            sliderElement.classList.remove('splide');
            track.style.overflow = 'visible';
            cardsList.style.display = 'flex';
            cardsList.style.justifyContent = 'flex-start';
            cardsList.style.gap = '0';

            // 最後のカードに右ボーダーを追加
            const lastCard = slides[slides.length - 1]?.querySelector('.certified-store-card');
            if (lastCard) {
                lastCard.style.borderRight = '1px solid #F39556';
            }
        }

        // Hide arrow buttons for single store
        const arrows = document.querySelector('.certified-stores-list__arrows');
        if (arrows) {
            arrows.style.display = 'none';
        }
        return; // Exit without mounting slider
    }

    // Initialize Splide slider with dynamic configuration
    const slider = new Splide('#certified-stores-slider', sliderConfig);

    // Custom arrow buttons (now outside slider element)
    const prevBtn = document.querySelector('.certified-stores-list__arrow--left');
    const nextBtn = document.querySelector('.certified-stores-list__arrow--right');

    // Function to update arrow button states
    function updateArrowStates(index) {
        if (!prevBtn || !nextBtn) return;

        if (storeCount >= 4) {
            // 4店舗以上（ループモード）: 常に両方有効
            prevBtn.style.opacity = '1';
            prevBtn.style.pointerEvents = 'auto';
            nextBtn.style.opacity = '1';
            nextBtn.style.pointerEvents = 'auto';
        } else {
            // 2-3店舗（非ループモード）: 端で無効化
            // 最初のスライドで左ボタン無効
            if (index === 0) {
                prevBtn.style.opacity = '0.3';
                prevBtn.style.pointerEvents = 'none';
            } else {
                prevBtn.style.opacity = '1';
                prevBtn.style.pointerEvents = 'auto';
            }

            // 最後のスライドで右ボタン無効
            // スライド数から表示枚数（4枚）を引いた位置が最後
            const lastIndex = Math.max(0, storeCount - 4);
            if (index >= lastIndex) {
                nextBtn.style.opacity = '0.3';
                nextBtn.style.pointerEvents = 'none';
            } else {
                nextBtn.style.opacity = '1';
                nextBtn.style.pointerEvents = 'auto';
            }
        }
    }

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
    }


    // Handle slider events
    slider.on('mounted', function() {
        console.log('Certified stores slider mounted successfully');
        console.log('Store count:', storeCount, '| Slider type:', sliderConfig.type);

        // Add accessibility improvements
        const track = sliderElement.querySelector('.splide__track');
        if (track) {
            track.setAttribute('tabindex', '0');
            track.setAttribute('role', 'region');
            track.setAttribute('aria-label', '認定店舗一覧');
        }

        // Set initial arrow states
        updateArrowStates(0);
    });

    slider.on('moved', function(newIndex, prevIndex, destIndex) {
        // Update arrow button states based on current position
        updateArrowStates(newIndex);
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