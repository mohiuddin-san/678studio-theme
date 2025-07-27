/**
 * Page Transitions Module
 * Beautiful page transitions with blur and fade effects
 */

document.addEventListener('DOMContentLoaded', function() {
    // より洗練された固定的なアニメーション設定
    const TRANSITION_CONFIG = {
        duration: 800, // スムーズで快適な時間
        blur: {
            initial: 0,
            max: 8 // 適度なブラー効果
        },
        opacity: {
            hidden: 0,
            visible: 1
        },
        easing: {
            out: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)', // より自然なイージング
            in: 'cubic-bezier(0.55, 0.06, 0.68, 0.19)'   // より自然なイージング
        }
    };

    // 全ページで統一された美しいアニメーション（ギャラリーページも同様）

    // 固定的なオーバーレイ要素を作成（上から降りてこない）
    function createTransitionOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'page-transition-overlay';
        
        // 完全に固定的なスタイルを適用
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,248,248,0.98) 100%);
            backdrop-filter: blur(10px);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            pointer-events: none;
            transform: none !important;
            animation: none !important;
        `;
        
        overlay.innerHTML = `
            <div class="page-transition-content" style="
                width: 100%;
                height: 100%;
                background: transparent;
                transform: none !important;
                animation: none !important;
            ">
            </div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    }

    // 完全に固定的なページアウトアニメーション（位置移動なし）
    function pageOutAnimation() {
        return new Promise((resolve) => {
            const overlay = createTransitionOverlay();
            const content = document.querySelector('.main-content') || document.body;
            
            // 現在のスクロール位置を保存・固定
            const scrollY = window.scrollY;
            document.body.style.position = 'fixed';
            document.body.style.top = `-${scrollY}px`;
            document.body.style.width = '100%';
            
            // オーバーレイの初期状態
            overlay.style.opacity = '0';
            overlay.style.display = 'flex';
            
            // 完全に固定的なアニメーション（位置は一切動かさない）
            requestAnimationFrame(() => {
                // Phase 1: コンテンツを完全固定でブラー・フェードのみ
                content.style.transition = `
                    filter ${TRANSITION_CONFIG.duration * 0.7}ms ${TRANSITION_CONFIG.easing.out},
                    opacity ${TRANSITION_CONFIG.duration * 0.7}ms ${TRANSITION_CONFIG.easing.out}
                `;
                
                content.style.filter = `blur(${TRANSITION_CONFIG.blur.max}px)`;
                content.style.opacity = '0.15';
                content.style.transform = 'none'; // 強制的に変形なし
                
                // Phase 2: オーバーレイの自然なフェードイン
                setTimeout(() => {
                    overlay.style.transition = `opacity ${TRANSITION_CONFIG.duration * 0.5}ms ${TRANSITION_CONFIG.easing.out}`;
                    overlay.style.opacity = '1';
                }, TRANSITION_CONFIG.duration * 0.25);
            });
            
            // 完了タイミング
            setTimeout(resolve, TRANSITION_CONFIG.duration * 0.8);
        });
    }

    // 完全に固定的なページインアニメーション（位置移動なし）
    function pageInAnimation() {
        return new Promise((resolve) => {
            const overlay = document.querySelector('.page-transition-overlay');
            const content = document.querySelector('.main-content') || document.body;
            
            // 新ページでは body の固定を解除してスクロール位置をリセット
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            window.scrollTo(0, 0);
            
            // オーバーレイがある場合（ページ遷移時）
            if (overlay) {
                // 完全に固定的な表示アニメーション
                requestAnimationFrame(() => {
                    // Phase 1: オーバーレイを自然にフェードアウト
                    overlay.style.transition = `opacity ${TRANSITION_CONFIG.duration * 0.4}ms ${TRANSITION_CONFIG.easing.out}`;
                    overlay.style.opacity = '0';
                    
                    // Phase 2: コンテンツの完全固定登場（位置は絶対に動かさない）
                    setTimeout(() => {
                        content.style.transition = `
                            filter ${TRANSITION_CONFIG.duration * 0.8}ms ${TRANSITION_CONFIG.easing.in},
                            opacity ${TRANSITION_CONFIG.duration * 0.7}ms ${TRANSITION_CONFIG.easing.in}
                        `;
                        
                        content.style.filter = `blur(${TRANSITION_CONFIG.blur.initial}px)`;
                        content.style.opacity = '1';
                        content.style.transform = 'none'; // 強制的に変形なし
                    }, TRANSITION_CONFIG.duration * 0.1);
                });
                
                // スムーズなクリーンアップ
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
                }, TRANSITION_CONFIG.duration * 0.9);
            } else {
                // 初回ページ読み込み時（オーバーレイなし）
                requestAnimationFrame(() => {
                    content.style.transition = `
                        filter ${TRANSITION_CONFIG.duration * 0.8}ms ${TRANSITION_CONFIG.easing.in},
                        opacity ${TRANSITION_CONFIG.duration * 0.7}ms ${TRANSITION_CONFIG.easing.in}
                    `;
                    
                    content.style.filter = `blur(${TRANSITION_CONFIG.blur.initial}px)`;
                    content.style.opacity = '1';
                    content.style.transform = 'none'; // 強制的に変形なし
                });
                
                // クリーンアップと準備完了状態の設定
                setTimeout(() => {
                    content.style.transition = '';
                    content.style.filter = '';
                    content.style.opacity = '';
                    content.style.transform = '';
                    document.body.classList.add('page-ready');
                    resolve();
                }, TRANSITION_CONFIG.duration * 0.8);
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