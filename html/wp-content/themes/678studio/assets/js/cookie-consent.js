/**
 * Cookie Consent Manager for 678 Studio
 * Google Consent Mode v2 対応
 */

class CookieConsentManager {
  constructor() {
    this.CONSENT_KEY = '678_cookie_consent';
    this.BANNER_SHOWN_KEY = '678_banner_shown';
    this.init();
  }

  init() {
    // DOMが読み込まれたら実行
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setup());
    } else {
      this.setup();
    }
  }

  setup() {
    this.bindEvents();
    this.checkConsentStatus();
  }

  bindEvents() {
    // バナーボタンのイベント
    const acceptAllBtn = document.getElementById('cookie-accept-all');
    const necessaryOnlyBtn = document.getElementById('cookie-accept-necessary');
    const settingsBtn = document.getElementById('cookie-settings');

    // モーダルボタンのイベント
    const modalCloseBtn = document.getElementById('cookie-modal-close');
    const modalCancelBtn = document.getElementById('cookie-modal-cancel');
    const saveSettingsBtn = document.getElementById('cookie-save-settings');

    if (acceptAllBtn) {
      acceptAllBtn.addEventListener('click', () => this.acceptAll());
    }

    if (necessaryOnlyBtn) {
      necessaryOnlyBtn.addEventListener('click', () => this.acceptNecessaryOnly());
    }

    if (settingsBtn) {
      settingsBtn.addEventListener('click', () => this.showSettings());
    }

    if (modalCloseBtn) {
      modalCloseBtn.addEventListener('click', () => this.hideSettings());
    }

    if (modalCancelBtn) {
      modalCancelBtn.addEventListener('click', () => this.hideSettings());
    }

    if (saveSettingsBtn) {
      saveSettingsBtn.addEventListener('click', () => this.saveCustomSettings());
    }

    // モーダル背景クリックで閉じる
    const modal = document.getElementById('cookie-settings-modal');
    if (modal) {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          this.hideSettings();
        }
      });
    }
  }

  checkConsentStatus() {
    const consent = this.getStoredConsent();
    const bannerShown = localStorage.getItem(this.BANNER_SHOWN_KEY);

    if (consent) {
      // 既に同意設定がある場合、その設定を適用
      this.applyConsent(consent);
      this.hideBanner();
    } else if (!bannerShown) {
      // 初回訪問の場合、バナーを表示
      this.showBanner();
      localStorage.setItem(this.BANNER_SHOWN_KEY, 'true');
    }
  }

  showBanner() {
    const banner = document.getElementById('cookie-consent-banner');
    if (banner) {
      banner.style.display = 'block';
    }
  }

  hideBanner() {
    const banner = document.getElementById('cookie-consent-banner');
    if (banner) {
      banner.style.display = 'none';
    }
  }

  showSettings() {
    const modal = document.getElementById('cookie-settings-modal');
    if (modal) {
      modal.style.display = 'flex';

      // 現在の設定をチェックボックスに反映
      const consent = this.getStoredConsent();
      const analyticsCheckbox = document.getElementById('analytics-cookies');
      if (analyticsCheckbox && consent) {
        analyticsCheckbox.checked = consent.analytics_storage === 'granted';
      }
    }
  }

  hideSettings() {
    const modal = document.getElementById('cookie-settings-modal');
    if (modal) {
      modal.style.display = 'none';
    }
  }

  acceptAll() {
    const consent = {
      analytics_storage: 'granted',
      ad_storage: 'granted',
      ad_user_data: 'granted',
      ad_personalization: 'granted'
    };

    this.storeConsent(consent);
    this.applyConsent(consent);
    this.hideBanner();
    this.trackConsentEvent('accept_all');
  }

  acceptNecessaryOnly() {
    const consent = {
      analytics_storage: 'denied',
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied'
    };

    this.storeConsent(consent);
    this.applyConsent(consent);
    this.hideBanner();
    this.trackConsentEvent('necessary_only');
  }

  saveCustomSettings() {
    const analyticsCheckbox = document.getElementById('analytics-cookies');
    const analyticsGranted = analyticsCheckbox ? analyticsCheckbox.checked : false;

    const consent = {
      analytics_storage: analyticsGranted ? 'granted' : 'denied',
      ad_storage: analyticsGranted ? 'granted' : 'denied',
      ad_user_data: analyticsGranted ? 'granted' : 'denied',
      ad_personalization: analyticsGranted ? 'granted' : 'denied'
    };

    this.storeConsent(consent);
    this.applyConsent(consent);
    this.hideSettings();
    this.hideBanner();
    this.trackConsentEvent('custom_settings');
  }

  applyConsent(consent) {
    // Google Consent Mode v2 に設定を送信
    if (typeof gtag !== 'undefined') {
      gtag('consent', 'update', consent);
    }

    // dataLayerに直接送信（フォールバック）
    if (typeof dataLayer !== 'undefined') {
      dataLayer.push({
        event: 'consent_update',
        ...consent
      });
    }
  }

  storeConsent(consent) {
    const consentData = {
      ...consent,
      timestamp: new Date().toISOString(),
      version: '1.0'
    };
    localStorage.setItem(this.CONSENT_KEY, JSON.stringify(consentData));
  }

  getStoredConsent() {
    try {
      const stored = localStorage.getItem(this.CONSENT_KEY);
      if (stored) {
        const consent = JSON.parse(stored);
        // 30日間の有効期限をチェック
        const storedDate = new Date(consent.timestamp);
        const now = new Date();
        const daysDiff = (now - storedDate) / (1000 * 60 * 60 * 24);

        if (daysDiff < 30) {
          return consent;
        } else {
          // 期限切れの場合は削除
          localStorage.removeItem(this.CONSENT_KEY);
        }
      }
    } catch (e) {
      console.warn('Cookie consent data parsing error:', e);
    }
    return null;
  }

  trackConsentEvent(action) {
    // Google Analytics にイベントを送信
    if (typeof gtag !== 'undefined') {
      gtag('event', 'cookie_consent', {
        event_category: 'Privacy',
        event_label: action,
        value: 1
      });
    }

    // GTM dataLayer にイベントを送信
    if (typeof dataLayer !== 'undefined') {
      dataLayer.push({
        event: 'cookie_consent_action',
        consent_action: action,
        timestamp: new Date().toISOString()
      });
    }
  }

  // 外部から同意状況を確認するためのメソッド
  getConsentStatus() {
    return this.getStoredConsent();
  }

  // 同意をリセットするメソッド（テスト用）
  resetConsent() {
    localStorage.removeItem(this.CONSENT_KEY);
    localStorage.removeItem(this.BANNER_SHOWN_KEY);

    // デフォルト状態に戻す
    if (typeof gtag !== 'undefined') {
      gtag('consent', 'update', {
        analytics_storage: 'denied',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied'
      });
    }

    this.showBanner();
  }
}

// グローバルに公開（デバッグ用）
window.CookieConsentManager = CookieConsentManager;

// 自動初期化
window.cookieConsent = new CookieConsentManager();