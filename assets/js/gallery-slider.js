// Gallery Slider with GSAP
document.addEventListener('DOMContentLoaded', function() {
  // GSAPの読み込みを待つ
  if (typeof gsap === 'undefined') {
    setTimeout(function() {
      if (typeof gsap !== 'undefined') {
        initGallerySlider();
      }
    }, 100);
  } else {
    initGallerySlider();
  }
});

function initGallerySlider() {
  const gallerySlider = document.querySelector('.store-gallery__track');
  
  // 既に初期化されている場合はスキップ（重複実行を防ぐ）
  if (!gallerySlider || gallerySlider.dataset.initialized === 'true') {
    return;
  }
  
  // 初期化済みフラグを設定
  gallerySlider.dataset.initialized = 'true';
  
  const items = gsap.utils.toArray('.store-gallery__item');
  const originalCount = items.length;
  
  
  // アイテムを複製（無限ループのため1セット追加）
  items.forEach((item) => {
    const clone = item.cloneNode(true);
    gallerySlider.appendChild(clone);
  });
  
  // 各アイテムの幅とギャップを考慮
  const itemWidth = items[0].offsetWidth;
  const gap = 20;
  const totalWidth = originalCount * (itemWidth + gap);
  
  // GSAPアニメーション（速度をもっと遅く調整）
  const animation = gsap.to(gallerySlider, {
    x: -totalWidth,
    duration: originalCount * 6, // 各画像6秒で通過（よりゆっくり）
    ease: 'none',
    repeat: -1
  });
  
  
  // ホバー時の一時停止
  gallerySlider.addEventListener('mouseenter', () => {
    animation.pause();
  });
  
  gallerySlider.addEventListener('mouseleave', () => {
    animation.resume();
  });
  
  // ライトボックス機能を初期化
  initLightbox();
}

// ライトボックス機能
function initLightbox() {
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImage = lightbox.querySelector('.lightbox__image');
  const closeBtn = lightbox.querySelector('.lightbox__close');
  const overlay = lightbox.querySelector('.lightbox__overlay');
  
  
  // 画像クリックイベント（イベント委譲を使用）
  document.addEventListener('click', (e) => {
    if (e.target.closest('.store-gallery__item')) {
      const item = e.target.closest('.store-gallery__item');
      const img = item.querySelector('img');
      const fullImageSrc = img.dataset.fullImage || img.src;
      
      
      // ライトボックスを開く
      lightboxImage.src = fullImageSrc;
      lightboxImage.alt = img.alt;
      lightbox.classList.add('lightbox--active');
      document.body.style.overflow = 'hidden';
    }
  });
  
  // 閉じる機能
  const closeLightbox = () => {
    lightbox.classList.remove('lightbox--active');
    document.body.style.overflow = '';
  };
  
  // 閉じるボタンのクリック
  closeBtn.addEventListener('click', (e) => {
    closeLightbox();
  });
  
  // 背景クリック（画像以外の場所）で閉じる
  lightbox.addEventListener('click', (e) => {
    // クリックされた要素が画像でない場合のみ閉じる
    if (e.target === lightbox || e.target === overlay || e.target.classList.contains('lightbox__content')) {
      closeLightbox();
    }
  });
  
  // ESCキーで閉じる
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && lightbox.classList.contains('lightbox--active')) {
      closeLightbox();
    }
  });
}