/**
 * Page Transitions Module
 * Beautiful page transitions with blur and fade effects
 */

document.addEventListener('DOMContentLoaded', function() {
    // 現代的で美しいアニメーション設定
    const TRANSITION_CONFIG = {
        duration: 1000, // 美しさを重視した時間設定
        blur: {
            initial: 0,
            max: 12 // より美しいブラー効果
        },
        scale: {
            initial: 1,
            out: 0.95, // 微妙なスケール効果
            in: 1.05
        },
        opacity: {
            hidden: 0,
            visible: 1
        },
        easing: {
            out: 'cubic-bezier(0.4, 0.0, 1, 1)', // Expo out
            in: 'cubic-bezier(0.0, 0.0, 0.2, 1)'  // Expo in
        }
    };

    // 全ページで統一された美しいアニメーション（ギャラリーページも同様）

    // 美しいオーバーレイ要素を作成
    function createTransitionOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'page-transition-overlay';
        overlay.innerHTML = `
            <div class="page-transition-content">
                <div class="page-transition-gradient"></div>
                <div class="page-transition-blur-layer"></div>
            </div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    }

    // 美しいページアウトアニメーション
    function pageOutAnimation() {
        return new Promise((resolve) => {
            const overlay = createTransitionOverlay();
            const content = document.querySelector('.main-content') || document.body;
            
            // オーバーレイの初期状態
            overlay.style.opacity = '0';
            overlay.style.display = 'flex';
            
            // 美しい段階的アニメーション
            requestAnimationFrame(() => {
                // Phase 1: コンテンツをゆっくりとブラー・スケール・フェード
                content.style.transition = `
                    filter ${TRANSITION_CONFIG.duration * 0.8}ms ${TRANSITION_CONFIG.easing.out},
                    opacity ${TRANSITION_CONFIG.duration * 0.8}ms ${TRANSITION_CONFIG.easing.out},
                    transform ${TRANSITION_CONFIG.duration * 0.8}ms ${TRANSITION_CONFIG.easing.out}
                `;
                
                content.style.filter = `blur(${TRANSITION_CONFIG.blur.max}px)`;
                content.style.opacity = '0.1';
                content.style.transform = `scale(${TRANSITION_CONFIG.scale.out})`;
                
                // Phase 2: オーバーレイの美しいフェードイン（少し遅らせて）
                setTimeout(() => {
                    overlay.style.transition = `opacity ${TRANSITION_CONFIG.duration * 0.6}ms ${TRANSITION_CONFIG.easing.out}`;
                    overlay.style.opacity = '1';
                }, TRANSITION_CONFIG.duration * 0.2);
            });
            
            // 美しさを重視した完了タイミング
            setTimeout(resolve, TRANSITION_CONFIG.duration * 0.9);
        });
    }

    // 美しいページインアニメーション
    function pageInAnimation() {
        return new Promise((resolve) => {
            const overlay = document.querySelector('.page-transition-overlay');
            const content = document.querySelector('.main-content') || document.body;
            
            // オーバーレイがある場合（ページ遷移時）
            if (overlay) {
                // 美しい段階的表示アニメーション
                requestAnimationFrame(() => {
                    // Phase 1: オーバーレイを優雅にフェードアウト
                    overlay.style.transition = `opacity ${TRANSITION_CONFIG.duration * 0.4}ms ${TRANSITION_CONFIG.easing.out}`;
                    overlay.style.opacity = '0';
                    
                    // Phase 2: コンテンツの美しい登場（少し遅らせて）
                    setTimeout(() => {
                        content.style.transition = `
                            filter ${TRANSITION_CONFIG.duration * 0.9}ms ${TRANSITION_CONFIG.easing.in},
                            opacity ${TRANSITION_CONFIG.duration * 0.8}ms ${TRANSITION_CONFIG.easing.in},
                            transform ${TRANSITION_CONFIG.duration * 0.8}ms ${TRANSITION_CONFIG.easing.in}
                        `;
                        
                        content.style.filter = `blur(${TRANSITION_CONFIG.blur.initial}px)`;
                        content.style.opacity = '1';
                        content.style.transform = `scale(${TRANSITION_CONFIG.scale.initial})`;
                    }, TRANSITION_CONFIG.duration * 0.1);
                });
                
                // 美しいクリーンアップ
                setTimeout(() => {
                    if (overlay && overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                    content.style.transition = '';
                    content.style.filter = '';
                    content.style.opacity = '';
                    content.style.transform = '';
                    document.body.classList.add('page-ready');
                    resolve();
                }, TRANSITION_CONFIG.duration * 1.1);
            } else {
                // 初回ページ読み込み時（オーバーレイなし）
                requestAnimationFrame(() => {
                    content.style.transition = `
                        filter ${TRANSITION_CONFIG.duration * 0.9}ms ${TRANSITION_CONFIG.easing.in},
                        opacity ${TRANSITION_CONFIG.duration * 0.8}ms ${TRANSITION_CONFIG.easing.in},
                        transform ${TRANSITION_CONFIG.duration * 0.8}ms ${TRANSITION_CONFIG.easing.in}
                    `;
                    
                    content.style.filter = `blur(${TRANSITION_CONFIG.blur.initial}px)`;
                    content.style.opacity = '1';
                    content.style.transform = `scale(${TRANSITION_CONFIG.scale.initial})`;
                });
                
                // クリーンアップと準備完了状態の設定
                setTimeout(() => {
                    content.style.transition = '';
                    content.style.filter = '';
                    content.style.opacity = '';
                    content.style.transform = '';
                    document.body.classList.add('page-ready');
                    resolve();
                }, TRANSITION_CONFIG.duration * 0.9);
            }
        });
    }

    // ナビゲーションリンクにイベントリスナーを追加
    function attachTransitionEvents() {
        // 内部リンクを取得（外部リンクは除外）
        const internalLinks = document.querySelectorAll('a[href^="/"], a[href^="./"], a[href^="../"], a[href^="#"], a[href*="' + window.location.hostname + '"]');
        
        internalLinks.forEach(link => {
            // ハッシュリンク（アンカーリンク）は除外
            if (link.getAttribute('href').startsWith('#')) return;
            
            // メール・電話リンクは除外
            if (link.getAttribute('href').startsWith('mailto:') || link.getAttribute('href').startsWith('tel:')) return;
            
            // 既にイベントが追加されている場合はスキップ
            if (link.hasAttribute('data-transition-attached')) return;
            
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                
                // ページアウトアニメーション実行後にページ遷移
                pageOutAnimation().then(() => {
                    window.location.href = href;
                });
            });
            
            link.setAttribute('data-transition-attached', 'true');
        });
    }

    // ページ読み込み時のアニメーション
    function initPageTransitions() {
        // 安全な初期化とフォールバック
        const content = document.querySelector('.main-content') || document.body;
        
        // ページ読み込み完了時にインアニメーション実行
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                // 少し遅延してからアニメーション開始
                setTimeout(() => {
                    pageInAnimation().catch(() => {
                        // エラー時のフォールバック - 必ず表示する
                        content.style.filter = '';
                        content.style.opacity = '1';
                        content.style.transform = '';
                        document.body.classList.add('page-ready');
                    });
                }, 100);
            });
        } else {
            // 既に読み込み完了している場合
            setTimeout(() => {
                pageInAnimation().catch(() => {
                    // エラー時のフォールバック - 必ず表示する
                    content.style.filter = '';
                    content.style.opacity = '1';
                    content.style.transform = '';
                    document.body.classList.add('page-ready');
                });
            }, 50);
        }
        
        // ナビゲーションイベントを設定
        attachTransitionEvents();
        
        // 動的に追加される要素に対応
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    attachTransitionEvents();
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // 初期化
    initPageTransitions();

    // Back/Forward ボタン対応
    window.addEventListener('pageshow', function(event) {
        const content = document.querySelector('.main-content') || document.body;
        if (event.persisted) {
            pageInAnimation().catch(() => {
                // エラー時のフォールバック
                content.style.filter = '';
                content.style.opacity = '1';
                content.style.transform = '';
                document.body.classList.add('page-ready');
            });
        }
    });

    // ページ離脱前の処理
    window.addEventListener('beforeunload', function() {
        const overlay = document.querySelector('.page-transition-overlay');
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
    });

    // 緊急フォールバック - 3秒後に強制表示
    setTimeout(() => {
        if (!document.body.classList.contains('page-ready')) {
            const content = document.querySelector('.main-content') || document.body;
            content.style.filter = '';
            content.style.opacity = '1';
            content.style.transform = '';
            document.body.classList.add('page-ready');
            console.log('Page transitions: Emergency fallback activated');
        }
    }, 3000);
});

// Export for other modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { pageOutAnimation, pageInAnimation };
}