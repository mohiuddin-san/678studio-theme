jQuery(document).ready(function($) {

    // Function to fetch fresh nonce
    function getFreshNonce() {
        return new Promise((resolve, reject) => {
            if (!siaes_ajax || !siaes_ajax.ajax_url || !siaes_ajax.page_id) {
                console.error('siaes_ajax is undefined or missing required properties');
                reject('Configuration error: AJAX settings not loaded');
                return;
            }
            $.ajax({
                url: siaes_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'siaes_get_fresh_nonce',
                    page_id: siaes_ajax.page_id
                },
                success: function(response) {
                    if (response.success && response.data.nonce) {
                        siaes_ajax.nonce = response.data.nonce;
                        resolve(response.data.nonce);
                    } else {
                        console.error('Failed to refresh nonce:', response.data);
                        reject('Failed to refresh nonce: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Nonce refresh failed:', xhr.status, error, xhr.responseText);
                    reject('Nonce refresh failed: ' + xhr.status + ' ' + error);
                }
            });
        });
    }

    // Function to validate form and show confirmation
    function validateAndShowConfirmation(formId) {
        var $form = $('#' + formId);
        var isValid = true;
        var missingFields = [];

        $form.find('[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('error');
                $(this).next('.error-message').show();
                missingFields.push($(this).attr('name') || $(this).attr('id') || 'unnamed field');
            } else {
                $(this).removeClass('error');
                $(this).next('.error-message').hide();
            }
        });

        // Check for shop-id (both forms use shop-id)
        var shopField = 'shop-id';
        // store-selectはフォームの外にあるため、グローバルに検索
        var $storeSelect = $('#store-select');
        var shopId = $storeSelect.val();
        
        // shop-id の取得が成功
        
        // 店舗が選択されているか確認
        if (!shopId || shopId === '') {
            isValid = false;
            missingFields.push(shopField);
            $storeSelect.addClass('error');
            // エラー表示要素もグローバルに検索（フォーム外にある）
            $('#store-error').show();
        } else {
            // 店舗が選択されている場合はエラーをクリア
            $storeSelect.removeClass('error');
            $('#store-error').hide();
        }

        if (!isValid) {
            console.error('Validation failed for form ' + formId + '. Missing fields:', missingFields);
            // エラーメッセージをより適切に表示
            var errorMessages = [];
            if (missingFields.includes('name')) errorMessages.push('お名前を入力してください');
            if (missingFields.includes('kana')) errorMessages.push('フリガナを入力してください');
            if (missingFields.includes('email')) errorMessages.push('メールアドレスを入力してください');
            if (missingFields.includes('shop-id')) errorMessages.push('店舗を選択してください');
            if (missingFields.includes('reservation_date')) errorMessages.push('撮影希望日を選択してください');
            if (missingFields.includes('reservation_time')) errorMessages.push('開始時間を選択してください');
            if (missingFields.includes('agreement')) errorMessages.push('個人情報の取り扱いについて同意してください');
            
            alert(errorMessages.join('\n'));
            return false;
        }

        // inquiry-form.jsの確認画面表示機能を使用
        if (formId === 'inquiryForm' && typeof window.showInquiryConfirmation === 'function') {
            window.showInquiryConfirmation();
        } else if (formId === 'reservationForm') {
            // 予約フォームの場合はreservation-form.jsに任せる（すでに処理済み）
            $('#formStep').hide();
            $('#confirmationStep').show();
        } else {
            // その他のフォームの場合の既存の処理
            // Populate confirmation step
            $('#confirmName').text($form.find('#name').val());
            $('#confirmKana').text($form.find('#kana').val());
            $('#confirmContact').text($form.find('#contact').val() || 'N/A');
            $('#confirmEmail').text($form.find('#email').val());
            $('#confirmStore').text($('#store-select option:selected').text());
            $('#confirmNotes').text($form.find('#notes').val() || 'N/A');

            if (formId === 'reservationForm') {
                $('#confirmDate').text($form.find('#reservation_date').val());
                $('#confirmTimeFrom').text($form.find('#reservation_time_from').val());
                $('#confirmTimeTo').text($form.find('#reservation_time_to').val());
            }

            $('#formStep').hide();
            $('#confirmationStep').show();
        }
        
        return true;
    }

    // Function to submit form
    function submitForm(formId, nonce) {
        var $form = $('#' + formId);
        var formData = $form.serializeArray();
        
        // shop-idが含まれていない場合、手動で追加（両フォーム対応）
        var shopIdFound = formData.some(function(item) { return item.name === 'shop-id'; });
        var shopIdEmpty = formData.some(function(item) { return item.name === 'shop-id' && item.value === ''; });
        
        if (!shopIdFound || shopIdEmpty) {
            var shopId = $('#store-select').val();
            if (shopId) {
                // 空のshop-idを削除
                formData = formData.filter(function(item) { return !(item.name === 'shop-id' && item.value === ''); });
                // 正しいshop-idを追加
                formData.push({ name: 'shop-id', value: shopId });
            }
        }
        
        formData.push({ name: 'action', value: 'siaes_submit_form' });
        formData.push({ name: 'page_id', value: siaes_ajax.page_id });
        formData.push({ name: 'nonce', value: nonce });


        var $submitButton = $('#submitButton');
        var originalButtonText = $submitButton.text();
        $submitButton.prop('disabled', true).text('Submitting...');

        $.ajax({
            url: siaes_ajax.ajax_url,
            type: 'POST',
            data: formData,
            timeout: 30000,
            success: function(response) {
                $submitButton.prop('disabled', false).text(originalButtonText);
                if (response.success) {
                    alert('Thank you! Your ' + (formId === 'reservationForm' ? 'reservation' : 'inquiry') + ' has been submitted.');
                    
                    // カスタムイベントを発火（inquiry-form.jsが処理）
                    $(document).trigger('siaes:submission:success', [response]);
                    
                    $form[0].reset();
                    $form.find('#store-select').val('');
                    $('#store-select').val(''); // Also reset global store-select
                    $('#confirmationStep').hide();
                    $('#formStep').show();
                } else {
                    console.error('Submission failed for ' + formId + ':', response.data);
                    alert('Error: ' + (response.data || 'Unknown error occurred'));
                    
                    // エラーイベントを発火
                    $(document).trigger('siaes:submission:error', [response]);
                }
            },
            error: function(xhr, status, error) {
                $submitButton.prop('disabled', false).text(originalButtonText);
                console.error('Submission error for ' + formId + ':', xhr.status, error, xhr.responseText);
                alert('Error submitting ' + (formId === 'reservationForm' ? 'reservation' : 'inquiry') + ': ' + xhr.status + ' ' + error);
            }
        });
    }

    // Handle form submission for both forms
    // 注意: 現在はreservation-form.jsとinquiry-form.jsで確認ボタンクリック時の処理を行っているため、
    // このイベントハンドラーは実際の最終送信時のみ呼ばれます
    $('#inquiryForm, #reservationForm').on('submit', function(e) {
        e.preventDefault();
        var formId = $(this).attr('id');
        
        // 確認画面が表示されている場合は実際の送信処理のため、バリデーションをスキップ
        var confirmationStep = $('#confirmationStep');
        if (confirmationStep.is(':visible')) {
            // 最終送信時はバリデーション不要
            return;
        }
        
        // フォールバック: 万が一確認画面表示前にここに来た場合のみバリデーション実行
        validateAndShowConfirmation(formId);
    });

    // Handle back button
    $('#backButton').on('click', function() {
        $('#confirmationStep').hide();
        $('#formStep').show();
    });

    // Handle final submission
    $('#submitButton').on('click', function() {
        var formId = $('#inquiryForm').length && $('#formStep').find('#inquiryForm').length ? 'inquiryForm' : 'reservationForm';
        getFreshNonce()
            .then(nonce => submitForm(formId, nonce))
            .catch(error => {
                console.error('Form submission aborted for ' + formId + ':', error);
                var formNonce = $('#' + formId + ' input[name="nonce"]').val();
                if (formNonce) {
                    submitForm(formId, formNonce);
                } else {
                    alert('Error: Unable to validate security token. Please refresh the page.');
                }
            });
    });
});