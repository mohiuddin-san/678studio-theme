/**
 * Scroll Animations Module
 * GSAP ScrollTrigger を使用した汎用的なスクロールアニメーション
 */

document.addEventListener('DOMContentLoaded', function() {
    // GSAPとScrollTriggerの確認
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
        console.warn('GSAP or ScrollTrigger not loaded');
        return;
    }

    // ScrollTriggerプラグインを登録
    gsap.registerPlugin(ScrollTrigger);

    // アニメーション設定
    const ANIMATION_CONFIG = {
        duration: 1.2,
        initialBlur: 8, // pixels
        ease: "power2.out",
        triggerStart: "top bottom-=100",
        stagger: 0.2
    };

    /**
     * スクロールアニメーションを初期化
     */
    function initScrollAnimations() {
        const animateItems = document.querySelectorAll('.scroll-animate-item');
        
        if (animateItems.length === 0) return;

        // 初期状態を設定
        animateItems.forEach(item => {
            gsap.set(item, {
                opacity: 0,
                filter: `blur(${ANIMATION_CONFIG.initialBlur}px)`,
                y: 30,
                scale: 0.95
            });
        });

        // セクション別にアニメーションを設定
        const sections = document.querySelectorAll('[id*="section"], section[id]');
        
        sections.forEach(section => {
            const sectionItems = section.querySelectorAll('.scroll-animate-item');
            if (sectionItems.length === 0) return;

            // セクションのアニメーションタイムライン作成
            const tl = gsap.timeline({
                scrollTrigger: {
                    trigger: section,
                    start: ANIMATION_CONFIG.triggerStart,
                    toggleActions: "play none none none"
                }
            });

            // アイテムを delay 順にソート
            const sortedItems = Array.from(sectionItems).sort((a, b) => {
                const delayA = parseFloat(a.dataset.delay) || 0;
                const delayB = parseFloat(b.dataset.delay) || 0;
                return delayA - delayB;
            });

            // 各アイテムのアニメーションを追加
            sortedItems.forEach((item, index) => {
                const delay = parseFloat(item.dataset.delay) || (index * ANIMATION_CONFIG.stagger);
                
                tl.to(item, {
                    opacity: 1,
                    filter: "blur(0px)",
                    y: 0,
                    scale: 1,
                    duration: ANIMATION_CONFIG.duration,
                    ease: ANIMATION_CONFIG.ease
                }, delay);
            });
        });

        console.log(`Scroll animations initialized for ${animateItems.length} items`);
    }

    /**
     * リサイズ時にScrollTriggerを更新
     */
    function handleResize() {
        ScrollTrigger.refresh();
    }

    // リサイズ用のデバウンス処理
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleResize, 250);
    });

    // アニメーション初期化
    initScrollAnimations();

    // ページ読み込み完了後にScrollTriggerを更新
    window.addEventListener('load', () => {
        ScrollTrigger.refresh();
    });
});

// Export for other modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initScrollAnimations };
}