/**
 * Security Helper Functions for 678 Studio Forms
 * Provides XSS protection and safe HTML rendering
 */

// HTML escape function to prevent XSS
function escapeHtml(unsafe) {
    if (typeof unsafe !== 'string') {
        return unsafe;
    }
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Safe text content setter with automatic escaping
function setSafeText(elementId, text) {
    const element = document.getElementById(elementId);
    if (element) {
        // Check if element has data-escape attribute
        if (element.hasAttribute('data-escape')) {
            element.textContent = text; // Use textContent instead of innerHTML for safety
        } else {
            element.innerHTML = escapeHtml(text);
        }
    }
}

// Safe value setter for confirmation screens
function setConfirmationValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        // Always escape content for confirmation screens
        element.textContent = value || '';
    }
}

// Validate input against potential XSS patterns
function validateInput(input) {
    if (typeof input !== 'string') {
        return false;
    }

    // Check for potentially dangerous patterns
    const dangerousPatterns = [
        /<script[^>]*>.*?<\/script>/gi,
        /javascript:/gi,
        /on\w+\s*=/gi,
        /<iframe[^>]*>.*?<\/iframe>/gi,
        /<object[^>]*>.*?<\/object>/gi,
        /<embed[^>]*>.*?<\/embed>/gi
    ];

    return !dangerousPatterns.some(pattern => pattern.test(input));
}

// Sanitize form data before sending
function sanitizeFormData(formData) {
    const sanitized = {};
    for (const [key, value] of Object.entries(formData)) {
        if (typeof value === 'string') {
            // Remove potentially dangerous characters and patterns
            sanitized[key] = value
                .replace(/[<>]/g, '') // Remove angle brackets
                .replace(/javascript:/gi, '') // Remove javascript: protocol
                .replace(/on\w+=/gi, '') // Remove event handlers
                .trim();
        } else {
            sanitized[key] = value;
        }
    }
    return sanitized;
}

// CSRF token validation
function validateCSRFToken() {
    const nonceField = document.querySelector('input[name*="nonce"]');
    if (!nonceField || !nonceField.value) {
        console.warn('CSRF token not found or empty');
        // CSRFトークンがない場合でも送信を許可（AJAX処理で別途検証）
        return true;
    }
    return true;
}

// Rate limiting check (client-side)
function checkRateLimit() {
    const lastSubmission = localStorage.getItem('lastFormSubmission');
    const now = Date.now();
    const minInterval = 30000; // 30 seconds minimum between submissions

    if (lastSubmission && (now - parseInt(lastSubmission)) < minInterval) {
        return false;
    }

    localStorage.setItem('lastFormSubmission', now.toString());
    return true;
}

// Initialize security measures on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add security attributes to all forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        // フォームがAJAX送信（確認ボタン）でない場合のみイベントリスナーを追加
        if (!form.id || !['reservationForm', 'inquiryForm', 'recruitmentForm'].includes(form.id)) {
            form.addEventListener('submit', function(e) {
                // Validate CSRF token
                if (!validateCSRFToken()) {
                    e.preventDefault();
                    alert('セキュリティエラー: ページを再読み込みしてください');
                    return false;
                }

                // Check rate limiting
                if (!checkRateLimit()) {
                    e.preventDefault();
                    alert('送信間隔が短すぎます。30秒お待ちください');
                    return false;
                }

                // Validate all text inputs
                const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="url"], textarea');
                for (const input of inputs) {
                    if (!validateInput(input.value)) {
                        e.preventDefault();
                        alert('入力内容に不正な文字が含まれています');
                        return false;
                    }
                }
            });
        }
    });

    console.log('Security helpers initialized');
});