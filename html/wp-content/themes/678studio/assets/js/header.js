/**
 * Header Mobile Menu Functionality
 */
document.addEventListener('DOMContentLoaded', function() {
  const hamburgerButton = document.querySelector('.header__hamburger');
  const mobileNavigation = document.querySelector('.header__navigation-mobile');
  const header = document.querySelector('.header');
  
  if (hamburgerButton && mobileNavigation && header) {
    hamburgerButton.addEventListener('click', function() {
      const isOpen = mobileNavigation.classList.contains('is-open');
      
      if (isOpen) {
        // メニューを閉じる
        mobileNavigation.classList.remove('is-open');
        header.classList.remove('is-open');
        hamburgerButton.setAttribute('aria-expanded', 'false');
        hamburgerButton.setAttribute('aria-label', 'メニューを開く');
        // ボディのスクロールを有効化
        document.body.style.overflow = '';
      } else {
        // メニューを開く
        mobileNavigation.classList.add('is-open');
        header.classList.add('is-open');
        hamburgerButton.setAttribute('aria-expanded', 'true');
        hamburgerButton.setAttribute('aria-label', 'メニューを閉じる');
        // ボディのスクロールを無効化
        document.body.style.overflow = 'hidden';
      }
    });

    // モバイルメニュー外をクリックした時に閉じる
    document.addEventListener('click', function(event) {
      if (!hamburgerButton.contains(event.target) && !mobileNavigation.contains(event.target)) {
        mobileNavigation.classList.remove('is-open');
        header.classList.remove('is-open');
        hamburgerButton.setAttribute('aria-expanded', 'false');
        hamburgerButton.setAttribute('aria-label', 'メニューを開く');
        document.body.style.overflow = '';
      }
    });

    // ESCキーでメニューを閉じる
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape' && mobileNavigation.classList.contains('is-open')) {
        mobileNavigation.classList.remove('is-open');
        header.classList.remove('is-open');
        hamburgerButton.setAttribute('aria-expanded', 'false');
        hamburgerButton.setAttribute('aria-label', 'メニューを開く');
        hamburgerButton.focus();
        document.body.style.overflow = '';
      }
    });
  }
});