/**
 * FAQ Accordion with GSAP
 * よくある質問のアコーディオン機能
 */

class FAQAccordion {
  constructor() {
    // Debug: Initial setup
    if (window.wpDebugLogger) {
      window.wpDebugLogger.debug('FAQ Accordion: Constructor called', {
        timestamp: new Date().toISOString()
      });
    }

    this.items = document.querySelectorAll('.faq-item');
    
    // Debug: Check if FAQ items found
    if (window.wpDebugLogger) {
      window.wpDebugLogger.info('FAQ Accordion: Items found', {
        itemCount: this.items.length,
        items: Array.from(this.items).map(item => ({
          id: item.dataset.faqItem,
          hasToggle: !!item.querySelector('[data-faq-toggle]'),
          hasAnswer: !!item.querySelector('[data-faq-answer]'),
          hasIcon: !!item.querySelector('[data-faq-icon]')
        }))
      });
    }
    
    this.init();
  }

  init() {
    if (this.items.length === 0) {
      if (window.wpDebugLogger) {
        window.wpDebugLogger.warn('FAQ Accordion: No FAQ items found', {
          selector: '.faq-item'
        });
      }
      return;
    }

    // Debug: Check GSAP availability
    if (typeof gsap === 'undefined') {
      if (window.wpDebugLogger) {
        window.wpDebugLogger.error('FAQ Accordion: GSAP not loaded', {
          gsapExists: typeof gsap !== 'undefined',
          windowGsap: window.gsap
        });
      }
      return;
    }

    if (window.wpDebugLogger) {
      window.wpDebugLogger.info('FAQ Accordion: Starting initialization', {
        gsapVersion: gsap.version,
        itemCount: this.items.length
      });
    }

    // Initialize GSAP timeline for each FAQ item
    this.setupItems();
    this.bindEvents();
  }

  setupItems() {
    this.items.forEach((item, index) => {
      const answer = item.querySelector('[data-faq-answer]');
      const icon = item.querySelector('[data-faq-icon]');
      
      // Get natural height of content
      const content = answer.querySelector('.faq-item__a-content');
      
      // Temporarily make visible and get natural height
      gsap.set(answer, { 
        height: 'auto', 
        opacity: 1,
        visibility: 'visible',
        position: 'static'
      });
      
      // Force reflow to ensure accurate measurement
      answer.offsetHeight;
      const naturalHeight = answer.scrollHeight;
      
      // Debug: Log setup details
      if (window.wpDebugLogger) {
        window.wpDebugLogger.debug('FAQ Accordion: Item setup', {
          itemIndex: index,
          naturalHeight: naturalHeight,
          offsetHeight: answer.offsetHeight,
          scrollHeight: answer.scrollHeight,
          hasAnswer: !!answer,
          hasIcon: !!icon,
          hasContent: !!content
        });
      }
      
      // Now set initial state - answers closed
      gsap.set(answer, {
        height: 0,
        opacity: 0
      });

      // Store references for later use
      item._answer = answer;
      item._icon = icon;
      item._isOpen = false;
      item._naturalHeight = naturalHeight;
      
      // Create timeline for this item (paused initially)
      item._timeline = gsap.timeline({ paused: true });
      
      // Animation timeline using stored natural height
      item._timeline
        .to(answer, {
          height: naturalHeight,
          opacity: 1,
          duration: 0.4,
          ease: "power2.out"
        })
        .to(icon, {
          scale: 1.1,
          duration: 0.15,
          ease: "power2.out",
          yoyo: true,
          repeat: 1
        }, "<"); // Small scale animation for visual feedback
    });
  }

  bindEvents() {
    this.items.forEach((item, index) => {
      const toggle = item.querySelector('[data-faq-toggle]');
      
      if (!toggle) {
        if (window.wpDebugLogger) {
          window.wpDebugLogger.warn('FAQ Accordion: No toggle element found', {
            itemIndex: index,
            itemId: item.dataset.faqItem
          });
        }
        return;
      }

      if (window.wpDebugLogger) {
        window.wpDebugLogger.debug('FAQ Accordion: Binding event', {
          itemIndex: index,
          toggleElement: toggle.tagName,
          toggleText: toggle.textContent.trim()
        });
      }
      
      toggle.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('FAQ Debug: Click detected on item', index); // TEMPORARY DEBUG
        
        if (window.wpDebugLogger) {
          window.wpDebugLogger.info('FAQ Accordion: Click detected', {
            itemIndex: index,
            currentlyOpen: item._isOpen,
            target: e.target.tagName
          });
        }
        
        this.toggleItem(item);
      });
    });
  }

  toggleItem(item) {
    if (window.wpDebugLogger) {
      window.wpDebugLogger.debug('FAQ Accordion: Toggle item', {
        isCurrentlyOpen: item._isOpen,
        itemId: item.dataset.faqItem,
        hasTimeline: !!item._timeline
      });
    }
    
    if (item._isOpen) {
      this.closeItem(item);
    } else {
      // Close all other items first
      this.closeAllItems();
      this.openItem(item);
    }
  }

  openItem(item) {
    if (item._isOpen) return;

    // Recalculate height in case content has changed
    const answer = item._answer;
    gsap.set(answer, { height: 'auto', opacity: 1 });
    const currentHeight = answer.scrollHeight;
    gsap.set(answer, { height: 0, opacity: 0 });
    
    // Update timeline with current height
    item._timeline.clear();
    item._timeline
      .to(answer, {
        height: currentHeight,
        opacity: 1,
        duration: 0.4,
        ease: "power2.out"
      })
      .to(item._icon, {
        scale: 1.1,
        duration: 0.15,
        ease: "power2.out",
        yoyo: true,
        repeat: 1
      }, "<");

    // Change icon source to minus.svg at the start of animation
    const icon = item._icon;
    const iconSrc = icon.src;
    icon.src = iconSrc.replace('plus.svg', 'minus.svg');
    icon.alt = '閉じる';

    // Play the opening animation
    item._timeline.play();
    item._isOpen = true;
    item.classList.add('is-open');
  }

  closeItem(item) {
    if (!item._isOpen) return;

    // Change icon source back to plus.svg
    const icon = item._icon;
    const iconSrc = icon.src;
    icon.src = iconSrc.replace('minus.svg', 'plus.svg');
    icon.alt = '開く';

    // Reverse the animation
    item._timeline.reverse();
    item._isOpen = false;
    item.classList.remove('is-open');
  }

  closeAllItems() {
    this.items.forEach((item) => {
      if (item._isOpen) {
        this.closeItem(item);
      }
    });
  }

  // Public method to close all items
  closeAll() {
    this.closeAllItems();
  }

  // Public method to open specific item by index
  openItemByIndex(index) {
    if (this.items[index]) {
      this.closeAllItems();
      this.openItem(this.items[index]);
    }
  }
}

// Initialize FAQ Accordion when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Only initialize if we're on a page with FAQ items
  if (document.querySelector('.faq-item')) {
    console.log('FAQ Debug: Initializing FAQ Accordion'); // TEMPORARY DEBUG
    window.faqAccordion = new FAQAccordion();
    console.log('FAQ Debug: FAQ Accordion initialized', window.faqAccordion); // TEMPORARY DEBUG
  } else {
    console.log('FAQ Debug: No .faq-item elements found on page'); // TEMPORARY DEBUG
  }
});

// Export for potential external use
if (typeof module !== 'undefined' && module.exports) {
  module.exports = FAQAccordion;
}